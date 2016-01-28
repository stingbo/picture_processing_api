<?php

/**
 * 使用php extension imagick处理图片
 */
class Lib_Imagick {

    private $image = null;
    private $type = null;

    // 構造函數
    public function __construct(){}


    // 析構函數
    public function __destruct()
    {
        if($this->image!==null) $this->image->destroy();
    }

    // 載入圖像
    public function open($path = '')
    {
        if (!empty($path)) {
            $this->image = new Imagick($path);
            if($this->image)
            {
                $this->type = strtolower($this->image->getImageFormat());
            }
        } else {
            $this->image = new Imagick();
        } 
        return $this->image;
    }


    public function crop($x=0, $y=0, $width=null, $height=null)
    {
        if($width==null) $width = $this->image->getImageWidth()-$x;
        if($height==null) $height = $this->image->getImageHeight()-$y;
        if($width<=0 || $height<=0) return;

        if($this->type=='gif')
        {
            $image = $this->image;
            $canvas = new Imagick();

            $images = $image->coalesceImages();
            foreach($images as $frame){
                $img = new Imagick();
                $img->readImageBlob($frame);
                $img->cropImage($width, $height, $x, $y);

                $canvas->addImage( $img );
                $canvas->setImageDelay( $img->getImageDelay() );
                $canvas->setImagePage($width, $height, 0, 0);
            }

            $image->destroy();
            $this->image = $canvas;
        }
        else
        {
            $this->image->cropImage($width, $height, $x, $y);
        }
    }

    /**
     * 更改圖像大小
     $fit: 適應大小方式
     'force': 把圖片強制變形成 $width X $height 大小
     'scale': 按比例在安全框 $width X $height 內縮放圖片, 輸出縮放後圖像大小 不完全等于 $width X $height
     'scale_fill': 按比例在安全框 $width X $height 內縮放圖片，安全框內沒有像素的地方填充色, 使用此參數時可設置背景填充色 $bg_color = array(255,255,255)(紅,綠,藍, 透明度) 透明度(0不透明-127完全透明))
     其它: 智能模能 縮放圖像並載取圖像的中間部分 $width X $height 像素大小
     $fit = 'force','scale','scale_fill' 時： 輸出完整圖像
     $fit = 圖像方位值 時, 輸出指定位置部分圖像
     字母與圖像的對應關系如下:

     north_west   north   north_east

     west         center        east

     south_west   south   south_east

     */
    public function resize_to($width = 100, $height = 100, $fit = 'center', $fill_color = array(255,255,255,0) )
    {

        switch($fit)
        {
        case 'force':
            if($this->type=='gif')
            {
                $image = $this->image;
                $canvas = new Imagick();

                $images = $image->coalesceImages();
                foreach($images as $frame){
                    $img = new Imagick();
                    $img->readImageBlob($frame);
                    $img->thumbnailImage( $width, $height, false );

                    $canvas->addImage( $img );
                    $canvas->setImageDelay( $img->getImageDelay() );
                }
                $image->destroy();
                $this->image = $canvas;
            }
            else
            {
                $this->image->thumbnailImage( $width, $height, false );
            }
            break;
        case 'scale':
            if($this->type=='gif')
            {
                $image = $this->image;
                $images = $image->coalesceImages();
                $canvas = new Imagick();
                foreach($images as $frame){
                    $img = new Imagick();
                    $img->readImageBlob($frame);
                    $img->thumbnailImage( $width, $height, true );

                    $canvas->addImage( $img );
                    $canvas->setImageDelay( $img->getImageDelay() );
                }
                $image->destroy();
                $this->image = $canvas;
            }
            else
            {
                $this->image->thumbnailImage( $width, $height, true );
            }
            break;
        case 'scale_fill':
            $size = $this->image->getImagePage();
            $src_width = $size['width'];
            $src_height = $size['height'];

            $x = 0;
            $y = 0;

            $dst_width = $width;
            $dst_height = $height;

            if($src_width*$height > $src_height*$width)
            {
                $dst_height = intval($width*$src_height/$src_width);
                $y = intval( ($height-$dst_height)/2 );
            }
            else
            {
                $dst_width = intval($height*$src_width/$src_height);
                $x = intval( ($width-$dst_width)/2 );
            }

            $image = $this->image;
            $canvas = new Imagick();

            $color = 'rgba('.$fill_color[0].','.$fill_color[1].','.$fill_color[2].','.$fill_color[3].')';
            if($this->type=='gif')
            {
                $images = $image->coalesceImages();
                foreach($images as $frame)
                {
                    $frame->thumbnailImage( $width, $height, true );

                    $draw = new ImagickDraw();
                    $draw->composite($frame->getImageCompose(), $x, $y, $dst_width, $dst_height, $frame);

                    $img = new Imagick();
                    $img->newImage($width, $height, $color, 'gif');
                    $img->drawImage($draw);

                    $canvas->addImage( $img );
                    $canvas->setImageDelay( $img->getImageDelay() );
                    $canvas->setImagePage($width, $height, 0, 0);
                }
            }
            else
            {
                $image->thumbnailImage( $width, $height, true );

                $draw = new ImagickDraw();
                $draw->composite($image->getImageCompose(), $x, $y, $dst_width, $dst_height, $image);

                $canvas->newImage($width, $height, $color, $this->get_type() );
                $canvas->drawImage($draw);
                $canvas->setImagePage($width, $height, 0, 0);
            }
            $image->destroy();
            $this->image = $canvas;
            break;
        default:
            $size = $this->image->getImagePage();
            $src_width = $size['width'];
            $src_height = $size['height'];

            $crop_x = 0;
            $crop_y = 0;

            $crop_w = $src_width;
            $crop_h = $src_height;

            if($src_width*$height > $src_height*$width)
            {
                $crop_w = intval($src_height*$width/$height);
            }
            else
            {
                $crop_h = intval($src_width*$height/$width);
            }

            switch($fit)
            {
            case 'north_west':
                $crop_x = 0;
                $crop_y = 0;
                break;
            case 'north':
                $crop_x = intval( ($src_width-$crop_w)/2 );
                $crop_y = 0;
                break;
            case 'north_east':
                $crop_x = $src_width-$crop_w;
                $crop_y = 0;
                break;
            case 'west':
                $crop_x = 0;
                $crop_y = intval( ($src_height-$crop_h)/2 );
                break;
            case 'center':
                $crop_x = intval( ($src_width-$crop_w)/2 );
                $crop_y = intval( ($src_height-$crop_h)/2 );
                break;
            case 'east':
                $crop_x = $src_width-$crop_w;
                $crop_y = intval( ($src_height-$crop_h)/2 );
                break;
            case 'south_west':
                $crop_x = 0;
                $crop_y = $src_height-$crop_h;
                break;
            case 'south':
                $crop_x = intval( ($src_width-$crop_w)/2 );
                $crop_y = $src_height-$crop_h;
                break;
            case 'south_east':
                $crop_x = $src_width-$crop_w;
                $crop_y = $src_height-$crop_h;
                break;
            default:
                $crop_x = intval( ($src_width-$crop_w)/2 );
                $crop_y = intval( ($src_height-$crop_h)/2 );
            }

            $image = $this->image;
            $canvas = new Imagick();

            if($this->type=='gif')
            {
                $images = $image->coalesceImages();
                foreach($images as $frame){
                    $img = new Imagick();
                    $img->readImageBlob($frame);
                    $img->cropImage($crop_w, $crop_h, $crop_x, $crop_y);
                    $img->thumbnailImage( $width, $height, true );

                    $canvas->addImage( $img );
                    $canvas->setImageDelay( $img->getImageDelay() );
                    $canvas->setImagePage($width, $height, 0, 0);
                }
            }
            else
            {
                $image->cropImage($crop_w, $crop_h, $crop_x, $crop_y);
                $image->thumbnailImage( $width, $height, true );
                $canvas->addImage( $image );
                $canvas->setImagePage($width, $height, 0, 0);
            }
            $image->destroy();
            $this->image = $canvas;
        }

    }




