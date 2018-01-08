<?php

namespace Aliyun\Sms;

class Core
{
    /**
     * 配置信息
     * @var
     */
    protected $config;

    /**
     * 接口请求域名
     * @var string
     */
    protected $url = 'dysmsapi.aliyuncs.com';

    /**
     * 生成签名并发起请求
     *
     * @param $params array API具体参数
     * @param $security boolean 使用https
     * @return bool|\stdClass 返回API接口调用结果，当发生错误时返回false
     */
    public function request($params, $security = false)
    {
        $params['SignatureMethod'] = 'HMAC-SHA1';
        $params['SignatureNonce'] = uniqid(mt_rand(0, 0xffff), true);
        $params['SignatureVersion'] = '1.0';
        $params['AccessKeyId'] = $this->config['accessKeyId'];
        $params['Timestamp'] = gmdate("Y-m-d\TH:i:s\Z");
        $params['Format'] = 'JSON';
        ksort($params);

        $sortedQueryStringTmp = "";
        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }

        $stringToSign = "GET&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));
        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $this->config['accessKeySecret'] . "&", true));
        $signature = $this->encode($sign);
        $url = ($security ? 'https' : 'http') . "://{$this->url}/?Signature={$signature}{$sortedQueryStringTmp}";

        try {
            $content = $this->fetchContent($url);
            return json_decode($content);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 编码
     * @param $str
     * @return mixed|string
     */
    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    /**
     * 调用请求接口
     * @param $url
     * @return mixed
     */
    private function fetchContent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-sdk-client" => "php/2.0.0"
        ));
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $rtn = curl_exec($ch);
        if ($rtn === false) {
            trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);
        return $rtn;
    }
}