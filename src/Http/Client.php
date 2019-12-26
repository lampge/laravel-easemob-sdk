<?php

namespace lampge\Easemob\Http;

/**
 * Date: 2019/7/13 17:51
 * Copyright (c) lampge <lampge@sina.com>
 */

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use lampge\Easemob\Facade as Easemob;
use lampge\Easemob\Services\BaseService;
use GuzzleHttp\Client as GuzzleClient;

class Client
{
    /**
     * @param $url
     * @param array $data
     * @param int $second
     * @param array $header
     * @param string $method
     * @return Response|array
     * @author lampge
     */
    public static function auth($method = 'POST', $url, $data = [], $second = 30, $header = [])
    {
        $method                  = strtoupper($method);
        $token                   = Easemob::user()->getAccessToken();
        $header['Authorization'] = 'Bearer ' . $token;
        /**
         * @var Client $client
         */
        $client  = app('easemob.http');
        $options = [
            'headers' => $header,
            'timeout' => $second
        ];

        if ($method == 'GET' && !empty($data)) {
            $url .= (stripos($url, '?') === false ? '?' : '&');
            $url .= (is_array($data) ? http_build_query($data) : $data);
        }
        if ($method != 'GET' && !empty($data)) {
            $options['json'] = $data;
        }
        if (config('app.debug')) {
            Log::info(str_repeat('-', 120));
            Log::debug('环信请求信息:', $options);
        }
        $response = $client->request($method, $url, $options);
        if ($response->getStatusCode() == 401) {
            Cache::pull(BaseService::CACHE_NAME);
        }
        $content = $response->getBody()->getContents();
        if (config('app.debug')) {
            Log::debug($url);
            Log::debug($content);
            Log::info(str_repeat('-', 120));
        }
        // return $response->getStatusCode() == 200 ? \GuzzleHttp\json_decode($content, 1) : $response;
        if($response->getStatusCode() == 200){
            return \GuzzleHttp\json_decode($content, 1);
        }else{
            return false;
        }
    }

    public static function json($url, $data = [], $second = 30, $header = [])
    {
        /**
         * @var GuzzleClient $client
         */
        $client   = app('easemob.http');
        $response = $client->post($url, [
            'headers' => $header,
            'timeout' => $second,
            'json'    => $data,
        ]);
        return $response->getStatusCode() == 200 ? $response->getBody()->getContents() : false;
    }

    /**
     * http请求
     *
     * @param        $url
     * @param        $option
     * @param int    $header
     * @param string $type
     * @param int    $setopt
     * @param bool   $is_json [返回数据是否是json 下载文件的时候用到]
     *
     * @return mixed
     * @throws EasemobError
     */
    public static function postCurl($type = 'POST', $url, $option, $setopt = 10, $header = 0, $is_json = true)
    {
        $curl = curl_init (); // 启动一个CURL会话
        if (! empty ( $option )) {
            if($type == "GET" ){
                $url .= '?'.http_build_query($option);
            }else{
                $options = json_encode ( $option );
                curl_setopt ( $curl, CURLOPT_POSTFIELDS, $options ); // Post提交的数据包
            }
        }
        curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE ); // 对认证证书来源的检查
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE ); // 从证书中检查SSL加密算法是否存在
        curl_setopt ( $curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)' ); // 模拟用户使用的浏览器
        curl_setopt ( $curl, CURLOPT_TIMEOUT, $setopt ); // 设置超时限制防止死循环
        if(empty($header)){
            $header = array();
        }
        curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header ); // 设置HTTP头
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
        curl_setopt ( $curl, CURLOPT_CUSTOMREQUEST, $type );
        $result = curl_exec ( $curl ); // 执行
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close ( $curl ); // 关闭CURL会话
        if($status == 401){
            Cache::pull(BaseService::CACHE_NAME);
        }
        if($status !== 200){
            $return_array = json_decode($result,true);
            if($return_array){
                $error_message = '请求错误:' ;
                if(isset($return_array['error']))
                    $error_message .= $return_array['error'];
                if(isset($return_array['error_description']))
                    $error_message .= ' '.$return_array['error_description'];
            }else{
                $error_message = '请求错误!' ;
            }
            throw new EasemobError($error_message,$status);
        }
        // 在下载文件的时候 不是json
        if($is_json){
            $result_array = json_decode($result,true);
        }else{
            $result_array = $result;
        }
        return $result_array;
    }
}
