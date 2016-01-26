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

        $year  = substr($idcard_no, 6, 4);
        $month = substr($idcard_no, 10, 2);
        $day   = substr($idcard_no, 12, 2);

        $birthday = $year . '.' . $month . '.' . $day;
        $new = date('Y.m.d');
        $diff = $new - $birthday;

        $expired_day = rand(1, 28);
        if ($expired_day < 10) {
            $expired_day = '.0' . $expired_day; 
        } else {
            $expired_day = '.' . $expired_day; 
        }

        if ($diff < 16) {
            $expired['expired_start'] = $birthday + ($diff - 1) . $expired_day;
            $expired['expired_end']   = $birthday + ($diff + 4) . $expired_day;
        } elseif ($diff >= 16 && $diff <= 26) {
            $year = rand(1, 5);
            $expired['expired_start'] = $birthday + ($diff - $year) . $expired_day;
            $expired['expired_end']   = $birthday + ($diff - $year + 10) . $expired_day;
        } elseif ($diff > 26 && $diff <= 46) {
            $year = rand(3, 10);
            $expired['expired_start'] = $birthday + ($diff - $year) . $expired_day;
            $expired['expired_end']   = $birthday + ($diff - $year + 20). $expired_day;
        } else {
            $year = rand(1, 10);
            $expired['expired_start'] = $birthday + ($diff - $year) . $expired_day;
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
}
