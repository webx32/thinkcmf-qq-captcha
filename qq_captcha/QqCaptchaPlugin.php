<?php
// +---------------------------------
// | 腾讯防水墙插件管理控制器类
// +---------------------------------
// | Author: webx32 <www@webx32.com>
// +---------------------------------
namespace plugins\qq_captcha;

use cmf\lib\Plugin;

class QqCaptchaPlugin extends Plugin
{

    public $info = [
        'name'        => 'QqCaptcha',
        'title'       => '腾讯防水墙',
        'description' => '后台登录页验证码修改为腾讯防水墙',
        'status'      => 1,
        'author'      => 'webx32',
        'version'     => '1.0.0'
    ];

    public $hasAdmin = 0;//插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    public function adminLogin()
    {
        $config = $this->getConfig();
        $this->assign($config);
        return $this->fetch('widget');
    }

}