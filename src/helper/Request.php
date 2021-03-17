<?php

class Request
{
    /**
     * POST 请求
     *
     * @param $url
     * @param $params
     * @param string $cookie
     * @param array $headers
     * @return bool|string
     *
     * @throws Exception
     */
    public static function post($url, $params, $cookie = '', $headers = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        // 302 相关
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $res = curl_exec($ch);
        if (!$res) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);

        return $res;
    }


    /**
     * GET 请求
     *
     * @param $url
     * @param string $cookie
     * @param bool $isSaveCookie
     * @return bool|string
     *
     * @throws Exception
     */
    public static function get($url, $cookie = '', $isSaveCookie = false)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 302 相关 (重要)
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($cookie) {
            if ($isSaveCookie) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
            } else {
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
            }
        }

        $res = curl_exec($ch);
        if (!$res) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);

        return $res;
    }

}