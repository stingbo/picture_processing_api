<?php
require 'vendor/autoload.php';

// Create and configure Slim app
$app = new \Slim\App;

// Define app routes
$app->get('/index/{name}', function ($request, $response, $args) {
    return $response->write("Hello " . $args['name']);
});

// Run app
$app->run();
