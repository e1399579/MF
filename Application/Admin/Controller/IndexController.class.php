<?php
namespace Admin\Controller;
use Admin\Model\MenuModel;

class IndexController extends AuthController {
    public function index(){
        //缓存，区分角色，避免影响其他用户
        $role_id = $_SESSION['role_id'];
        $model = new MenuModel();
	    $menu = $model->getMenuByRoleId($role_id);

        $this->assign('menu', $menu);
        $this->display();
    }

    public function main() {
        $mysql = M()->query('SELECT version()');
        $mysql = current($mysql[0]);
        $ext = array_chunk(get_loaded_extensions(), 15);
        $extension = '';
        foreach ($ext as $val) {
            $extension .= implode(', ', $val) . '<br />';
        }
        //服务器信息
        $info = array(
            '服务器时间' => date('Y-m-d H:i:s') . ' ' . date_default_timezone_get(),
            '操作系统' => PHP_OS,
            '运行环境' => $_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式' => php_sapi_name(),
            'PHP版本' => PHP_VERSION,
            'MYSQL版本' => $mysql,
            '支持的扩展' => $extension,
            '上传附件限制' => ini_get('upload_max_filesize'),
            '执行时间限制' => ini_get('max_execution_time') . "秒",
            '剩余空间' => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
        );
        $this->assign('server_info', $info);
	    $this->assign('menu', array('name' => '首页'));
        $this->display();
    }

    public function info() {
        phpinfo();
    }
}