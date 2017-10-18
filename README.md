Yii2钉钉扩展
========
[![Total Downloads](https://poser.pugx.org/jasonzhangxian/yii2-dingtalk-corp/downloads.png)](https://packagist.org/packages/jasonzhangxian/yii2-dingtalk-corp)

基于官方demo改写的，参考了[buptlsp](https://github.com/buptlsp/yii2-dingtalk)的一些写法

本扩展提供了对钉钉API接口的常规访问，具体的API页面访问[钉钉API接口](https://open-doc.dingtalk.com/)

使用了一些其他的包，[nategood/httpful](https://github.com/nategood/httpful)，[alexandernst/yii2-device-detect](https://github.com/alexandernst/yii2-device-detect)
```
"nategood/httpful": "*",
"alexandernst/yii2-device-detect": "0.0.11"
```
安装
------------

推荐的方式是通过composer 进行下载安装[composer](http://getcomposer.org/download/)。  

在命令行执行  

```
php composer.phar require --prefer-dist jasonzhangxian/yii2-dingtalk-corp "*"
```

或加入

```
"jasonzhangxian/yii2-dingtalk-corp": "*"
```

到你的`composer.json`文件中的require段。  


使用
-----

安装了这个插件，你就需要在配置文件中加入如下的代码：  


```php
return [
    'components' => [
    	//钉钉接口
        'dingtalk' => [
             'class' => '\jasonzhangxian\dingtalk\Dingtalk',
             'agentid' => '', //您的应用的agentid 
             'corpid' => '',  //您的企业corpid
             'corpsecret' => '', //您的企业的corpsecret
        ],
        //钉钉扫码登录
        'dingtalksns' => [
            'class' => '\jasonzhangxian\dingtalk\DingtalkSns',
            'appid' => "",//扫码登录申请的appid
            'appsecret' => "",//扫码登录申请的appsecret
            'redirect_uri' => "",//扫码登录跳转地址
        ],
        //判断设备类型
        'devicedetect' => [
            'class' => 'alexandernst\devicedetect\DeviceDetect'
        ],
        //Yii缓存
        'cache'         => [
            'class' => 'yii\caching\FileCache',
        ],
        // .... 
    ],   
];
```
在配置好之后：   

扫码登录的实现，前端页面添加：
```php
echo \jasonzhangxian\dingtalk\JsSnsConfig::widget([
    'container_id' => "login_container", //二维码容器ID，你需要在页面增加对应的html代码
]);
```
后端redirect_uri的代码：
```php
$code = Yii::$app->request->get('code');
//通过临时授权码获取用户信息
$user_info = Yii::$app->dingtalksns->getUserByCode($code);
//根据用户信息，执行登录
.
.
.
```
钉钉接口调用：
```php
//获取部门列表
$department_list = Yii::$app->dingtalk->run('/department/list');

//发送消息
$userid = '';//接收消息的用户
$response = Yii::$app->dingtalk->run('/message/send', [], ['touser'=>$userid,'agentid'=>Yii::$app->dingtalk->agentid,'msgtype'=>'text','text'=>['content'=>'Hello World!']]);
```

JsApi
参考的[buptlsp](https://github.com/buptlsp/yii2-dingtalk)的写法
```php
echo \jasonzhangxian\dingtalk\JsapiConfig::widget([
    'jsApiList' => ["runtime.permission.requestAuthCode"], //本页面需要使用的jsapi,本例中为免登服务
    'successJs' => 'function(){ //jsapi配置好后执行的JS回调，我们可以在此处开始写执行的代码
         '.(!\Yii::$app->devicedetect->isMobile()?'DingTalkPC':'dd').'.runtime.permission.requestAuthCode({
             corpId: "'.\Yii::$app->dingtalk->corpid.'",
             onSuccess: function(result) {
                 $.ajax({
                     url: "", //此处填上根据code登录的url
                     data: {
                         code: result.code
                     },
                     success: function(data){  //处理成功请求
                     },
                 });
             },
             onFail : function(err) {
                 //alert(err.errmsg);
             }
         });
    }',
    //'errorJs' => 'function(){}', //错误时的JS,默认会输出错误的信息
]);
```

回调
使用了Yii2的behaviors功能，在controller里面设置：
```php
    public $layout = false;
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'callbackbehavior' => [
                'class' => 'jasonzhangxian\dingtalk\behaviors\CallbackBehavior',
                '_token' => 'your token',
                '_encodeing_aes_key' => 'your encodeing_aes_key',
                '_suite_key' => 'your corpid',
                'actions' => ['index']//回调入口action
            ],
        ];
    }
    
    public function actionIndex()
    {
        $data = Yii::$app->request->getBodyParams();
        
        //用于注册回调时 返回加密后的success字符串
        if (isset($data['EventType']) && $data['EventType'] == "check_url") {
            echo json_encode($data['encryptMsg']);
        }else{
            //处理回调的逻辑代码
        }
    }
```