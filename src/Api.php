<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2018/1/8
 * Time: 下午8:12
 */

namespace Aliyun\Sms;

class Api extends Core
{
    protected $config;

    /**
     * 注入配置信息
     * Api constructor.
     * @param array $config
     */
    public function __construct(Array $config)
    {
        $this->config = $config;
    }

    /**
     * 设置短信模板码
     * 如果没有模板则使用默认配置模板码
     * @param array $param
     * @param string $code
     * @return $this
     */
    public function setTemplate($param = [], $code = '')
    {
        $code || $this->config['templateCode'] = $this->config['defaultTemplate'];
        $this->config['templateParam'] = $param;
        return $this;
    }

    /**
     * 发送短信
     * @param $phone
     * @return bool|\stdClass
     */
    public function send($phone)
    {
        $params = [
            'PhoneNumbers' => $phone,
            'SignName' => $this->config['signName'],
            'TemplateCode' => $this->config['templateCode'],
            'TemplateParam' => json_encode($this->config['templateParam'], JSON_UNESCAPED_UNICODE),
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
            //'OutId' => '',//设置发送短信流水号
            //'SmsUpExtendCode' => ''//上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        ];
        return $this->request($params);
    }

    /**
     * 获取指定手机号近期的发送记录
     * @param $phone
     * @param string $date
     * @param int $page_nums
     * @param int $page
     * @return bool|\stdClass
     */
    public function getSendDetail($phone, $date = '', $page_nums = 15, $page = 1)
    {
        $params = [
            'PhoneNumbers' => $phone,
            'SendDate' => $date ?: date('Ymd'),
            'PageSize' => $page_nums,
            'CurrentPage' => $page,
            "RegionId" => "cn-hangzhou",
            "Action" => "QuerySendDetails",
            "Version" => "2017-05-25",
            //'BizId' => $this->config['code'], //设置发送短信流水号
        ];
        return $this->request($params);
    }


}