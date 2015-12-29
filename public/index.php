<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

// Autoload 自动载入
require '../vendor/autoload.php';

// Create and configure Slim app
$app = new \Slim\App;

//中间件的使用
$mw = function ($request, $response, $next) {
    //$response->write('BEFORE');
    $model = new Capsule();
    $model->addConnection(require '../config/database.php');
    $model->setEventDispatcher(new Dispatcher(new Container));
    $model->setAsGlobal();
    $model->bootEloquent();
    $response = $next($request, $response);
    //$response->write('AFTER');

    return $response;
};

// Define app routes
$app->get('/index/{name}', function ($request, $response, $args) {
    $id = Capsule::table('tiantian_idcard')->where('id', '=', 2)->get();
    print_r($id);
})->add($mw);

$app->get('/idcard/{name}', function ($request, $response, $args) {
    require '../controller/idcard_controller.php';

    $obj = new Idcard_controller();
    $all = $obj->get();
    print_r($all);

    return $all;
})->add($mw);

$app->post('/index/{name}', function ($request, $response, $args) {
    echo 'This is a POST route';
});

$app->put('/index/{name}', function ($request, $response, $args) {
    echo 'This is a PUT route';
});

$app->delete('/index/{name}', function ($request, $response, $args) {
    echo 'This is a DELETE route';
});

$app->get('/tickets/{name}/messages/{id}', function ($request, $response, $args) {
    echo $args['name'];
    echo $args['id'];
})->add($mw);

// Run app
$app->run();
