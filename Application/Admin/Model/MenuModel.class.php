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
		$where = array();
		if ($name = I('get.name'))
			$where['name'] = array('LIKE', "%{$name}%");
    	if ($controller = I('get.controller'))
			$where['controller'] = array('LIKE', "%{$controller}%");
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
		//TODO 清除所有关联该菜单角色的缓存
		$this->flushMenuCache();
	}

	public function _before_delete($options) {
		$this->flushMenuCache();
	}

	public function getTree(&$data, $pid=0, $deep=0, $is_clear=true) {
		static $tree;
		$is_clear and $tree = array();
		foreach ($data as $key => &$row) {
			if ($row['parent_id'] == $pid) {
				$row['deep'] = $deep;
				$menu_id = $row['menu_id'];
				$tree[] = $row;
				unset($data[$key]);//删除已经找到的，下次调用时，循环次数减少
				$this->getTree($data, $menu_id, $deep+1, false);
			}
		}
		unset($row);
		return $tree;
	}

	/**
	 * 获取无限分类菜单
	 * @param string $ids
	 * @return array
	 */
	public function getTreeByMenuId($ids) {
		if ('' === $ids)
			return array();
		$map = array(
			'is_display' => '显示',
		);
		if ('*' != $ids) {
			$map['menu_id'] = array('IN', $ids);
		}
		$data = $this->where($map)->select();
		return $this->getTreeByData($data);
	}

	public function getTreeByData(&$data, $parent_id=0) {
		$child = $this->getChild($data, $parent_id);
		if (empty($child)) {
			return array();
		}
		foreach ($child as &$row) {
			$row['child'] = $this->getTreeByData($data, $row['menu_id']);
		}
		unset($row);
		return $child;
	}

	public function getChild(&$data, $parent_id=0) {
		$child = array();
		foreach ($data as $key => $row) {
			if ($parent_id == $row['parent_id']) {
				$child[] = $row;
				unset($data[$key]);
			}
		}
		return $child;
	}

	public function getMenuByRoleId($role_id) {
		$this->initCache();
		$key = 'menu' . $role_id;
		$menu = S($key);
		if (empty($menu)) {
			if ($role_id == 1) {//管理员显示全部菜单
				$menu = $this->getTreeByMenuId('*');
			} else {
				$ids = M('Role')->where(array('role_id' => $role_id))->getField('menu_id_list');
				$menu = $this->getTreeByMenuId($ids);
			}
			S($key, $menu);
		}
		return $menu;
	}

	public function initCache() {
		S(array(
			'type'      => 'memcached',
			'host'      => '127.0.0.1',
			'port'      => '11211',
			'prefix'    => 'think',
		));
	}

	public function flushMenuCache($role_id=null) {
		$this->initCache();
		$role_id or $role_id = $_SESSION['role_id'];
		$key = 'menu' . $role_id;
		S($key, null);//清空缓存
	}
}