<?php

require '../models/Idcard.php';
require '../common/Lib_Imagick.php';

/**
 * 合成身份证图片
 */
class Image_Controller {

    /**
     * 素材目录路径
     *
     * @var string
     */
    protected $material_path;


    /**
     * 身份证正面图片路径
     *
     * @var string
     */
    protected $idcard_front_img_path;

    /**
     * 身份证反面图片路径
     *
     * @var string
     */
    protected $idcard_back_img_path;

    /**
     * 身份证字体路径
     *
     * @var string
     */
    protected $font_path;

    /**
     * 合成图片保存路径
     *
     * @var string
     */
    protected $save_path;

    public function __construct() {
        $this->material_path = __DIR__ . '/../assets/';
        //$this->idcard_front_img_path = $this->material_path . 'images/front_thumb.jpg';
        //$this->idcard_back_img_path  = $this->material_path . 'images/back_thumb.jpg';
        $this->idcard_front_img_path = $this->material_path . 'images/front.jpg';
        $this->idcard_back_img_path  = $this->material_path . 'images/back.jpg';
        $this->font_path  = $this->material_path . 'fonts/idcard_font.ttf';
        $this->save_path  = $this->material_path . 'uploads/';
    }

    /**
     * 使用接收的用户信息创建图片
     *
     * @param    array    $detail_info    用户的详细信息
     * @return   array    $success_idcard 创建成功的或是在数据里已有图片的身份证号
     */
    public function createImg($detail_info) {
        $filepath = $this->save_path . date('Ymd') . '/';

        if ( ! is_dir($filepath)) mkdir($filepath, 0777, TRUE);

        $style['font'] = $this->font_path;
        $style['font_size'] = 14;

        $image = new Lib_Imagick();

        $success_idcard = [];

        //所有男性头像
        $male = 0;
        $male_heads = scandir($this->material_path . 'images/male_head/', 1);
        array_pop($male_heads);
        array_pop($male_heads);
        shuffle($male_heads);

        //所有女性头像
        $female = 0;
        $female_heads = scandir($this->material_path . 'images/female_head/', 1);
        array_pop($female_heads);
        array_pop($female_heads);
        shuffle($female_heads);

        foreach ($detail_info as $key => $val) {

            //查询此身份号是否已有图片，有则不重新创建
            $img_info = Idcard::getIdcardImg($val['idcard_no']);
            if (
                !empty($img_info) &&
                (!empty($img_info['idcard_front_img']) || !empty($img_info['idcard_front_img_false'])) &&
                (!empty($img_info['idcard_back_img']) || !empty($img_info['idcard_back_img_false'])) &&
                (!empty($img_info['idcard_both_img']) || !empty($img_info['idcard_both_img_false']))
            ) {

                //返回给请求的成功的身份证号
                $success_idcard[] = $val['idcard_no'];

                continue;
            }

            //头像图片地址
            if ($img_info['gender'] == 1) {
                $male_head_name = $male_heads[$male];
                $head = $this->material_path . 'images/male_head/' . $male_head_name;
                $male ++;
            } else {
                $female_head_name = $female_heads[$female];
                $head = $this->material_path . 'images/female_head/' . $female_head_name;
                $female ++;
            }

            //正面原始圖片
            $image->open($this->idcard_front_img_path);

            //地址切割换行
            $address_before = mb_substr($val['detail_address'], 0, 11);
            $address_after  = mb_substr($val['detail_address'], 11);

            //取出生日里的数字
            $reg='/(\d{1,4}(\.\d+)?)/is';
            preg_match_all($reg, $val['birthday'], $birthday);
            if ($birthday[0][1] < 10) {
                $left_month = 119;
                $birthday[0][1] = (int)$birthday[0][1];
            } else {
                $left_month = 116;
            }

            if ($birthday[0][2] < 10) {
                $left_day = 150;
                $birthday[0][2] = (int)$birthday[0][2];
            } else {
                $left_day = 146;
            }

            //打印文字
            $image->add_text($val['name'], 65, 175, 0, $style);
            $image->add_text($val['gender'], 65, 149, 0, $style);
            $image->add_text($val['nation'], 139, 149, 0, $style);
            $image->add_text($birthday[0][0], 65, 123, 0, $style);
            $image->add_text($birthday[0][1], $left_month, 123, 0, $style);
            $image->add_text($birthday[0][2], $left_day, 123, 0, $style);
            $image->add_text($address_before . "\r\n" . $address_after, 65, 78, 0, $style);
            $image->add_text($val['idcard_no'], 109, 27, 0, $style);

            //合成图片
            $image->compositeImage(225, 36, $head);

            //新圖片保存地址，如果不保留原始圖片可 直接寫原路徑進行覆蓋即可
            $front = $filepath . $val['idcard_no'] . '_1.jpg';
            $front_res = $image->save_to($front);

            //反面原始圖片
            $image->open($this->idcard_back_img_path);

            //打印文字
            $image->add_text($val['issuing_authority'], 146, 53, 0, $style);
            $image->add_text($val['expired_start'] . '-' . $val['expired_end'], 146, 25, 0, $style);

            //新圖片保存地址，如果不保留原始圖片可直接寫原路徑進行覆蓋即可
            $back = $filepath . $val['idcard_no'] . '_2.jpg';
            $back_res = $image->save_to($back);

            //正反两面的合成
            $image->open();
            if (!empty($img_info['idcard_front_img']) && !empty($img_info['idcard_back_img'])) {
                $image->compositeBothImage(595, 842, 'white', $img_info['idcard_front_img'], $img_info['idcard_back_img']);
            } else {
                $image->compositeBothImage(595, 842, 'white', $front, $back);
            }
            $both = $filepath . $val['idcard_no'] . '_3.jpg';
            $both_res = $image->save_to($both);

            if ($front_res == true && $back_res == true && $both_res == true) {
                //返回给请求的成功的身份证号
                $success_idcard[] = $val['idcard_no'];

                //更新图片地址和签发机关到数据库
                $data['idcard_no'] = $val['idcard_no'];
                $data['front']  = $front;
                $data['back']   = $back;
                $data['both']   = $both;
                $data['issuing_authority']  = $val['issuing_authority'];
                Idcard::updateIdcard($data);
            }
        }

        return $success_idcard;
    }

