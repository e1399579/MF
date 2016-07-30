<?php
namespace Admin\Controller;

class MenuController extends AuthController {
	public function index() {
		$model = D('Menu');
		$data = $model->search();
		$this->assign($data);
		//当前菜单信息
		$map = array(
			'module' => MODULE_NAME,
			'controller' => CONTROLLER_NAME,
			'action' => ACTION_NAME,
		);
		$menu = M('Menu')->where($map)->order('menu_id DESC')->find();
		$this->assign('menu', $menu);
		$this->display();
	}
	
	public function search() {
		$model = D('Menu');
		$data = $model->search();
		echo json_encode($data);
	}
	
	public function add(){
		$model = D('Menu');
		$data = $model->select();
		$menu = $model->getTree($data);
		$this->assign('menu', $menu);
		$this->display();
	}
	
	public function addPost() {
		$model = D('Menu');
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
		$model = D('Menu');
		$model->where(array('parent_id' => $id))->delete();
		$model->delete($id);
		$this->success('删除成功!');
		die;
	}
	
	public function bdel(){
		$delid = I('post.delid');
		if ($delid) {
			$did = implode(',',$delid);
			$model = D('Menu');
			$model->delete($did);
		}
		$this->success('删除成功！');
		exit;
	}
	
	public function save($id){
		$model = D('Menu');
		$info = $model->find($id);
		$this->assign('info',$info);
		$data = $model->where("`menu_id`<>$id")->select();//排除自身
		$menu = $model->getTree($data);
		$this->assign('menu', $menu);
		$this->display();
	}
	
	public function savePost(){
		$model = D('Menu');
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
}