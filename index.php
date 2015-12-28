<?php
require 'vendor/autoload.php';

// Create and configure Slim app
$app = new \Slim\App;

// Define app routes
$app->get('/index/{name}', function ($request, $response, $args) {
    return $response->write("Hello " . $args['name']);
});

$app->post('/index/{name}', function ($request, $response, $args) {
    //return $response->write("Hello " . $args['name']);
    echo 'This is a POST route';
});

$app->put('/index/{name}', function ($request, $response, $args) {
    //return $response->write("Hello " . $args['name']);
    echo 'This is a PUT route';
});

$app->delete('/index/{name}', function ($request, $response, $args) {
    //return $response->write("Hello " . $args['name']);
    echo 'This is a DELETE route';
});

// Run app
$app->run();
