<?php

require '../models/Idcard.php';
require '../common/Lib_Imagick.php';

/**
 * 合成身份证图片
 */
class Image_Controller {

    public function __construct() {
    }

    public function createImg() {
        $img_path = __DIR__ . '/../assets/images/';

        $path1 = '/home/sting/Pictures/idcard_1.png';
        $path2 = '/home/sting/Pictures/idcard_2.png';
        $path3 = '/home/sting/Pictures/head.png';

        $style['font'] = '/home/sting/Downloads/idcard_font/bb2216/idcard_font.ttf';
        $style['font_size'] = 12;

        $image = new Lib_Imagick();
        $image->open($path2);  //原始圖片
        $image->add_text('中国公安局', 180, 60, 0, $style);  //打印文字 方法
        $image->save_to($img_path . 'text.jpg');  //新圖片保存地址，如果不保留原始圖片可 直接寫原路徑進行覆蓋即可

        $image3 = new Lib_Imagick();
        $image3->open($path1);  //原始圖片

        $image3->compositeImage(260, 40, $path3);  //原始圖片
        $image3->save_to($img_path . 'new.jpg');  //新圖片保存地址，如果不保留原始圖片可 直接寫原路徑進行覆蓋即可
    }

}
