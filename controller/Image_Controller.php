<?php

require '../models/Idcard.php';
require '../common/Lib_Imagick.php';

/**
 * 合成身份证图片
 */
class Image_Controller {

    protected $material_path;
    protected $idcard_front_img_path;
    protected $idcard_back_img_path;
    protected $font_path;
    protected $save_path;

    public function __construct() {
        $this->material_path = __DIR__ . '/../assets/';
        $this->idcard_front_img_path = $this->material_path . 'images/front_thumb.jpg';
        $this->idcard_back_img_path  = $this->material_path . 'images/back_thumb.jpg';
        //$this->idcard_front_img_path = $this->material_path . 'images/front.jpg';
        //$this->idcard_back_img_path  = $this->material_path . 'images/back.jpg';
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
        $head = $this->material_path . 'images/male_head/1.png';

        $filepath = $this->save_path . date('Ymd') . '/';

        if ( ! is_dir($filepath)) mkdir($filepath, 0777, TRUE);

        $style['font'] = $this->font_path;
        $style['font_size'] = 20;

        $image = new Lib_Imagick();

        $success_idcard = [];
        foreach ($detail_info as $key => $val) {

            //反面原始圖片
            $image->open($this->idcard_back_img_path);

            //打印文字
            $image->add_text($val['issuing_authority'], 250, 102, 0, $style);
            $image->add_text($val['expired_start'] . '-' . $val['expired_end'], 250, 62, 0, $style);

            //新圖片保存地址，如果不保留原始圖片可直接寫原路徑進行覆蓋即可
            $back = $filepath . $val['idcard_no'] . '_2.jpg';
            $image->save_to($back);

            //正面原始圖片
            $image->open($this->idcard_front_img_path);

            //打印文字
            $image->add_text($val['name'], 140, 302, 0, $style);
            $image->add_text($val['gender'], 140, 270, 0, $style);
            $image->add_text($val['nation'], 240, 270, 0, $style);
            $image->add_text($val['birthday'], 140, 240, 0, $style);
            $image->add_text($val['detail_address'], 140, 190, 0, $style);
            $image->add_text($val['idcard_no'], 190, 62, 0, $style);

            //合成图片
            $image->compositeImage(460, 40, $head);

            //新圖片保存地址，如果不保留原始圖片可 直接寫原路徑進行覆蓋即可
            $front = $filepath . $val['idcard_no'] . '_1.jpg';
            $image->save_to($front);

            //返回给请求的成功的身份证号
            $success_idcard[] = $val['idcard_no'];

            //更新图片地址到数据库
            $data['name']  = $val['name'];
            $data['front'] = $front;
            $data['back']  = $back;
            Idcard::updateIdcard($data);
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
        //接收上一页面传递过来的选中的所有的文件的路径
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

        //循环将要下载的文件路径添加到压缩包
        foreach ($img_path as $key => $val) {
            $zip->addFile($val['idcard_front_img_false'], basename($val['idcard_front_img_false']));
            $zip->addFile($val['idcard_back_img_false'], basename($val['idcard_back_img_false']));
        }

        //关闭压缩包
        $zip->close();

        //return $zipName;

        //实现文件的下载
        header('Content-Type:Application/zip');
        header('Content-Disposition:attachment; filename=' . $zipName);
        header('Content-Length:' . filesize($zipName));
        readfile($zipName);

        //删除生成的压缩文件
        //unlink($zipName);
    }
}