    /**
     * 下载身份证图片
     *
     * @param    array    $data    用户的详细信息
     * @return   array    $success_idcard 创建成功的或是在数据里已有图片的身份证号
     */
    public function downloadImg($idcard_no) {

        //将所有文件路径分割成一个数组
        foreach ($idcard_no as $val) {
            $img_path[] = Idcard::getIdcardImg($val);
        }

        //实例化zipArchive类
        $zip = new zipArchive();

        //创建空的压缩包
        $zipName = $this->save_path . md5(uniqid() . time()) . '.zip';

        //打开的方式来进行创建 若有则打开 若没有则进行创建
        $zip->open($zipName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);

        if (isset($img_path) && !empty($img_path)) {

            //循环将要下载的文件路径添加到压缩包
            foreach ($img_path as $key => $val) {

                //如果有真实图片,则优先使用真实图片
                if (isset($val['idcard_front_img']) && !empty($val['idcard_front_img'])) {
                    $zip->addFile($val['idcard_front_img'], basename($val['idcard_front_img']));
                } else {
                    $zip->addFile($val['idcard_front_img_false'], basename($val['idcard_front_img_false']));
                }

                if (isset($val['idcard_back_img']) && !empty($val['idcard_back_img'])) {
                    $zip->addFile($val['idcard_back_img'], basename($val['idcard_back_img']));
                } else {
                    $zip->addFile($val['idcard_back_img_false'], basename($val['idcard_back_img_false']));
                }

                if (isset($val['idcard_both_img']) && !empty($val['idcard_both_img'])) {
                    $zip->addFile($val['idcard_both_img'], basename($val['idcard_both_img']));
                } else {
                    $zip->addFile($val['idcard_both_img_false'], basename($val['idcard_both_img_false']));
                }
            }
        }

        //关闭压缩包
        $zip->close();

        //实现文件的下载
        header('Content-Type:Application/zip');
        header('Content-Disposition:attachment; filename=' . $zipName);
        header('Content-Length:' . filesize($zipName));
        readfile($zipName);

        //删除生成的压缩文件
        unlink($zipName);
    }
}
