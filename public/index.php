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
    $model->addConnection(require '../config/database-local.php');
    $model->setEventDispatcher(new Dispatcher(new Container));
    $model->setAsGlobal();
    $model->bootEloquent();
    $response = $next($request, $response);

    //$response->write('AFTER');

    return $response;
};

$app->add($mw);

// Define app routes
$app->get('/idcard/{name}', function ($request, $response, $args) {
    require '../controller/Idcard_Controller.php';

    $idcard = new Idcard_Controller();
    $user = $idcard->getByName($args['name']);

    if ($user == false || empty($user)) {
        $status = 404;
        $result = ['errcode' => $status, 'message' => '没有此信息'];
        $res = json_encode($result, JSON_UNESCAPED_UNICODE);
    } else {
        $status = 200;
        $result = ['errcode' => $status, 'message' => '请求成功', 'data' => $user];
        $res = json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    $response = $response->withStatus($status)
        ->withHeader('Content-Type', 'application/json')
        ->write($res);
    return $response;
});

$app->post('/idcard/{name}', function ($request, $response, $args) {

});

//创建图片
$app->post('/image/{name}', function ($request, $response, $args) {
    require '../controller/Image_Controller.php';

    $image = new Image_Controller();
    $result = $image->createImg();
    echo $args['name'];

});

// Run app
$app->run();