    // 添加水印圖片
    public function add_watermark($path, $x = 0, $y = 0)
    {
        $watermark = new Imagick($path);
        $draw = new ImagickDraw();
        $draw->composite($watermark->getImageCompose(), $x, $y, $watermark->getImageWidth(), $watermark->getimageheight(), $watermark);

        if($this->type=='gif')
        {
            $image = $this->image;
            $canvas = new Imagick();
            $images = $image->coalesceImages();
            foreach($image as $frame)
            {
                $img = new Imagick();
                $img->readImageBlob($frame);
                $img->drawImage($draw);

                $canvas->addImage( $img );
                $canvas->setImageDelay( $img->getImageDelay() );
            }
            $image->destroy();
            $this->image = $canvas;
        }
        else
        {
            $this->image->drawImage($draw);
        }
    }


    // 添加水印文字
    public function add_text($text, $x = 0 , $y = 0, $angle = 0, $style = array())
    {
        //$width = $this->image->getImageWidth()-$x;
        //$height = $this->image->getImageHeight()-$y;
        //if($width<=0 || $height<=0) return;
        $draw = new ImagickDraw();
        $draw->setgravity(imagick::GRAVITY_SOUTHWEST);
        if(isset($style['font'])) $draw->setFont($style['font']);
        if(isset($style['font_size'])) $draw->setFontSize($style['font_size']);  //字體大小
        if(isset($style['fill_color'])) $draw->setFillColor($style['fill_color']); // 字體顔色
        if(isset($style['under_color'])) $draw->setTextUnderColor($style['under_color']);

        if($this->type=='gif')
        {
            foreach($this->image as $frame)
            {
                $frame->annotateImage($draw, $x, $y, $angle, $text);
            }
        }
        else
        {
            $this->image->annotateImage($draw, $x, $y, $angle, $text);
        }
    }

