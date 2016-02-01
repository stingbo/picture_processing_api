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

    require '../common/Common.php';
    $common_model = new Common();

    $mark = false;
    if ($request->hasHeader('HTTP_SIGN') && $request->hasHeader('HTTP_CLIENT')) {

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

        //记录日志
        $common_model->writeLog($request->getHeaders(), $request->getParsedBody(), $res);
        return $response;
    }

    $model = new Capsule();
    $model->addConnection(require '../config/database-local.php');
    $model->setEventDispatcher(new Dispatcher(new Container));
    $model->setAsGlobal();
    $model->bootEloquent();
    $response = $next($request, $response);

    //记录日志
    $common_model->writeLog($request->getHeaders(), $request->getParsedBody(), $response->getBody());

    return $response;
};

$app->add($mw);

// Define app routes
// 使用用户名获取用户信息
$app->get('/idcard/name/{name}', function ($request, $response, $args) {
    require '../controller/Idcard_Controller.php';

    $idcard = new Idcard_Controller();
    $user = $idcard->getUserInfo($args['name']);

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

//使用身份证号获取用户信息
$app->get('/idcard/idcard_no/{idcard_no}', function ($request, $response, $args) {
    require '../controller/Idcard_Controller.php';

    $idcard = new Idcard_Controller();
    $user = $idcard->getUserInfo('', $args['idcard_no']);

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

// 使用姓名和身份证号验证用户是否存在
$app->get('/idcard/name/{name}/idcard_no/{idcard_no}', function ($request, $response, $args) {
    require '../controller/Idcard_Controller.php';

    $idcard = new Idcard_Controller();
    $user = $idcard->verifyIdcardInfo($args);

    if ($user == false || empty($user)) {
        $status = 404;
        $result = ['message' => '没有此信息'];
        $res = json_encode($result, JSON_UNESCAPED_UNICODE);
    } else {
        $status = 200;
        $result = ['message' => 'success'];
        $res = json_encode($result, JSON_UNESCAPED_UNICODE);
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

    $info = $request->getParsedBody();
    $file = $request->getUploadedFiles();

    $respon = $idcard->createUser($info, $file);

    if ($respon['result'] == false) {
        $status = 400;
        $result = ['message' => $respon['message']];
        $res = json_encode($result, JSON_UNESCAPED_UNICODE);
    } else {
        $status = 201;
        $res = json_encode($respon['data'], JSON_UNESCAPED_UNICODE);
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

    if (isset($data['download_type']) && in_array($data['download_type'], ['both', 'all'])) {
        $zip_num = $data['download_type'];
    } else {
        $zip_num = '';
    }

    $result = $image->downloadImg($data['idcard_list'], $zip_num);

});

// Run app
$app->run();
