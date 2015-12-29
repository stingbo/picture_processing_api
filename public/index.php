<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;


// Autoload 自动载入
require '../vendor/autoload.php';

require '../models/idcard.php';


// Create and configure Slim app
$app = new \Slim\App;

// Define app routes
$app->get('/index/{name}', function ($request, $response, $args) {
    $model = new Capsule();
    $model->addConnection(require '../config/database.php');
    $model->setEventDispatcher(new Dispatcher(new Container));
    $model->setAsGlobal();
    $model->bootEloquent();
    $idcard = Capsule::table('tiantian_idcard')->where('id', '=', 1)->get();

    echo 'aaaa';
    echo '<pre>';
    print_r($idcard);

    //$model = new Idcard();
    //$model::all();
    //$model->getList();
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

//中间件的使用
$mw = function ($request, $response, $next) {
    $response->write('BEFORE');
    $response = $next($request, $response);
    $response->write('AFTER');

    return $response;
};

$app->get('/tickets/{name}/messages/{id}', function ($request, $response, $args) {
    echo $args['name'];
    echo $args['id'];
})->add($mw);

// Run app
$app->run();
