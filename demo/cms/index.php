<?php

// Includes
require_once(dirname(__FILE__) . '/../../includes/shp_cms.php');

// Create the instance
$app = new CMS_PublicApplication(dirname(__FILE__));

// Run the application
$app->run();
