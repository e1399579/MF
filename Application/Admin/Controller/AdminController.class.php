<?php
namespace Admin\Controller;

class AdminController extends AuthController {
	public function index() {
		$model = D('Admin');
		$data = $model->search();
		$this->assign($data);
		$menu = M('Menu')->where('module="'.MODULE_NAME.'" AND controller="'.CONTROLLER_NAME.'" AND action="'.ACTION_NAME.'"')->order('menu_id DESC')->find();
		$this->assign('menu', $menu);
		$this->display();
	}
	
	public function search() {
		$model = D('Admin');
		$data = $model->search();
		echo json_encode($data);
	}
	
	public function add(){
		$role = M('Role')->getField('role_id,name');
		$this->assign('role', $role);
		$this->display();
	}
	
	public function addPost() {
		$model = D('Admin');
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
		$model = D('Admin');
		//如果有图片，则删除
		$avatar = $model->where(array('admin_id' => $id))->getField('avatar');
		empty($avatar) or @unlink('.' . $avatar);
		$model->delete($id);
		$this->success('删除成功!');
		die;
	}
	
	public function bdel(){
		$delid = I('post.delid');
		if ($delid) {
			$did = implode(',',$delid);
			$model = D('Admin');
			$model->delete($did);
		}
		$this->success('删除成功！');
		exit;
	}
	
	public function save($id){
		$model = M('Admin');
		$info = $model->find($id);
		$this->assign('info',$info);
		$role = M('Role')->getField('role_id,name');
		$this->assign('role', $role);
		$this->display();
	}
	
	public function savePost(){
		$model = D('Admin');
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

	public function avatar() {
		$model = D('Admin');
		$info = $model->field(array('admin_id', 'avatar'))->find($_SESSION['admin_id']);
		$this->assign('info', $info);
		$this->display();
	}

	public function saveAvatar() {
		if (empty($_FILES['avatar']['name'])) {
			$this->error('文件路径不能为空');
		}
		set_time_limit(600);
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     1024 * 1024 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath  =      'avatar/'; // 设置附件上传目录
		$info = $upload->uploadOne($_FILES['avatar']);
		if (!$info) {
			$this->error($upload->getError());;
		}

		$data['avatar'] = '/Uploads/' . $info['savepath'].$info['savename'];
		$admin_id = I('post.admin_id');
		$model = D('Admin');
		//如果有图片，则删除
		$avatar = $model->where(compact('admin_id'))->getField('avatar');
		empty($avatar) or @unlink('.' . $avatar);

		$res = $model->where(compact('admin_id'))->save($data);
		if ($res !== false) {
			$_SESSION['avatar'] = $data['avatar'];
			$this->success('修改成功',  __ROOT__ . $data['avatar']);
			die;
		} else {
			$this->error('修改失败');
		}
	}

	/**
	 * 查看资料
	 * @param string $id
	 * @return void
	 */
	public function show($id){
		$model = M('Admin');
		$info = $model->find($id);
		$this->assign('info',$info);
		$role = M('Role')->getField('role_id,name');
		$this->assign('role', $role);
		$this->display();
	}

	public function addAllowList() {
		//查看/修改头像，查看/修改资料
		$this->addAllow(array('AdminAdminavatar', 'AdminAdminsaveAvatar', 'AdminAdminshow', 'AdminAdminsavePost'));
	}
}