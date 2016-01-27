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

    $mark = false;
    if ($request->hasHeader('HTTP_SIGN') && $request->hasHeader('HTTP_CLIENT')) {
        require '../common/Common.php';
        $common_model = new Common();

        $sign = $request->getHeaderLine('HTTP_SIGN');
        $client = $request->getHeaderLine('HTTP_CLIENT');

        $code = $common_model->decrypt($client, base64_decode($sign));

        if ($code != 'b610e0458b5512bf798eb4c0c2af1598') {
            $mark = true;
        }
    } else {
        $mark = true;
    }

    if ($mark) {
        $status = 401;
        $result = ['message' => '没有权限'];
        $res = json_encode($result, JSON_UNESCAPED_UNICODE);

        $response = $response->withStatus($status)
            ->withHeader('Content-Type', 'application/json')
            ->write($res);
        return $response;
    }

    $model = new Capsule();
    $model->addConnection(require '../config/database-local.php');
    $model->setEventDispatcher(new Dispatcher(new Container));
    $model->setAsGlobal();
    $model->bootEloquent();
    $response = $next($request, $response);

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

//创建用户身份证信息
$app->post('/idcard', function ($request, $response, $args) {
    require '../controller/Idcard_Controller.php';

    $idcard = new Idcard_Controller();
    $user = $idcard->createUser($args['name']);

    if ($user == false || empty($user)) {
        $status = 400;
        $result = ['message' => '语法错误'];
        $res = json_encode($result, JSON_UNESCAPED_UNICODE);
    } else {
        $status = 201;
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
