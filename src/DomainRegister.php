<?php

class DomainRegister
{
    // 阿里云
    public $domain = 'https://domain.aliyuncs.com';
    public $accessKeyId = 'xxx';
    public $accessKeySecret = 'xxx';

    public $commonUrl;
    public $signatureUrl;
    public $commonData;
    public $domainName;

    public function __construct($domainName)
    {
        require_once __DIR__ . '/helper/Request.php';

        header("Content-Type: text/html;charset=utf-8");

        $this->domainName = $domainName;
        $this->getCommonUrl();
    }

    // 运行
    public function run()
    {
        try {
            echo '<pre>';

            $this -> checkDomain();

            $this->createOrder();

        }catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    // 拼接公共 Url
    public function getCommonUrl()
    {
        // 不包括 Signature
        $commonData = [
            'Format' => 'JSON',
            'Version' => date('Y-m-d'),
            'AccessKeyId' => $this->accessKeyId,
            'SignatureMethod' => 'HMAC-SHA1',
            'Timestamp' => gmdate("Y-m-d\TH:i:s\Z"),
            'SignatureVersion' => '1.0',
            'SignatureNonce' => uniqid(),
        ];

        $str = http_build_query($commonData);
        $this->commonData = $commonData;
        $this->commonUrl = $this->domain . '?' . $str;
    }

    /**
     * 域名检测
     *
     * @throws Exception
     */
    public function checkDomain()
    {
        $checkData = [
            'Action' => 'CheckDomain',
            'DomainName' => $this->domainName,
        ];
        $data = array_merge($checkData, $this->commonData);

        $this->signatureUrl = $this->getSignatureUrl($data);
        $checkUrl = $this->signatureUrl . '&' . http_build_query($checkData);

        // $res = Request::get($checkUrl);
        // 模拟返回结果
        sleep(1);
        $res = '{
            "Name": "abc.com",
            "Avail": 1,
            "RequestId": "BA7A4FD4-EB9A-4A20-BB0C-9AEB15634DC1",
            "FeePeriod": 0
        }';
        $res = json_decode($res, true);

        if ($res['Avail'] != 1) {
            throw new Exception($res['Reason']);
        }

        echo "域名 {$this->domainName} 可注册" . PHP_EOL;
    }


    /**
     * 创建订单
     *
     * @throws Exception
     */
    public function createOrder()
    {
        $orderData = [
            'Action' => 'CreateOrder',
            'SubOrderParam.1.Action' => 'activate',
            'SubOrderParam.1.RelatedName' => $this->domainName,
            'SubOrderParam.1.Period' => 12,
            'SubOrderParam.1.DomainTemplateID' => '0000000',
        ];
        $data = array_merge($orderData, $this->commonData);

        $this->signatureUrl = $this->getSignatureUrl($data);
        $orderUrl = $this->signatureUrl . '&' . http_build_query($orderData);

        // $res = Request::get($orderUrl);
        // 模拟数据
        sleep(1);
        $res = '{
            "OrderID": "D201600000000000",
            "RequestId": "37675261-9687-488E-A980-42B6FDC48804"
        }';
        $res = json_decode($res, true);

        if (!isset($res['OrderID']) || empty($res['OrderID'])) {
            throw new Exception($res['Reason']);
        }

        echo "域名购买成功，订单号为：{$res['OrderID']}" . PHP_EOL;
    }

    // 获取签名后的 Url
    public function getSignatureUrl($data): string
    {
        $signature = $this->getSignature($data);
        return $this->commonUrl . '&Signature=' . $signature;
    }

    // 获取签名
    public function getSignature($params): string
    {
        ksort($params);
        $queryStr = '';

        foreach ($params as $k => $param) {
            $queryStr .= '&' . $this->percentEncode($k) . '=' . $this->percentEncode($param);
        }

        $strToSign = 'GET%2F&' . $this->percentEncode(substr($queryStr, 1));
        $signature = hash_hmac('sha1', $strToSign, $this->accessKeySecret . '&',true);

        return base64_encode($signature);
    }

    // 编码
    public function percentEncode($str)
    {
        $res = urlencode($str);

        $res = str_replace('+', '%20', $res);
        $res = str_replace('*', '%2A', $res);
        $res = str_replace('%7E', '~', $res);

        return $res;
    }

}

$domain = 'abc.com';
$dr = new DomainRegister($domain);
$dr->run();

