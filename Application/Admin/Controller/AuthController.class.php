<?php
/**
 * Created by PhpStorm.
 * User: guide
 * Date: 2015/8/4
 * Time: 9:04
 */
namespace Admin\Controller;
use Think\Controller;
class AuthController extends Controller {
    public function __construct() {
        parent::__construct();
        empty($_COOKIE['PHPSESSID']) and $this->redirect('Admin/Public/login');
        session_start();
        isset($_SESSION['admin_id']) or $this->redirect('Admin/Public/login');
        if (!$this->checkAuth($_SESSION['role_id']))
            $this->error('您没有访问权限！');
    }

    /**
     * 检查权限
     * @param $role_id 角色id
     * @return bool
     */
    public function checkAuth($role_id) {
        if ($role_id == 1)
            return true;
        $model = M('Role');
        $ids = $model->where("role_id=$role_id")->getField('menu_id_list');
        if (empty($ids))
            return false;
        if ($ids == '*')
            return true;
        $mca = MODULE_NAME.CONTROLLER_NAME.ACTION_NAME;
        if(in_array($mca, array('AdminIndexindex', 'AdminIndexmain')))
            return true;
        $menu = M('Menu');
        return $menu->where("concat(`module`,`controller`,`action`)='$mca' AND menu_id IN($ids)")->count();
    }
}