    /**
     * 合成图片
     */
    public function compositeImage($x = 0 , $y = 0, $path, $composite_x = 100, $composite_y = 115)
    {
        $image = new Imagick($path);
        $image->adaptiveResizeImage($composite_x, $composite_y);

        $this->image->compositeImage($image, Imagick::COMPOSITE_OVER, $x, $y);
    }

    /**
     * 把front和back合成到画布上
     *
     * @param    integer    $bg_width    画布的宽度
     * @param    integer    $bg_heitht   画布的高度
     * @param    integer    $bg_color    画布的颜色
     */
    public function compositeBothImage($bg_width = 595, $bg_height = 842, $bg_color = 'white', $front, $back)
    {
        //构建正反两面图片的画布
        $this->image->newimage($bg_width, $bg_height, $bg_color);
        $this->image->setimageformat('png');

        $bg_front = new Imagick($front);
        $bg_front->adaptiveResizeImage(320, 240);
        $this->image->compositeimage($bg_front, Imagick::COMPOSITE_OVER, 120, 110);

        $bg_back = new Imagick($back);
        $bg_back->adaptiveResizeImage(320, 240);
        $this->image->compositeimage($bg_back, Imagick::COMPOSITE_OVER, 120, 450);
    }

    // 保存到指定路徑
    public function save_to($path)
    {
        $result = false;
        if($this->type=='gif')
        {
            $result = $this->image->writeImages($path, true);
        }
        else
        {
            $result = $this->image->writeImage($path);
        }
        return $result;
    }

    // 輸出圖像
    public function output($header = true)
    {
        if($header) header('Content-type: '.$this->type);
        echo $this->image->getImagesBlob();
    }


    public function get_width()
    {
        $size = $this->image->getImagePage();
        return $size['width'];
    }

    public function get_height()
    {
        $size = $this->image->getImagePage();
        return $size['height'];
    }

    // 設置圖像類型， 默認與源類型一致
    public function set_type( $type='png' )
    {
        $this->type = $type;
        $this->image->setImageFormat( $type );
    }

    // 獲取源圖像類型
    public function get_type()
    {
        return $this->type;
    }


    // 當前對象是否爲圖片
    public function is_image()
    {
        if( $this->image )
            return true;
        else
            return false;
    }



    // 生成縮略圖 $fit爲真時將保持比例並在安全框 $width X $height 內生成縮略圖片
    public function thumbnail($width = 100, $height = 100, $fit = true)
    { 
        $this->image->thumbnailImage( $width, $height, $fit );
    }

    /*
    添加一個邊框
    $width: 左右邊框寬度
    $height: 上下邊框寬度
    $color: 顔色: RGB 顔色 'rgb(255,0,0)' 或 16進制顔色 '#FF0000' 或顔色單詞 'white'/'red'...
     */
    public function border($width, $height, $color='rgb(220, 220, 220)')
    {
        $color=new ImagickPixel();
        $color->setColor($color);
        $this->image->borderImage($color, $width, $height);
    }

    // 模糊
    public function blur($radius, $sigma)
    {
        $this->image->blurImage($radius, $sigma);
    } 

    // 高斯模糊
    public function gaussian_blur($radius, $sigma)
    {
        $this->image->gaussianBlurImage($radius, $sigma);
    }

    // 運動模糊
    public function motion_blur($radius, $sigma, $angle)
    {
        $this->image->motionBlurImage($radius, $sigma, $angle);
    }

    // 徑向模糊
    public function radial_blur($radius)
    {
        $this->image->radialBlurImage($radius);
    }

    // 添加噪點
    public function add_noise($type=null)
    {
        $this->image->addNoiseImage($type==null?imagick::NOISE_IMPULSE:$type);
    }

    // 調整色階
    public function level($black_point, $gamma, $white_point)
    {
        $this->image->levelImage($black_point, $gamma, $white_point);
    }

    // 調整亮度、飽和度、色調
    public function modulate($brightness, $saturation, $hue)
    {
        $this->image->modulateImage($brightness, $saturation, $hue);
    }

    // 素描
    public function charcoal($radius, $sigma)
    {
        $this->image->charcoalImage($radius, $sigma);
    }

    // 油畫效果
    public function oil_paint($radius)
    {
        $this->image->oilPaintImage($radius);
    }

    // 水平翻轉
    public function flop()
    {
        $this->image->flopImage();
    }

    // 垂直翻轉
    public function flip()
    {
        $this->image->flipImage();
    }

}

?>
