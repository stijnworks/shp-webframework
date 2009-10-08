<?php

/**
 *  SHpartners Web Framework
 *
 *  (c) Copyright 2009 by SHpartners, http://www.shpartners.com
 *
 *  @author Pieter Claerhout <pieter@shpartners.com>
 *  @version 1.0
 */

// Includes
require_once(dirname(__FILE__) . '/shp_http.php');

// Abstraction of an URL
class SH_Url {
    
    // Class variables
    private $url;
    private $method;
    private $conditions;
    private $filters = array();
    public $params   = array();
    public $match    = false;
    
    // Constructor
    public function __construct($httpMethod, $url, $conditions=array(), $mountPoint) {
        
        // Get the request information
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri    = isset($_GET['__url__']) ? $_GET['__url__'] : '/';
        
        // Populate the class variables
        $this->url        = $url;
        $this->method     = $httpMethod;
        $this->conditions = $conditions;
        
        // Check if the HTTP method matches
        if (strtoupper($httpMethod) == $requestMethod) {
            
            // Initialize the variables
            $paramNames  = array();
            $paramValues = array();
            
            // Get the parameter names
            preg_match_all('@:([a-zA-Z]+)@', $url, $paramNames, PREG_PATTERN_ORDER);
            
            // We only need the matches
            $paramNames = $paramNames[1];
            
            // Replace the parameters with the regex capture
            $regexedUrl = preg_replace_callback('@:[a-zA-Z_]+@', array($this, 'regexValue'), $url);
            
            // Determine match and get param values
            if (preg_match('@^' . $regexedUrl . '$@', $requestUri, $paramValues)) {
                
                // Remove the text match
                array_shift($paramValues);
                
                // Populate the params
                for ($i=0; $i < count($paramNames); $i++) {
                    $this->params[$paramNames[$i]] = $paramValues[$i];
                }
                
                // Indicate we found a match
                $this->match = true;
                
            }
            
        }
        
    }
    
    // Callback function to replace a match string with a regexp capture
    private function regexValue($matches) {
        $key = str_replace(':', '', $matches[0]);
        if (isset($this->conditions[$key])) {
            return '(' . $this->conditions[$key] . ')';
        } else {
            return '([a-zA-Z0-9_]+)';
        }
    }
    
}

// Wrapper for an array class
class SH_ArrayWrapper {
    
    // The array
    private $subject;
    
    // Constructor
    public function __construct(&$subject) {
        $this->subject = $subject;
    }
    
    // Get a value
    public function __get($key) {
        return isset($this->subject[$key]) ? $this->subject[$key] : null;
    }
    
    // Set a value
    public function __set($key, $value) {
        $this->subject = $value;
        return $value;
    }
    
}

// Wrapper for the request class
class SH_RequestWrapper {
    
    // Get a value
    public function __get($key) {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
    }
    
    // Set a value
    public function __set($key, $value) {
        $_REQUEST[$key] = $value;
        return $value;
    }
}

// The main framework class
class SH_Framework {
    
    // Class variables
    private $mappings = array();
    private $options;
    protected $session;
    protected $request;
    
    // Constructor
    public function __construct($options=array()) {
        
        // Initialize the variables
        $this->options = new SH_ArrayWrapper($options);
        $this->request = new SH_RequestWrapper();
        
        // Enable the error handler
        //set_error_handler(array($this, 'handleError'), 2);
        
    }
    
    // Handle an error
    public function handleError($number, $message, $file, $line) {
        header("HTTP/1.0 500 Server Error");
        throw new Exception('500 Server Error');
    }
    
    // Show a 404 exeception
    public function show404() {
        header("HTTP/1.0 404 Not Found");
        throw new Exception('404 Not Found');
    }
    
    // Get callback
    public function get($url, $methodName, $conditions=array()) {
       $this->event('get', $url, $methodName, $conditions);
    }
    
    // Post callback
    public function post($url, $methodName, $conditions=array()) {
       $this->event('post', $url, $methodName, $conditions);
    }
    
    // Apply a before filter
    public function before($methodName, $filterName) {
        if (!is_array($methodName)) {
            $methodName = explode('|', $methodName);
        }
        for ($i = 0; $i < count($methodName); $i++) {
            $method = $methodName[$i];
            if (!isset($this->filters[$method])) {
                $this->filters[$method] = array();
            }
            array_push($this->filters[$method], $filterName);
        }
    }
    
    // Run the request
    public function run() {
        echo $this->processRequest();
    }
    
    // Perform a redirect
    protected function redirect($path) {
        
        // Construct the url parts
        $protocol = $_SERVER['HTTPS'] ? 'https' : 'http';
        $host = (preg_match('%^http://|https://%', $path) > 0) ? '' : "$protocol://" . $_SERVER['HTTP_HOST'];
        $uri  = dirname($_SERVER['PHP_SELF']);
        
        // Perform the redirect
        SH_Http::redirect("$host$uri$path");
        
    }
    
    // Execute a method given parameters
    private function execute($methodName, $params) {
        
        // Check if it's a filter
        if (isset($this->filters[$methodName])) {
            for ($i=0; $i < count($this->filters[$methodName]); $i++) {
                $return = call_user_func(array($this, $this->filters[$methodName][$i]));
                if (!is_null($return)) {
                    return $return;
                }
            }
        }
        
        // Run the method
        $reflection = new ReflectionMethod(get_class($this), $methodName);
        $args = array();
        foreach ($reflection->getParameters() as $i => $param) {
            $args[$param->name] = $params[$param->name];
        }
        
        // Run it an return the response
        return call_user_func_array(array($this, $methodName), $args);
        
    }
    
    // Trigger an event
    private function event($httpMethod, $url, $methodName, $conditions=array()) {
        if (method_exists($this, $methodName)) {
            array_push($this->mappings, array($httpMethod, $url, $methodName, $conditions));
        }
    }
    
    // Process the request
    private function processRequest() {
        for ($i = 0; $i < count($this->mappings); $i++) {
            $mapping = $this->mappings[$i];
            $mountPoint = is_string($this->options->mountPoint) ? $this->options->mountPoint : '';
            $url        = new SH_Url($mapping[0], $mapping[1], $mapping[3], $mountPoint);
            if ($url->match === true) {
                return $this->execute($mapping[2], $url->params);
            }
        }
        return $this->show404();
    }
    
}
