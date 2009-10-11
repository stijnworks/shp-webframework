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
require_once(dirname(__FILE__) . '/shp_debug.php');
require_once(dirname(__FILE__) . '/shp_html.php');
require_once(dirname(__FILE__) . '/shp_framework.php');

// The public CMS application
class CMS_PublicApplication extends SH_WebApplication {
    
    // Class variables
    protected $root;
    protected $store;
    
    // Constructor
    public function __construct($root, $options=array()) {
        
        // Run the parent constructor
        parent::__construct($options);
        
        // Link the default handlers
        $this->any('<page:.*>', 'any_page');
        
        // Assign the class variables
        $this->root = $root;
        $this->store = CMS_ContentStore::$root = "{$root}/content";
        
        // Update the include path
        SH_WebApplication::import("{$root}/code");
        
        // Include the base page class
        include_once('Page.php');
        
    }
    
    // Get a page by it's url
    public function any_page($page) {
        
        // The path of the page
        $this->page = CMS_ContentStore::findPage($page);
        
        // Output the template
        $this->template($this->page);
        
    }
    
    // Output a template
    protected function template($page) {
        
        // Get the template path
        $template = get_class($page);
        $template = "{$this->root}/templates/{$template}.html";
        
        // Extract the variables
        extract(get_object_vars($this));
        
        // Include the template
        include($template);
        
    }
    
}

// The CMS content store
class CMS_ContentStore {
    
    // Class variables
    public static $root;
    
    // Find a page
    public static function findPage($path) {
        
        // Construct the full path to the file
        $fullPath = self::rel2absPath($path) . '.json';
        
        // Raise an error if the page doesn't exist
        if (!file_exists($fullPath)) {
            throw new Exception("Not found: {$path}");
        }
        
        // Return the page instance
        return self::getPageInstance($path, $fullPath);
        
    }
    
    // Get the parent pages of a page
    public static function listParents($path) {
        
        // Keep an empty list of items
        $pages = array();
        
        // Return an empty array if we are at the top page
        if (empty($path)) {
            return $pages;
        }
        
        // Split up the path and remove the last element
        $pathSplitted = explode('/', '/' . $path);
        array_pop($pathSplitted);
        
        // Get the list of pages
        $currentPath = array();
        foreach ($pathSplitted as $pathItem) {
            
            // Add it to the current path
            $currentPath[] = $pathItem;
            
            // Get the item path
            $itemPath = ltrim(implode('/', $currentPath), '/');
            
            // Add the page to the list
            $pages[] = self::findPage($itemPath);
            
        }
        
        // Return the list of pages
        return $pages;
        
    }
    
    // List all children of a page
    public static function listChildren($path) {
        
        // Keep an empty list of items
        $pages = array();
        
        // Get the full path
        $fullPath = self::rel2absPath($path);
        
        // Exit if the directory doesn't exist
        if (!is_dir($fullPath)) {
            return $pages;
        }
        
        // List all the subpages
        foreach (new DirectoryIterator($fullPath) as $file) {
            
            // Skip the invisible files
            if (substr($file->getFileName(), 0, 1) == '.' || $file->isDir()) {
                continue;
            }
            
            // Get the path and full path
            $pagePath = $path . '/' . basename($file->getFileName(), '.json') . '/';
            $pageFullPath = $fullPath . '/' . $file->getFileName();
            
            // Add it to the list
            $pages[] = self::getPageInstance($pagePath, $pageFullPath);
            
        }
        
        // Return the list of pages
        return $pages;
        
    }
    
    // Get a page instance
    protected static function getPageInstance($path, $fullPath) {
        
        // Parse the page content
        $page = json_decode(file_get_contents($fullPath), true);
        
        // Get the class instance
        if (isset($page['__type__'])) {
            $class = $page['__type__'];
        } else {
            $class = 'Page';
        }
        
        // Include the file
        include_once("{$class}.php");
        
        // Create the instance
        $instance = new $class();
        $instance->path = $path;
        $instance->data = $page;
        
        // Return the instance
        return $instance;
        
    }
    
    // Get the full path from a relative path
    protected static function rel2absPath($path) {
        $path = empty($path) ? "root" : "root/{$path}";
        return self::$root . "/" . $path;
    }
    
}

// The CMS content page
class CMS_ContentPage {
    
    // Class variables
    public $path;
    public $data = array();
    
    // Convert this to a string
    public function __toString() {
        return htmlentities("<CMS Content Page: {$this->path}>");
    }
    
    // Get a variable
    public function get($name, $default='') {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return $default;
        }
    }
    
    // Get the url for the page
    public function getUrl() {
        if (!empty($this->path)) {
            return trim($this->path, '/') . '/';
        } else {
            return $this->path;
        }
    }
    
    // Get the path to the page
    public function getParents() {
        return CMS_ContentStore::listParents($this->path);
    }
    
    // Get the children of the page
    public function getChildren() {
        return CMS_ContentStore::listChildren($this->path);
    }
    
}