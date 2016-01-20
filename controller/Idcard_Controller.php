<?php

require '../models/Idcard.php';
require '../models/Basic_Idcard.php';
require '../models/Position.php';
require '../common/Common.php';

/**
 * 获取身份证信息
 */
class Idcard_Controller {

    protected $common;

    public function __construct() {
        $this->common = new Common();
    }

    /**
     * 1,根据用户名到idcard表里查询，如果存在则直接返回(后期可以加一个缓存层)
     * 2,到基础表basic_idcard里查询，如果有则去验证是否有效，有效则把信息补充完成存入idcard表
     * 3,都没有则返回空
     * 
     * @param    string    $name    用户姓名
     * @return   array
     */
    public function getByName($name) {
        $user = Idcard::getOneByName($name);

        if ($user == false || empty($user)) {
            $users = Basic_Idcard::getByName($name);

            if ($users == false) {
                return false;
            } else {

                //根据身份证号验证查询地址，只能到区县
                $url = "http://apis.juhe.cn/idcard/index";
                $params = [
                    "dtype"  => 'json',
                    "key"    => '3bb65eeff15840789e356f14ea2d23af',
                ];

                foreach ($users as $key => $value) {
                    $len = strlen($value['idcard_no']);
                    if ($len >= 15 && $len <= 18) {
                        $params['cardno'] = $value['idcard_no'];

                        $url .= '?' . http_build_query($params);

                        //curl请求验证身份号是否存在
                        $response = $this->common->request($url);
                        $res = json_decode($response, true);

                        if ($res['resultcode'] == 200) {

                            //根据身份证号前六位获取地址信息
                            $county_id = substr($value['idcard_no'], 0, 6);
                            $county_id = str_pad($county_id, 12, 0);

                            $positions = Position::getPositionByCountyId($county_id);

                            if ($positions == false || empty($positions)) {

                                //根据身份证号前四位获取地址信息
                                $city_id = substr($value['idcard_no'], 0, 4);
                                $city_id = str_pad($city_id, 12, 0);
                                $positions = Position::getPositionByCityId($city_id);

                                if ($positions == false || empty($positions)) continue;
                            }

                            //地址信息
                            $pos_key = array_rand($positions, 1);

                            //身份证有效期信息
                            $expired = $this->common->getExpired($value['idcard_no']);

                            $data['name']       = $name;
                            $data['gender']     = $res['result']['sex'] == '男' ? 1 : 0;
                            $data['nation']     = '汉';
                            $data['birthday']   = $res['result']['birthday'];
                            $data['idcard_no']  = $value['idcard_no'];
                            $data['province']   = $positions[$pos_key]['province_name'];
                            $data['city']       = $positions[$pos_key]['city_name'];
                            $data['county']     = $positions[$pos_key]['county_name'];
                            $data['full_address'] = $data['province'] . $data['city'] . $data['county'] . $positions[$pos_key]['town_name'] . $positions[$pos_key]['village_name'];
                            $data['expired_start'] = $expired['expired_start'];
                            $data['expired_end']   = $expired['expired_end'];
                            $data['is_portion_validate'] = 1;
                            $data['created_at']    = date('Y-m-d H:i:s');

                            //保存
                            Idcard::createIdcard($data);

                            break;
                        } else {
                            continue;
                        }

                    } else {
                        continue;
                    }
                }

                $user = Idcard::getOneByName($name);
            }
        }

        return $user;
    }
}
