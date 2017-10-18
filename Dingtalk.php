<?php
namespace jasonzhangxian\dingtalk;

use \Yii;
use \yii\base\Component;
use yii\caching\Cache;
use jasonzhangxian\dingtalk\Http;


class Dingtalk extends Component
{

    public $corpid = "";
    public $corpsecret = "";
    public $agentid = "";
    public $host = "oapi.dingtalk.com";
    public $protocol = "https";
    public $cache;

    const DINGTALK_CACHEKEY = "dingtalk_cachekey";
    const DINGTALK_JSAPI_CACHEKEY = "dingtalk_jsapi_cachekey";

    public function init()
    {
        parent::init();

        $this->cache = Yii::$app->cache;
    }

    /**
     * 缓存accessToken。accessToken有效期为两小时，需要在失效前请求新的accessToken
     */
    public function getAccessToken()
    {
        $accessToken = $this->cache->get(self::DINGTALK_CACHEKEY);
        if ( !$accessToken) {
            $response = Http::get('/gettoken', array('corpid' => $this->corpid, 'corpsecret' => $this->corpsecret));
            $accessToken = $response->access_token;
            $this->cache->set(self::DINGTALK_CACHEKEY, $accessToken, 7000);
        }

        return $accessToken;
    }

    /**
     * 缓存jsTicket。jsTicket有效期为两小时，需要在失效前请求新的jsTicket
     */
    public function getTicket()
    {
        $jsticket = $this->cache->get(self::DINGTALK_JSAPI_CACHEKEY);
        if ( !$jsticket) {
            $response = Http::get('/get_jsapi_ticket',
                array('type' => 'jsapi', 'access_token' => $this->getAccessToken()));
            $jsticket = $response->ticket;
            $this->cache->set(self::DINGTALK_JSAPI_CACHEKEY, $jsticket, 7000);
        }

        return $jsticket;
    }

    /**
     * 通用查询
     */
    public function run($action, $params = array(), $postFields = array())
    {
        $params['access_token'] = $this->getAccessToken();
        if (empty($postFields)) {
            $response = Http::get($action, $params);
        } else {
            $response = Http::post($action, $params, $postFields);
        }

        return $response;
    }

    /**
     * 获取jsapi配置信息
     */
    public function getConfig($jsApiList = [])
    {
        $corpId = $this->corpid;
        $agentId = $this->agentid;
        $nonceStr = \Yii::$app->security->generateRandomString(10);
        $timeStamp = time();
        $url = $this->curPageURL();
        $ticket = $this->getTicket();
        //生成签名
        $signature = $this->sign($ticket, $nonceStr, $timeStamp, $url);
        //构造结果
        $config = array(
            'url' => $url,
            'nonceStr' => $nonceStr,
            'agentId' => $agentId,
            'timeStamp' => $timeStamp,
            'corpId' => $corpId,
            'signature' => $signature
        );
        if ( !empty($jsApiList)) {
            $config['jsApiList'] = $jsApiList;
        }

        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    /**
     * 组装并获取签名
     */
    public function sign($ticket, $nonceStr, $timeStamp, $url)
    {
        $plain = 'jsapi_ticket=' . $ticket .
            '&noncestr=' . $nonceStr .
            '&timestamp=' . $timeStamp .
            '&url=' . $url;

        return sha1($plain);
    }

    /**
     * 获取当前页面
     */
    private function curPageURL()
    {
        $pageURL = 'http';

        if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }


}
