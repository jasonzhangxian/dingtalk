<?php

namespace jasonzhangxian\dingtalk\assets;

use yii\web\AssetBundle;

/**
 * dingtalk qr-code login asset bundle.
 */
class DingtalkSnsAsset extends AssetBundle
{
    public $js = [
        '//g.alicdn.com/dingding/dinglogin/0.0.5/ddLogin.js',
    ];
}
