<?php
// +---------------------------------
// | 腾讯防水墙插件管理控制器类
// +---------------------------------
// | Author: webx32 <www@webx32.com>
// +---------------------------------
return [
    'app_id'     => [
        'title' => '腾讯防水墙AppID',
        'type'  => 'text',
    	'value'=>'2050471674',
        'tip'   => '腾讯防水墙(<a href="https://007.qq.com" target="_blank">https://007.qq.com</a>)<a href="https://007.qq.com" target="_blank">免费申请</a>',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '腾讯防水墙AppID不能为空'
        ],
    ],
    'app_secret_key' => [
        'title' => '腾讯防水墙AppSecretKey',
        'type'  => 'text',
    	'value'=>'0XbEwIPLzclLesEDwdw******',
        'tip'   => '此插件有webx32通过，源码请访问<a href="https://github.com/webx32/thinkcmf-qq-captcha" target="_blank">https://007.qq.com</a>',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '腾讯防水墙AppSecretKey不能为空'
        ],
    ]
];
					