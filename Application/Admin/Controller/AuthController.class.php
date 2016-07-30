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
    public $allowList = array('AdminIndexindex', 'AdminIndexmain');

    public function __construct() {
        parent::__construct();
        empty($_COOKIE['PHPSESSID']) and $this->redirect('Admin/Public/login');
        session_start();
        isset($_SESSION['admin_id']) or $this->redirect('Admin/Public/login');
	    $this->addAllowList();
        if (!$this->checkAuth($_SESSION['role_id']))
            $this->error('您没有访问权限！');
    }

    /**
     * 检查权限
     * @param int $role_id 角色id
     * @return bool
     */
    public function checkAuth($role_id) {
        if ($role_id == 1)
            return true;
	    $mca = strtolower(MODULE_NAME.CONTROLLER_NAME.ACTION_NAME);
	    if(in_array($mca, array_map('strtolower', $this->allowList)))
		    return true;
        $model = M('Role');
        $ids = $model->where("role_id=$role_id")->getField('menu_id_list');
        if (empty($ids))
            return false;
        if ($ids == '*')
            return true;
        $menu = M('Menu');
	    $map['menu_id'] = array('IN', $ids);
        $menuList = $menu->field(array('module', 'controller', 'action'))->where($map)->select();
	    foreach ($menuList as $row) {
		    if ($mca == strtolower(implode('', $row)))
			    return true;
	    }
    }

    public function addAllow($mca) {
        if (is_array($mca)) {
            $this->allowList = array_merge($this->allowList, $mca);
        } else {
            array_push($this->allowList, $mca);
        }
    }

	/**
	 * 添加允许权限列表
	 */
    public function addAllowList() {}
}