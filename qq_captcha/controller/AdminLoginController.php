<?php
// +---------------------------------
// | 腾讯防水墙插件管理控制器类
// +---------------------------------
// | Author: webx32 <www@webx32.com>
// +---------------------------------
namespace plugins\qq_captcha\controller;

use think\Db;
use cmf\controller\PluginBaseController;

class AdminLoginController extends PluginBaseController
{
	/**
	 * 登录验证
	 */
	public function doLogin()
	{
		$loginAllowed = session("__LOGIN_BY_CMF_ADMIN_PW__");
		if (empty($loginAllowed)) {
			$this->error(lang('outlaw_login'), cmf_get_root() . '/');
		}
		//腾讯防水墙验证
		$ticket = $this->request->param('ticket');
		$randstr = $this->request->param('randstr');

		$qqVerify = $this->qqCaptchaVerify($ticket,$randstr);

		if($qqVerify->response==0){
			$this->error($qqVerify->err_msg);
		}


		// require_once dirname(dirname(__FILE__)) . '/lib/class.geetestlib.php';
		// $config = $this->getPlugin()->getConfig();
		// $GtSdk = new \GeetestLib($config['captcha_id'], $config['private_key']);
		// $data = [
		// 	"user_id" => session('user_id'),
		// 	"client_type" => "web",
		// 	"ip_address" => get_client_ip()
		// ];
		// $geetest_challenge = $this->request->param('geetest_challenge');
		// $geetest_validate = $this->request->param('geetest_validate');
		// $geetest_seccode = $this->request->param('geetest_seccode');
		// if (session('gtserver') == 1) {   //服务器正常
		// 	$result = $GtSdk->success_validate($geetest_challenge, 
		// 		$geetest_validate, $geetest_seccode, $data);
		// 	if (!$result) {
		// 		 $this->error(lang('CAPTCHA_NOT_RIGHT'));
		// 	}
		// }else{  //服务器宕机,走failback模式
		// 	$result = $GtSdk->fail_validate($geetest_challenge, 
		// 		$geetest_validate, $geetest_seccode);
		// 	if (!$result){
		// 		$this->error(lang('CAPTCHA_NOT_RIGHT'));
		// 	}
		// }
		//用户名、密码验证
		$name = $this->request->param("username");
		if (empty($name)) {
			$this->error(lang('USERNAME_OR_EMAIL_EMPTY'));
		}
		$pass = $this->request->param("password");
		if (empty($pass)) {
			$this->error(lang('PASSWORD_REQUIRED'));
		}
		if (strpos($name, "@") > 0) {//邮箱登陆
			$where['user_email'] = $name;
		} else {
			$where['user_login'] = $name;
		}
	
		$result = Db::name('user')->where($where)->find();
	
		if (!empty($result) && $result['user_type'] == 1) {
			if (cmf_compare_password($pass, $result['user_pass'])) {
				$groups = Db::name('RoleUser')
				->alias("a")
				->join('__ROLE__ b', 'a.role_id =b.id')
				->where(["user_id" => $result["id"], "status" => 1])
				->value("role_id");
				if ($result["id"] != 1 && (empty($groups) || empty($result['user_status']))) {
					$this->error(lang('USE_DISABLED'));
				}
				//登入成功页面跳转
				session('ADMIN_ID', $result["id"]);
				session('name', $result["user_login"]);
				$result['last_login_ip']   = get_client_ip(0, true);
				$result['last_login_time'] = time();
				$token                     = cmf_generate_user_token($result["id"], 'web');
				if (!empty($token)) {
					session('token', $token);
				}
				Db::name('user')->update($result);
				cookie("admin_username", $name, 3600 * 24 * 30);
				session("__LOGIN_BY_CMF_ADMIN_PW__", null);
				$this->success(lang('LOGIN_SUCCESS'), url("admin/Index/index"));
			} else {
				$this->error(lang('USERNAME_OR_PASSWORD_NOT_RIGHT'));
			}
		} else {
			$this->error(lang('USERNAME_OR_PASSWORD_NOT_RIGHT'));
		}
	}
	
	/**
	 * 腾讯防水墙验证
	 */
	private function qqCaptchaVerify($ticket,$randstr){
		//error_reporting(0);
		
		$config = $this->getPlugin()->getConfig();
		$data = [
			"aid" => $config['app_id'],
			"AppSecretKey" => $config['app_secret_key'], 
			"Ticket"=> $ticket,
			"Randstr"=> $randstr,
			"UserIP" => get_client_ip()
		];
        $url = "https://ssl.captcha.qq.com/ticket/verify";
        $res = $this->post_request($url,$data);
        return @json_decode($res);
	}


    public static $connectTimeout = 1;
    public static $socketTimeout  = 1;
	 /**
     *
     * @param       $url
     * @param array $postdata
     * @return mixed|string
     */
    private function post_request($url, $postdata = '') {
        if (!$postdata) {
            return false;
        }

        $data = http_build_query($postdata);
        if (function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::$socketTimeout);

            //不可能执行到的代码
            if (!$postdata) {
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            } else {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            $data = curl_exec($ch);

            if (curl_errno($ch)) {
                $err = sprintf("curl[%s] error[%s]", $url, curl_errno($ch) . ':' . curl_error($ch));
                trigger_error($err);
            }

            curl_close($ch);
        } else {
            if ($postdata) {
                $opts    = array(
                    'http' => array(
                        'method'  => 'POST',
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . "Content-Length: " . strlen($data) . "\r\n",
                        'content' => $data,
                        'timeout' => self::$connectTimeout + self::$socketTimeout
                    )
                );
                $context = stream_context_create($opts);
                $data    = file_get_contents($url, false, $context);
            }
        }

        return $data;
    }


}