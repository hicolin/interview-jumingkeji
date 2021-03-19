<?php

class SimulateCrawl
{
    // 百度 OCR
    private $appKey = 'MULrmCc0jhSby1Bc21ltPX28';
    private $secretKey = '6xNQ2zr3vMIB0i8Mq9GhmUczcUeDTkdS';
    private $accessToken;

    // 橙米帐户
    private $domain = 'https://www.chengmi.cn';
    private $username;
    private $password;

    private $loginToken;
    private $imgPath;
    private $captcha;
    private $cookie;

    public function __construct($username, $password)
    {
        require_once __DIR__ . '/helper/Request.php';

        date_default_timezone_set('Asia/Shanghai');
        header("Content-Type: text/html;charset=utf-8");

        set_time_limit(0);

        $this->username = $username;
        $this->password = $password;
    }

    // 运行
    public function run()
    {
        try {
            echo '<pre>' . PHP_EOL;

            $this->gotoLoginPage();

            $this->getAccessToken();

            $this->login();

            $this->getBalance();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 下载验证码图片
     *
     * @throws Exception
     */
    private function downloadCaptcha()
    {
        $captchaUrl = $this->domain . '/member/code.aspx';
        $uploadDir = __DIR__ . '/uploads/images/' . date('Y-m-d');
        $imgName = time() . mt_rand(1000, 9999) . '.png';
        $tmpPath = $uploadDir . '/' . $imgName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $data = Request::get($captchaUrl, $this->cookie);
        file_put_contents($tmpPath, $data);

        $this->imgPath = $tmpPath;

        echo '下载验证码图片成功' . PHP_EOL;
    }

    /**
     * 获取 AccessToken
     *
     * @throws Exception
     */
    private function getAccessToken()
    {
        $tokenUrl = 'https://aip.baidubce.com/oauth/2.0/token';
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->appKey,
            'client_secret' => $this->secretKey,
        ];

        $res = Request::post($tokenUrl, $data);
        $res = json_decode($res, true);

        $this->accessToken = $res['access_token'];

        echo 'AccessToken 获取成功' . PHP_EOL;
    }

    /**
     * 识别验证码
     *
     * @throws Exception
     */
    private function getCaptcha()
    {
        if (!$this->accessToken) {
            throw new Exception('accessToken 不存在');
        }

        $captcha = '';
//        $ocrUrl = 'https://aip.baidubce.com/rest/2.0/ocr/v1/accurate_basic?access_token=' . $this->accessToken;
        $ocrUrl = 'https://aip.baidubce.com/rest/2.0/ocr/v1/general_basic?access_token=' . $this->accessToken;

        $i = 0;
        while (strlen($captcha) != 4) { // 4 位验证码
            if ($i > 0) {
                echo '验证码识别失败，重试中 ...' . PHP_EOL;
            }

            $i++;
            $this->downloadCaptcha();

            $img = base64_encode(file_get_contents($this->imgPath));
            $data = ['image' => $img];

            $res = Request::post($ocrUrl, $data);
            $res = json_decode($res, true);

            if (!isset($res['words_result'][0]['words']) || empty($res['words_result'][0]['words'])) {
                sleep(1);
                continue;
            }

            $captcha = $res['words_result'][0]['words'];
            preg_match_all("/[a-zA-Z]*\d*/", $captcha, $matches);
            $captcha = implode("", $matches[0]);

            sleep(1);
        }

        $this->captcha = $captcha;

        echo '验证码识别成功：' . $captcha . PHP_EOL;
    }

    /**
     * 进入登录界面，创建 Cookie
     *
     * @throws Exception
     */
    private function gotoLoginPage()
    {
        $loginUrl = $this->domain;
        $cookieDir = __DIR__ . '/cookie';

        if (!is_dir($cookieDir)) {
            mkdir($cookieDir, 0777, true);
        }
        $this->cookie = $cookieDir . '/cookie.txt';

        $res = Request::get($loginUrl, $this->cookie, true);
        preg_match_all("/<input type=\"hidden\" id=\"login_token\" value=\"(.*)\"/", $res, $matches);

        $this->loginToken = $matches[1][0];

        echo '进入首页登录界面' . PHP_EOL;
    }

    /**
     * 登录
     *
     * @throws Exception
     */
    private function login()
    {
        $res = '';
        $loginUrl = $this->domain . '/member/ajax/User.ashx';

        $i = 0;
        while (strpos($res, '10000') === false) {
            if ($i > 0) {
                echo "登录失败，重新下载验证码 ..." . PHP_EOL;
            }

            $i++;
            $this->getCaptcha();

            $data = [
                'username' => $this->username,
                'userpwd' => md5($this->password),
                'token' => $this->loginToken,
                'b_type' => '1',
                'lang' => '',
                'vifrom' => '',
                'code' => $this->captcha,
            ];

            $res = Request::post($loginUrl, $data, $this->cookie);
        }

        echo '登录成功' . PHP_EOL;
    }

    // 获取余额
    private function getBalance()
    {
        $userHomeUrl = $this->domain . '/userpanel';
        $res = Request::get($userHomeUrl, $this->cookie);

        $pageContent = $res;

        $pattern = "/<td height=\"36\" align=\"center\" class=\"hsac\" style=\"font-size: 18px;\">\s*(.*)\s*<\/td>/";
        preg_match_all($pattern, $pageContent, $matches);

        echo '获取账户余额成功：' . $matches[1][0] . PHP_EOL;
    }

}

$username = '811687790@qq.com';
$password = 'abc$123456';

$sc = new SimulateCrawl($username, $password);
$sc->run();
