<?php
namespace jasonzhangxian\dingtalk;

use Yii;

require_once(__DIR__ . "/../../nategood/httpful/bootstrap.php");

Class Http
{

    public static function get($path, $params)
    {
        $url = self::joinParams($path, $params);
        $response = \Httpful\Request::get($url)->send();
        if ($response->hasErrors()) {
            var_dump($response);
        }
        if ( !isset($response->body->errcode) || $response->body->errcode != 0) {
            var_dump($response->body);
        }

        return $response->body;
    }
    
    
    public static function post($path, $params, $data)
    {
        $url = self::joinParams($path, $params);
        $response = \Httpful\Request::post($url)
            ->body($data)
            ->sendsJson()
            ->send();
        if ($response->hasErrors()) {
            var_dump($response);
        }
        if ( !isset($response->body->errcode) || $response->body->errcode != 0) {
            var_dump($response->body);
        }

        return $response->body;
    }
    
    
    private static function joinParams($path, $params)
    {
        $dingtalk = Yii::$app->get((Yii::$app->has('dingtalk') ? 'dingtalk' : 'dingtalksns'));
        $url = $dingtalk->protocol . "://" . $dingtalk->host . $path;
        if (count($params) > 0) {
            $url = $url . "?";
            foreach ($params as $key => $value) {
                $url = $url . $key . "=" . $value . "&";
            }
            $length = count($url);
            if ($url[$length - 1] == '&') {
                $url = substr($url, 0, $length - 1);
            }
        }

        return $url;
    }
}