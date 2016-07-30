<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model {
	protected $_validate = array(
		array('username','require','用户名不能为空'),
		array('username','','用户名已经存在', self::MUST_VALIDATE, 'unique'),
		array('password','require','密码不能为空'),
		array('register_time','require','注册时间不能为空'),
		array('role_id','require','角色ID不能为空'),
	);

	public function search() {
		$perpage = 10;
		$where = 1;
		if ($username = I('get.username'))
			$where .= " AND username LIKE '%{$username}%'";
    	if ($email = I('get.email'))
			$where .= " AND email LIKE '%{$email}%'";
    	if ($role_name = I('get.role_name'))
			$where .= " AND b.name LIKE '%{$role_name}%'";
    	$total = $this->alias('a')->join('__ROLE__ b USING(role_id)', 'LEFT')->where($where)->count();
		$page = new \Think\Page($total,$perpage);
		$page->setConfig('first','首页');
		$page->setConfig('last','尾页');
		$page->setConfig('prev','上一页');
		$page->setConfig('next','下一页');
		$pageStr = $page->show();
		$data = $this->alias('a')->join('__ROLE__ b USING(role_id)', 'LEFT')
				->field('a.*,b.name role_name')
				->where($where)->limit($page->firstRow.','.$page->listRows)
				->order('admin_id DESC')->select();
		return array(
			'page' => $pageStr,
			'data' => $data,
		);
	}

	public function _before_insert(&$data,$options) {
		$data['password'] = md5($data['password']);
		$data['register_time'] = date('Y-m-d H:i:s');
	}

	public function _before_update(&$data,$options) {
		if (in_array($data['password'], array('123456', '')))
			unset($data['password']);
		else
			$data['password'] = md5($data['password']);
	}
}