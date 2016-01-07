<?php
namespace Admin\Model;
use Think\Model;
class MenuModel extends Model {
	protected $_validate = array(
		array('module','require','模块不能为空'),
	array('controller','require','控制器不能为空'),
	array('action','require','方法不能为空'),
	array('name','require','菜单名称不能为空'),
	);
	public function search() {
		$perpage = 10;
		$where = 1;
		if ($name = I('get.name'))
			$where .= " AND name LIKE '%{$name}%'";
    	if ($controller = I('get.controller'))
			$where .= " AND controller LIKE '%{$controller}%'";
    	$total = $this->where($where)->count();
		$page = new \Think\Page($total,$perpage);
		$page->setConfig('first','首页');
		$page->setConfig('last','尾页');
		$page->setConfig('prev','上一页');
		$page->setConfig('next','下一页');
		$pageStr = $page->show();
		$data = $this->where($where)->limit($page->firstRow.','.$page->listRows)
				->order('menu_id ASC')->select();
		return array(
			'page' => $pageStr,
			'data' => $data,
		);
	}

	public function _before_write(&$data) {
		F('menu', null);//清空缓存
	}

	public function _before_delete($options) {
		F('menu', null);//清空缓存
	}

	public function getTree(&$data, $pid=0, $deep=0) {
		foreach ($data as $key => $row) {
			if ($row['parent_id'] == $pid) {
				$data[$key]['deep'] = $deep;
				$this->getTree($data, $row['menu_id'], $deep+1);
			}
		}
		return $data;
	}
}