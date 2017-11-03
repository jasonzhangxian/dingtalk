<?php
namespace jasonzhangxian\dingtalk;

use jasonzhangxian\dingtalk\assets\DingtalkSnsAsset;
use \Yii;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\web\AssetBundle;

class JsSnsConfig extends Widget
{

    public $dingtalk = 'dingtalksns';
    public $container_id;
    public $style = "border:none;background-color:#FFFFFF";
    public $width = 365;
    public $height = 400;

    public function init()
    {
        if (is_string($this->dingtalk)) {
            $this->dingtalk = Yii::$app->get($this->dingtalk);
        } elseif (is_array($this->dingtalk)) {
            if ( !isset($this->dingtalk['class'])) {
                $this->dingtalk['class'] = DingtalkSns::className();
            }
            $this->dingtalk = Yii::createObject($this->dingtalk);
        }
        if ( !$this->dingtalk instanceof DingtalkSns) {
            throw new InvalidConfigException("钉钉配置错误");
        }
    }


    public function run()
    {
        $view = $this->getView();
        DingtalkSnsAsset::register($view);
        $js = "
        var appid = '" . $this->dingtalk->appid . "';
        var jsapi_host = '" . ($this->dingtalk->protocol . "://" . $this->dingtalk->host) . "';
        var redirect_uri = '" . $this->dingtalk->redirect_uri . "';
        var common_url = 'appid=' + appid + '&response_type=code&scope=snsapi_login&state=STATE&redirect_uri='+encodeURIComponent(redirect_uri);
        var qrconnect_url = jsapi_host + '/connect/qrconnect?' + common_url;
        var obj = DDLogin({
               id:'" . $this->container_id . "',
               goto: encodeURIComponent(qrconnect_url),
               style: '" . $this->style . "',
               href: '',
               width : '" . $this->width . "',
               height: '" . $this->height . "'
            });
        var hanndleMessage = function (event) {
            var origin = event.origin;
            if( origin == 'https://login.dingtalk.com' ) {
                var loginTmpCode = event.data;
                var oauth2_url = jsapi_host + '/connect/oauth2/sns_authorize?' + common_url;
                window.location.href= oauth2_url + '&loginTmpCode=' + loginTmpCode;
            }
        };
        if (typeof window.addEventListener != 'undefined') {
            window.addEventListener('message', hanndleMessage, false);
        } else if (typeof window.attachEvent != 'undefined') {
            window.attachEvent('onmessage', hanndleMessage);
        }";
        $view->registerJs($js);
    }
}
