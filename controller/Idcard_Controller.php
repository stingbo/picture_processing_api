<?php

require '../models/Idcard.php';
require '../models/Basic_Idcard.php';
require '../models/Position.php';
//require '../common/Common.php';

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
    public function getUserInfo($name = '', $idcard_no = '') {
        if (!empty($name)) {
            $user = Idcard::getOneByName($name);
        } elseif (!empty($idcard_no)) {
            $user = Idcard::getOneByIdcardNo($idcard_no);
        } else {
            return false;
        }

        if ($user == false || empty($user)) {
            if (!empty($name)) {
                $users = Basic_Idcard::getByName($name);
            } else {
                return false;
            }

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

                            $user = $data;

                            break;
                        } else {
                            continue;
                        }

                    } else {
                        continue;
                    }
                }
            }
        }

        return $user;
    }

    /**
     * 批量获取用户信息
     *
     * @param    string    $names    用户姓名
     * @return   array
     */
    public function getByBatchName($names) {
        $user_info = [];
        foreach ($names as $key => $val) {
            $info = $this->getUserInfo($val['name']);
            if ($info !== false && !empty($info)) {
                $user_info[] = $info;
            } else {
                $emptyinfo['name'] = $val['name'];
                $emptyinfo['code'] = 404;
                $user_info[] = $emptyinfo;
            }
        }
        return $user_info;
    }

    /**
     * 创建用户信息
     *
     * @param    array    $info    用户信息
     * @param    array    $file    用户身份证图片
     * @return   array
     */
    public function createUser($info, $file) {
        if (isset($info['name']) && isset($info['idcard_no'])) {

            if (isset($file['idcard_front_img']) && !empty($file['idcard_front_img']) && isset($file['idcard_back_img']) && !empty($file['idcard_back_img'])) {

                //上传正面文件保存路径
                $filepath = __DIR__ . '/../assets/uploads/' . date('Ymd') . '/';
                if ( ! is_dir($filepath)) mkdir($filepath, 0777, TRUE);

                //判断正面
                $front_file_type  = $this->common->getFileType($file['idcard_front_img']->file);
                if (in_array($front_file_type, array('jpg', 'jpeg', 'gif', 'png'))) {
                    $front_save_path = $filepath . $info['idcard_no'] . '_1.' . $front_file_type;
                    $front_save_mark = true;
                } else {
                    return ['result' => false, 'message' => '正面图片格式不正确'];
                }

                //判断反面
                $back_file_type  = $this->common->getFileType($file['idcard_back_img']->file);
                if (in_array($back_file_type, array('jpg', 'jpeg', 'gif', 'png'))) {
                    $back_save_path = $filepath . $info['idcard_no'] . '_2.' . $front_file_type;
                    $back_save_mark = true;
                } else {
                    return ['result' => false, 'message' => '反面图片格式不正确'];
                }

                if (isset($front_save_mark) && isset($back_save_mark)) {
                    if ($front_save_mark && $back_save_mark) {
                        $front_move_rst = move_uploaded_file($file['idcard_front_img']->file, $front_save_path);
                        $back_move_rst  = move_uploaded_file($file['idcard_back_img']->file, $back_save_path);

                        //如果都移动成功,保存到数据库
                        if ($front_move_rst && $back_move_rst) {
                            $data['idcard_front_img']  = $front_save_path;
                            $data['idcard_back_img']   = $back_save_path;
                            $data['is_whole_validate'] = 1;
                        }
                    }
                }
            } elseif (isset($file['idcard_front_img']) && !empty($file['idcard_front_img']) && !isset($file['idcard_back_img'])) {
                return ['result' => false, 'message' => '必须同时上传正反面图片'];
            } elseif (!isset($file['idcard_front_img']) && isset($file['idcard_back_img']) && !empty($file['idcard_back_img'])) {
                return ['result' => false, 'message' => '必须同时上传正反面图片'];
            }

            $data['name']       = $info['name'];
            $data['idcard_no']  = $info['idcard_no'];
            $data['is_portion_validate'] = 1;
            $data['created_at'] = date('Y-m-d H:i:s');

            //保存
            Idcard::createIdcard($data);
            return ['result' => true, 'data' => $data];
        } else {
            return ['result' => false, 'message' => '接口所需信息不完整'];
        }
    }

    /**
     * 使用姓名和身份证号验证信息是否为真
     *
     * @param    array    $info    用户信息
     * @return   bool
     */
    public function verifyIdcardInfo($info) {
        $result = Idcard::verifyIdcardInfo($info);
        if (isset($result) && $result == 1) {
            return true;
        } else {
            return false;
        }
    }
}
