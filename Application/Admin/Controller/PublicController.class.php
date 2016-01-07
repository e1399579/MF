<?php
/**
 * Created by PhpStorm.
 * User: guide
 * Date: 2015/8/3
 * Time: 15:16
 */
namespace Admin\Controller;
use Think\Controller;
class PublicController extends Controller {
    /**
     * 登录
     */
    public function login() {
        if (!empty($_COOKIE['PHPSESSID'])) {
            session_start();
            isset($_SESSION['admin_id']) and $this->redirect('Admin/Index/index');
        }
        $this->display('login');
    }

    /**
     * 登录提交
     */
    public function loginPost() {
        $user = I('post.username');
        $pass = md5(I('post.password'));
        $is_auto = I('post.is_auto');
        $capt = I('post.captcha');
        if (!$this->check_verify($capt)) {
            $this->error('验证码错误！');
        }
        $model = M('Admin');
        $id = $model->where("username='$user' OR email='$user'")->getField('admin_id');
        if (empty($id)) {
            $this->error('帐号不存在！');
        }
        $res = $model->field('admin_id,username,role_id')->where("admin_id=$id AND password='$pass' AND user_status='启用'")->find();
        if (empty($res)) {
            $this->error('帐号或密码错误！');
        }
        //记住一周
        if ($is_auto) {
            //设置cookie中sessionid过期时间
            isset($_COOKIE['PHPSESSID']) ? setcookie('PHPSESSID', $_COOKIE['PHPSESSID'], time()+86400*7 , '/') : ini_set('session.cookie_lifetime', 86400*7);
        } else {
            isset($_COOKIE['PHPSESSID']) and setcookie('PHPSESSID', $_COOKIE['PHPSESSID'], 0 , '/');
        }
        F('menu', null);//清空缓存
        session_start();
        $_SESSION = array(
            'admin_id' => $id,
            'username' => $res['username'],
            'role_id' => $res['role_id'],
        );
        $data = array(
            'last_ip' => get_client_ip(),
            'last_time' => date('Y-m-d H:i:s'),
        );
        $model->where("admin_id=$id")->save($data);
        $this->success('登录成功', U('Admin/Index/index'));
    }

    /**
     * 注销
     */
    public function logout() {
        session_start();
        $_SESSION['admin_id'] = null;
        $_SESSION['role_id'] = null;
        session_destroy();
        setcookie(PHPSESSID, session_id(), 0, '/');
        $this->redirect('login');
    }

    /**
     * 验证码
     */
    public function captcha() {
        session_start();
        $config = array(
            'fontSize' => 20,
            'length' => 4,
            'useNoise' => false,// 关闭验证码杂点
            'fontttf' => '4.ttf',
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry(2);
    }

    /**
     * 图片验证
     */
    public function check_verify($capt) {
        session_start();
        $verify = new \Think\Verify();
        return $verify->check($capt, 2);
    }
}