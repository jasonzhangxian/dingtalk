<?php
namespace jasonzhangxian\dingtalk\behaviors;

use yii\base\Behavior;
use yii\di\Instance;
use yii\log\FileTarget;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use Yii;
use jasonzhangxian\dingtalk\crypto\DingtalkCrypt;

Class CallbackBehavior extends Behavior
{

    public $actions = [];
    public $_token;
    public $_encodeing_aes_key;
    public $_suite_key;

    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }

    //在beforeAction之前处理
    public function beforeAction($event)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $action = $event->action->id;
        if (in_array($action, $this->actions)) {
            $data = Yii::$app->request->rawBody;
            $getArr = Yii::$app->request->get();
            if (empty($getArr['signature']) || empty($getArr['timestamp']) || empty($getArr['nonce'])) {
                $this->outputError("dingtalk callback params error");

                return "";
            }
            $crypt = new DingtalkCrypt;
            $crypt->dingtalkCrypt($this->_token, $this->_encodeing_aes_key, $this->_suite_key);
            $encrypt = ArrayHelper::getValue(json_decode($data), "encrypt", "");
            $msg = "";
            $errCode = $crypt->decryptMsg($getArr['signature'], $getArr['timestamp'], $getArr['nonce'], $encrypt,
                $msg);
            if ($errCode == 0) {
                //记录日志
                $msg = json_decode($msg, true);
                //是否为创建套件
                if ($msg['EventType'] == "check_url") {
                    $encryptMsg = "";
                    $errCode = $crypt->encryptMsg("success", $getArr['timestamp'], $getArr['nonce'], $encryptMsg);
                    if ($errCode == 0) {
                        $msg['encryptMsg'] = json_decode($encryptMsg, true);
                    }
                }
                Yii::$app->request->setBodyParams($msg);

                return true;
            } else {
                $this->outputError("dingtalk callback decrypt error:" . $errCode);

                return true;
            }
        }
    }

    private function outputError($ret)
    {
        Yii::$app->response->data = $ret;
        Yii::$app->response->send();
        exit();
    }

}