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
        $this->idcard_front_img_path = $this->material_path . 'images/front.jpg';
        $this->idcard_back_img_path  = $this->material_path . 'images/back.jpg';
        $this->font_path  = $this->material_path . 'fonts/';
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

        //方正黑体简体
        $style_fzhtjt['font'] = $this->font_path . 'FZHTJW.TTF';
        $style_fzhtjt['fill_color'] = '#4b424a';

        //OCR-B字体
        $style_ocr['font'] = $this->font_path . 'Ocrb10n.ttf';
        $style_ocr['fill_color'] = '#4b424a';

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

            //如果没有图片还要创建图片，缺少信息，则进行下一条
            $front = $filepath . $val['idcard_no'] . '_1.jpg';
            if (!empty($val['name']) && !empty($val['gender']) && !empty($val['nation']) && !empty($val['birthday']) && !empty($val['detail_address']) && !empty($val['idcard_no'])) {
                //正面圖片制作
                $front_res = $this->createFrontImg($val, $front, $style_fzhtjt, $style_ocr, $head);
            }

            $back = $filepath . $val['idcard_no'] . '_2.jpg';
            if (!empty($val['issuing_authority']) && !empty($val['expired_start']) && !empty($val['expired_end'])) {
                //反面圖片制作
                $back_res = $this->createBackImg($val, $back, $style_fzhtjt);
            }

            //正反两面的合成
            $both = $filepath . $val['idcard_no'] . '_3.jpg';
            $both_res = $this->createBothImg($img_info, $both, $front, $back);

            $data = [];
            if (isset($front_res) && $front_res == true) {
                $data['front']  = $front;
            }

            if (isset($back_res) && $back_res == true) {
                $data['back']   = $back;
            }

            if (isset($both_res) && $both_res == true) {
                $data['both']   = $both;

                //返回给请求的成功的身份证号
                $success_idcard[] = $val['idcard_no'];

                //更新图片地址和签发机关到数据库
                $data['idcard_no'] = $val['idcard_no'];
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
     * @param    array    $zip_num 下载压缩图片张数
     * @return   array    $success_idcard 创建成功的或是在数据里已有图片的身份证号
     */
    public function downloadImg($idcard_no, $zip_num = '') {

        if (is_array($idcard_no) && count($idcard_no) >= 1) {

            //将所有文件路径分割成一个数组
            foreach ($idcard_no as $val) {
                $img_path[] = Idcard::getIdcardImg($val);
            }
        } elseif (is_string($idcard_no)) {
            $img_path[] = Idcard::getIdcardImg($idcard_no);
        } else {
            return false;
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

                //三张
                if ($zip_num == 'all') {

                    //如果有真实图片,则优先使用真实图片
                    if (isset($val['idcard_front_img']) && !empty($val['idcard_front_img'])) {
                        $zip->addFile($val['idcard_front_img'], basename($val['idcard_front_img']));
                    } elseif (isset($val['idcard_front_img_false']) && !empty($val['idcard_front_img_false'])) {
                        $zip->addFile($val['idcard_front_img_false'], basename($val['idcard_front_img_false']));
                    }

                    if (isset($val['idcard_back_img']) && !empty($val['idcard_back_img'])) {
                        $zip->addFile($val['idcard_back_img'], basename($val['idcard_back_img']));
                    } elseif (isset($val['idcard_back_img_false']) && !empty($val['idcard_back_img_false'])) {
                        $zip->addFile($val['idcard_back_img_false'], basename($val['idcard_back_img_false']));
                    }

                    if (isset($val['idcard_both_img']) && !empty($val['idcard_both_img'])) {
                        $zip->addFile($val['idcard_both_img'], basename($val['idcard_both_img']));
                    } elseif (isset($val['idcard_both_img_false']) && !empty($val['idcard_both_img_false'])) {
                        $zip->addFile($val['idcard_both_img_false'], basename($val['idcard_both_img_false']));
                    }

                //正反合一
                } elseif ($zip_num == 'both') {
                    if (isset($val['idcard_both_img']) && !empty($val['idcard_both_img'])) {
                        $zip->addFile($val['idcard_both_img'], basename($val['idcard_both_img']));
                    } elseif (isset($val['idcard_both_img_false']) && !empty($val['idcard_both_img_false'])) {
                        $zip->addFile($val['idcard_both_img_false'], basename($val['idcard_both_img_false']));
                    }

                //正反面
                } else {
                    if (isset($val['idcard_front_img']) && !empty($val['idcard_front_img'])) {
                        $zip->addFile($val['idcard_front_img'], basename($val['idcard_front_img']));
                    } elseif (isset($val['idcard_front_img_false']) && !empty($val['idcard_front_img_false'])) {
                        $zip->addFile($val['idcard_front_img_false'], basename($val['idcard_front_img_false']));
                    }

                    if (isset($val['idcard_back_img']) && !empty($val['idcard_back_img'])) {
                        $zip->addFile($val['idcard_back_img'], basename($val['idcard_back_img']));
                    } elseif (isset($val['idcard_back_img_false']) && !empty($val['idcard_back_img_false'])) {
                        $zip->addFile($val['idcard_back_img_false'], basename($val['idcard_back_img_false']));
                    }
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

    /**
     * 创建正面图片
     */
    public function createFrontImg($val, $front, $style_fzhtjt, $style_ocr, $head) {
        $image = new Lib_Imagick();

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
            $left_day = 151;
            $birthday[0][2] = (int)$birthday[0][2];
        } else {
            $left_day = 146;
        }

        //打印文字
        //名字
        $style_fzhtjt['font_size'] = 15;
        if (mb_strlen($val['name']) == 2) {
            $name_one = mb_substr($val['name'], 0, 1);
            $name_two = mb_substr($val['name'], 1);
            $image->add_text($name_one . ' ' . $name_two, 67, 176, 0, $style_fzhtjt);
        } else {
            $image->add_text($val['name'], 67, 176, 0, $style_fzhtjt);
        }

        //性别，民族，地址
        $style_fzhtjt['font_size'] = 13;
        $image->add_text($val['gender'], 67, 151, 0, $style_fzhtjt);
        $image->add_text($val['nation'], 138, 151, 0, $style_fzhtjt);
        $image->add_text($address_before . "\r\n" . $address_after, 67, 83, 0, $style_fzhtjt);

        //出生年月
        $style_fzhtjt['font_size'] = 13;
        $image->add_text($birthday[0][0], 66, 125, 0, $style_fzhtjt);
        $image->add_text($birthday[0][1], $left_month, 125, 0, $style_fzhtjt);
        $image->add_text($birthday[0][2], $left_day, 125, 0, $style_fzhtjt);

        //身份证号
        $style_ocr['font_size'] = 16;
        $image->add_text($val['idcard_no'], 115, 27, 0, $style_ocr);

        //合成图片
        $image->compositeImage(216, 36, $head);

        //图片保存
        $result = $image->save_to($front);

        return $result;
    }

    /**
     * 创建反面图片
     */
    public function createBackImg($val, $back, $style_fzhtjt) {
        $image = new Lib_Imagick();

        $image->open($this->idcard_back_img_path);

        //打印文字
        $style_fzhtjt['font_size'] = 13;
        $image->add_text($val['issuing_authority'], 146, 55, 0, $style_fzhtjt);
        $image->add_text($val['expired_start'] . '-' . $val['expired_end'], 146, 27, 0, $style_fzhtjt);

        //图片保存
        $result = $image->save_to($back);

        return $result;
    }

    /**
     * 正反合一图片合成
     */
    public function createBothImg($img_info, $both, $front = '', $back = '') {
        $image = new Lib_Imagick();

        $image->open();
        if (!empty($img_info['idcard_front_img']) && !empty($img_info['idcard_back_img']) && file_exists($img_info['idcard_front_img']) && file_exists($img_info['idcard_back_img'])) {
            $image->compositeBothImage(595, 842, 'white', $img_info['idcard_front_img'], $img_info['idcard_back_img']);
        } elseif (file_exists($front) && file_exists($back)) {
            $image->compositeBothImage(595, 842, 'white', $front, $back);
        } else {
            return false;
        }

        $result = $image->save_to($both);

        return $result;
    }
}
