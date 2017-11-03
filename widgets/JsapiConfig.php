<?php
namespace jasonzhangxian\dingtalk;

use jasonzhangxian\dingtalk\assets\DingtalkAsset;
use jasonzhangxian\dingtalk\assets\DingtalkPcAsset;
use \Yii;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\web\AssetBundle;

class JsapiConfig extends Widget
{

    public $dingtalk = 'dingtalk';
    public $successJs;
    public $errorJs;
    public $jsApiList = [];

    public function init()
    {
        if (is_string($this->dingtalk)) {
            $this->dingtalk = Yii::$app->get($this->dingtalk);
        } elseif (is_array($this->dingtalk)) {
            if ( !isset($this->dingtalk['class'])) {
                $this->dingtalk['class'] = Dingtalk::className();
            }
            $this->dingtalk = Yii::createObject($this->dingtalk);
        }
        if ( !$this->dingtalk instanceof Dingtalk) {
            throw new InvalidConfigException("钉钉配置错误");
        }
        if (empty($this->errorJs)) {
            $this->errorJs = "function(error){alert(error.message);}";
        }
    }

    public function run()
    {
        $view = $this->getView();
        if ( !\Yii::$app->devicedetect->isMobile()) {
            $dd = "DingTalkPC";
            DingtalkPCAsset::register($view);
        } else {
            $dd = "dd";
            DingtalkAsset::register($view);
        }
        $js = "$dd.config(" . str_replace(",", ",\r\n", $this->dingtalk->getConfig($this->jsApiList)) . ");
        $dd.ready(" . $this->successJs . ");
        $dd.error(" . $this->errorJs . ");";
        $view->registerJs($js);
    }
}
