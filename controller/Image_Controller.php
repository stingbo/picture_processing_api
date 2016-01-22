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
     */
    public function createImg($detail_info) {
        $head = $this->material_path . 'images/male_head/1.png';

        $filepath = $this->save_path . date('Ymd') . '/';

        if ( ! is_dir($filepath)) mkdir($filepath, 0777, TRUE);

        $style['font'] = $this->font_path;
        $style['font_size'] = 12;

        $image = new Lib_Imagick();

        //反面原始圖片
        $image->open($this->idcard_back_img_path);

        //打印文字
        $image->add_text('中国公安局', 180, 60, 0, $style);

        //新圖片保存地址，如果不保留原始圖片可直接寫原路徑進行覆蓋即可
        $image->save_to($filepath . 'back.jpg');

        //正面原始圖片
        $image->open($this->idcard_front_img_path);

        //合成图片
        $image->compositeImage(260, 40, $head);

        //新圖片保存地址，如果不保留原始圖片可 直接寫原路徑進行覆蓋即可
        $image->save_to($filepath . 'front.jpg');
    }
}
