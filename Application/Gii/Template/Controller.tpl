namespace <?php echo $moduleName;?>\Controller;
use \Admin\Controller\AuthController;
class <?php echo $tn;?>Controller extends AuthController {
	public function index() {
		$model = D('<?php echo $tn;?>');
		$data = $model->search();
		$this->assign($data);
		$menu = M('Menu')->where('module="'.MODULE_NAME.'" AND controller="'.CONTROLLER_NAME.'" AND action="'.ACTION_NAME.'"')->order('menu_id DESC')->find();
		$this->assign('menu', $menu);
		$this->display();
	}
	
	public function search() {
		$model = D('<?php echo $tn;?>');
		$data = $model->search();
		echo json_encode($data);
	}
	
	public function add(){
		$this->display();
	}
	
	public function addPost() {
		$model = D('<?php echo $tn;?>');
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
		$model = D('<?php echo $tn;?>');
		$model->delete($id);
		$this->success('删除成功!');
		die;
	}
	
	public function bdel(){
		$delid = I('post.delid');
		if ($delid) {
			$did = implode(',',$delid);
			$model = D('<?php echo $tn;?>');
			$model->delete($did);
		}
		$this->success('删除成功！');
		exit;
	}
	
	public function save($id){
		$model = M('<?php echo $tn;?>');
		$info = $model->find($id);
		$this->assign('info',$info);
		$this->display();
	}
	
	public function savePost(){
		$model = D('<?php echo $tn;?>');
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