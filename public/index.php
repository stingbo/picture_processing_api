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
        $result = ['message' => '没有此信息'];
        $res = json_encode($result, JSON_UNESCAPED_UNICODE);
    } else {
        $status = 200;
        $res = json_encode($user, JSON_UNESCAPED_UNICODE);
    }

    $response = $response->withStatus($status)
        ->withHeader('Content-Type', 'application/json')
        ->write($res);
    return $response;
});

//用姓名批量获取用户信息
$app->post('/userinfo', function ($request, $response, $args) {
    require '../controller/Idcard_Controller.php';

    $idcard = new Idcard_Controller();

    $names = $request->getParsedBody();
    $users = $idcard->getByBatchName($names['user_list']);

    $status = 200;
    $result = json_encode($users, JSON_UNESCAPED_UNICODE);
    $response = $response->withStatus($status)
        ->withHeader('Content-Type', 'application/json')
        ->write($result);
    return $response;
});

//创建图片
$app->post('/image', function ($request, $response, $args) {
    require '../controller/Image_Controller.php';

    $image = new Image_Controller();

    $data = $request->getParsedBody();
    $result = $image->createImg($data['user_list']);

    if (isset($result) && !empty($result)) {
        $status = 201;
        $res = json_encode($result, JSON_UNESCAPED_UNICODE);
    } else {
        $status = 400;
        $result = ['message' => '创建失败'];
        $res = json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    $response = $response->withStatus($status)
        ->withHeader('Content-Type', 'application/json')
        ->write($res);
    return $response;

});

//获取图片
$app->post('/images', function ($request, $response, $args) {
    require '../controller/Image_Controller.php';

    $image = new Image_Controller();

    $data = $request->getParsedBody();
    $result = $image->downloadImg($data);

    //if (isset($result) && !empty($result)) {
        //$status = 201;
        //$res = json_encode($result, JSON_UNESCAPED_UNICODE);
        //$res = json_encode($data, JSON_UNESCAPED_UNICODE);
    //} else {
        //$status = 400;
        //$result = ['message' => '创建失败'];
        //$res = json_encode($result, JSON_UNESCAPED_UNICODE);
    //}
    //$response = $response->withStatus($status)
        //->withHeader('Content-Type', 'application/json')
        //->write($res);
    //return $response;

});

// Run app
$app->run();
