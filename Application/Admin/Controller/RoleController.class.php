<?php
namespace Admin\Controller;
use \Admin\Controller\AuthController;
class RoleController extends AuthController {
	public function index() {
		$model = D('Role');
		$data = $model->search();
		$this->assign($data);
		$menu = M('Menu')->where('module="'.MODULE_NAME.'" AND controller="'.CONTROLLER_NAME.'" AND action="'.ACTION_NAME.'"')->order('menu_id DESC')->find();
		$this->assign('menu', $menu);
		$this->display();
	}
	
	public function search() {
		$model = D('Role');
		$data = $model->search();
		echo json_encode($data);
	}
	
	public function add(){
		$this->display();
	}
	
	public function addPost() {
		$model = D('Role');
		if ($model->create()) {
			if ($model->add()) {
				$this->success('添加成功',U('index'));
				die;
			} else {
				$this->error('添加失败');
			}
		} else {
			$this->error($model->getError());
		}
	}
	
	public function del($id) {
		$model = D('Role');
		$model->delete($id);
		$this->success('删除成功!');
		die;
	}
	
	public function bdel(){
		$delid = I('post.delid');
		if ($delid) {
			$did = implode(',',$delid);
			$model = D('Role');
			$model->delete($did);
		}
		$this->success('删除成功！');
		exit;
	}
	
	public function save($id){
		$model = M('Role');
		$info = $model->find($id);
		$this->assign('info',$info);
		$this->display();
	}
	
	public function savePost(){
		$model = D('Role');
		if ($model->create()) {
			if ($model->save() !== false) {
				$this->success('修改成功',U('index'));
				die;
			} else {
				$this->error('修改失败');
			}
		} else {
			$this->error($model->getError());
		}
	}

	public function privilege($id=0) {
		$menu = D('Menu');
		$data = $menu->select();
		$tree = $menu->getTree($data);
		$this->assign('tree', $tree);
		$ids = M('Role')->where("role_id=$id")->getField('menu_id_list');
		$mca = $menu->field('concat(`module`,`controller`,`action`) mca')
				->where("`menu_id` IN ($ids)")->select();
		//>5.5用array_column代替
		$tmp = array();
		foreach ($mca as $row) {
			$tmp[] = $row['mca'];
		}
		$this->assign('mca', $tmp);
		$this->assign('role_id', $id);
		$this->display();
	}

	public function auth() {
		$model = M('Role');
		$data['menu_id_list'] = implode(',', I('post.id', array()));
		$role_id = I('post.role_id', 0);
		$aff = $model->where('role_id', $role_id)->save($data);
		if ($aff === false) {
			$this->error('授权失败！');
		} else {
			$this->success('授权成功！');
		}
	}


}