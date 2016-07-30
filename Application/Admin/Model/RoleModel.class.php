<?php
namespace Admin\Model;
use Think\Model;
class RoleModel extends Model {
	protected $_validate = array(
		array('name','require','角色名称不能为空'),
	array('create_time','require','创建时间不能为空'),
	);

	public function search() {
		$perpage = 10;
		$where = 1;
		if ($name = I('get.name'))
			$where['name'] = array('LIKE', "%{$name}%");
    	if ($list_order = I('get.list_order'))
			$where['list_order'] = $list_order;
    	$total = $this->where($where)->count();
		$page = new \Think\Page($total,$perpage);
		$page->setConfig('first','首页');
		$page->setConfig('last','尾页');
		$page->setConfig('prev','上一页');
		$page->setConfig('next','下一页');
		$pageStr = $page->show();
		$data = $this->where($where)->limit($page->firstRow.','.$page->listRows)
				->order('role_id DESC')->select();
		return array(
			'page' => $pageStr,
			'data' => $data,
		);
	}

	public function _before_insert(&$data, $options) {
		$data['create_time'] = date('Y-m-d H:i:s');
	}
}