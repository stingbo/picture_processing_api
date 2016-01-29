<?php

/**
 * 公共使用的类
 *
 * @author ${bobo}
 */
class Common {

    /**
     * 解密公钥路径
     *
     * @var string
     */
    protected $publicKeyFilePath = __DIR__ . '/../assets/cert';

    /**
     * CURL 请求
     */
    public function request($url, $params = array(), $method = 'GET', $multi = false, $extheaders = array()) {
        if(!function_exists('curl_init')) exit('Need to open the curl extension');

        $method = strtoupper($method);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-SDK OAuth2.0');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $headers = (array)$extheaders;

        switch ($method) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, TRUE);
            if (!empty($params)){
                if($multi)
                {
                    foreach($multi as $key => $file)
                    {
                        $params[$key] = '@' . $file;
                    }
                    @curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                    $headers[] = 'Expect: ';
                }
                else
                {
                    @curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                    $headers[] = 'Expect: ';
                    // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                }
            }
            break;
        case 'DELETE':
        case 'GET':
            $method == 'DELETE' && curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if (!empty($params))
            {
                $url = $url . (strpos($url, '?') ? '&' : '?')
                    . (is_array($params) ? http_build_query($params) : $params);
            }
            break;
        }

        curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);

        if($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
        }

        $response = curl_exec($ch);
        curl_close ($ch);

        return $response;
    }

    /**
     * 返回身份证有效期信息(手动计算)
     *
     * @param    string    $idcard_no    身份证号
     * @return   array
     */
    public function getExpired($idcard_no) {
        $expired = [];

        $birthday  = substr($idcard_no, 6, 4);

        //生成随机月
        $month = rand(1, 12);
        if ($month < 10) {
            $month = '.0' . $month;
        } else {
            $month = '.' . $month;
        }

        //生成随机天
        $day = rand(1, 28);
        if ($day < 10) {
            $day = '.0' . $day;
        } else {
            $day = '.' . $day;
        }

        //按年龄差生成随机年
        $new_year = date('Y');
        $diff = $new_year - $birthday;
        if ($diff < 16) {
            $expired['expired_start'] = $birthday + ($diff - 1) . $month . $day;
            $expired['expired_end']   = $birthday + ($diff + 4) . $month . $day;
        } elseif ($diff >= 16 && $diff <= 26) {
            $year = rand(1, 5);
            $expired['expired_start'] = $birthday + ($diff - $year) . $month . $day;
            $expired['expired_end']   = $birthday + ($diff - $year + 10) . $month . $day;
        } elseif ($diff > 26 && $diff <= 46) {
            $year = rand(3, 10);
            $expired['expired_start'] = $birthday + ($diff - $year) . $month . $day;
            $expired['expired_end']   = $birthday + ($diff - $year + 20) . $month . $day;
        } else {
            $year = rand(1, 10);
            $expired['expired_start'] = $birthday + ($diff - $year) . $month . $day;
            $expired['expired_end']   = '长期';
        }

        return $expired;
    }

    /**
     * 使用公钥加密信息
     */
    public function decrypt($client, $encryptData) {

        //公钥文件的路径
        $publicKeyFilePath = $this->publicKeyFilePath . '/' . $client . '.pem';

        if(!extension_loaded('openssl')) return false;

        if(!file_exists($publicKeyFilePath)) return false;

        //生成Resource类型的公钥，如果公钥文件内容被破坏，openssl_pkey_get_public函数返回false
        $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyFilePath));

        if(!$publicKey) return false;

        if (openssl_public_decrypt($encryptData, $decryptData, $publicKey)) {
            return $decryptData;
        } else {
            return false;
        }
    }

    /**
     * 读取文件前几个字节 判断文件类型
     *
     * @param    string    $filename    文件的名称
     * @return   string
     */
    public function getFileType($filename) {
        $file = fopen($filename,'rb');

        //只读2字节
        $bin = fread($file, 2);

        fclose($file);

        $strInfo = @unpack("c2chars", $bin);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
        $fileType = '';

        switch ($typeCode) {
            case 7790:
                $fileType = 'exe';
                break;
            case 7784:
                $fileType = 'midi';
                break;
            case 8297:
                $fileType = 'rar';
                break;
            case 255216:
                $fileType = 'jpg';
                break;
            case 7173:
                $fileType = 'gif';
                break;
            case 6677:
                $fileType = 'bmp';
                break;
            case 13780:
                $fileType = 'png';
                break;
            default:
                $fileType = 'unknown'.$typeCode;
                break;
        }

        //Fix
        if ($strInfo['chars1']=='-1' && $strInfo['chars2']=='-40') {
            return 'jpg';
        }

        if ($strInfo['chars1']=='-119' && $strInfo['chars2']=='80') {
            return 'png';
        }

        return $fileType;
    }
}
