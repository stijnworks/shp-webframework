<?php

// Includes
require_once(dirname(__FILE__) . '/../../includes/shp_framework.php');

// Create a new application class
class Application extends SH_WebApplication {
    
    // Basic get request
    public function get_index() {
        $this->template('index');
    }
    
    // Run the php info
    public function get_info() {
        phpinfo();
    }
    
    // Return json info
    public function get_json() {
        SH_Http::sendJson(
            array(
                'name'  => 'Pieter Claerhout',
                'email' => 'pieter@shpartners.com',
                'url'   => 'http://www.shpartners.com',
            )
        );
    }
    
    // Return xml info
    public function get_xml() {
        SH_Http::sendXml(
            "<results><record><name>Pieter Claerhout</name><email>pieter@shpartners.com</email></record></results>"
        );
    }
    
    // Perform a redirect
    public function get_redirect() {
        $this->redirect('/');
    }
    
    // Get a page
    public function get_page($page, $id=null) {
        $this->page = $page;
        $this->params = func_get_args();
        $this->template('page');
    }
    
    // Any request handler
    public function any_handler() {
        $this->template('any');
    }
    
    // Add a custom header
    public function add_header() {
        echo '<h1>My custom header</h1>';
    }
    
    // Output a template
    protected function template($template) {
        include(dirname(__FILE__) . "/templates/{$template}.html");
    }
    
}

// Create the instance
$app = new Application();

// Run a before filter
$app->before('get_info', 'add_header');

// Link up the urls
$app->get('/',                        'get_index');
$app->get('/info/',                   'get_info');
$app->get('/json/',                   'get_json');
$app->get('/xml/',                    'get_xml');
$app->get('/redirect/',               'get_redirect');
$app->get('/page/<page>/',            'get_page');
$app->any('/any/',                    'any_handler');
$app->get('/page/<page:\d{4}>/<id>/', 'get_page');
$app->get('/page/<page:.*>/',         'get_page');

// Run the application
$app->run();
