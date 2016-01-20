<?php

/**
 * 合成身份证图片
 */
class Create_Image_Controller {

    public function __construct() {
    }

    public function get() {

    }

    public function post() {
        //$watermark = new Imagick('/home/sting/Pictures/idcard_1.png');

        //$draw = new ImagickDraw();

        //$draw->composite($watermark->getImageCompose(),$x,$y,$watermark->getImageWidth(),$watermark->getimageheight(),$watermark);


        $src1 = new Imagick("/home/sting/Pictures/idcard_1.png");
        $src2 = new Imagick("/home/sting/Pictures/head.png");

        $src1->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
        $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
        $src1->compositeImage($src2, Imagick::COMPOSITE_MATHEMATICS, 0, 0);
        $src1->writeImage("./output.png");
    }

    public function put() {

    }

    public function del() {

    }
}
