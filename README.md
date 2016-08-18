Yii2钉钉扩展
========
基于官方demo改写的，参考了/buptlsp/yii2-dingtalk的一些写法
本框架提供了对钉钉API接口的常规访问，具体的API页面访问[钉钉API接口](https://open-doc.dingtalk.com/)

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
        'dingtalk' => [
             'class' => '\jasonzhangxian\dingtalk\Dingtalk',
             'agentid' => '', //您的应用的agentid 
             'corpid' => '',  //您的企业corpid
             'corpsecret' => '', //您的企业的corpsecret
        ],
        'dingtalksns' => [
            'class' => '\jasonzhangxian\dingtalk\DingtalkSns',
            'appid' => "",//扫码登录申请的appid
            'appsecret' => "",//扫码登录申请的appsecret
            'redirect_uri' => "",//扫码登录跳转地址
        ],
        // .... 
    ],   
];
```
在配置好之后：   
```php
$data = Yii::$app->dingtalk->run('/department/list');
```

扫码登录的实现：
前端添加：
```php
echo \jasonzhangxian\dingtalk\JsSnsConfig::widget([
    'container_id' => "login_container", //二维码容器ID，你需要在页面增加对应的html代码
]);
```
后端实现：
```php
$code = Yii::$app->request->get('code');
//通过临时授权码获取用户信息
$user_info = Yii::$app->dingtalksns->getUserByCode($code);
//根据用户信息，执行登录

```