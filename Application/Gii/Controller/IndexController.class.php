<?php
namespace Gii\Controller;
use Admin\Controller\AuthController;
use Gii\Model\GiiModel;

header('content-type:text/html;charset=utf-8');
class IndexController extends AuthController {
	public $module;
	public $controller;
	public function index(){
		if (!IS_POST) {
			$dbName = C('DB_NAME');
			$tables = M()->query("SELECT table_name FROM information_schema.tables WHERE table_schema='{$dbName}' AND table_type='base table'");
			$this->assign('tables', $tables);
			$this->display();
			return;
		}
		$tableName = I('post.tableName');
		//查看表注释
		$com = M()->query("SHOW TABLE STATUS LIKE '$tableName'");
		$comment = is_null($com[0]['Comment']) ? $com[0]['comment'] : $com[0]['Comment'];//表中文名
		$pri = M()->query("SELECT COLUMN_NAME pri FROM INFORMATION_SCHEMA.COLUMNS
			     WHERE TABLE_NAME='$tableName' AND COLUMN_KEY='PRI'");
		$pri = empty($pri) ? 'id' : $pri[0]['pri'];//表主键
		if(empty($_POST['tableName']) || empty($_POST['moduleName'])){
			$this->error('表名不能为空');
		}
		$tab = M()->query("SHOW TABLES LIKE '$tableName'");
		if(!count($tab)){
			$this->error('没有这个表！');
		}
		$moduleName = ucfirst(I('post.moduleName'));
		/* 创建目录 */
		$cDir = './Application/'.$moduleName.'/Controller/';
		$mDir = './Application/'.$moduleName.'/Model/';
		$vDir = './Application/'.$moduleName.'/View/';
		if(!is_dir($cDir))
			mkdir($cDir,0777,true);
		if(!is_dir($mDir))
			mkdir($mDir,0777,true);
		if(!is_dir($vDir))
			mkdir($vDir,0777,true);

		$c = $m = $add = $save = $lst = $res = 0;

		//表名转换
		$tn = $this->tableName2TpName($tableName);

		/* 生成控制器 */
		if(isset($_POST['is_controller'])){
			$controllerName = "{$tn}Controller";
			//校验文件存在
			$c_file = $cDir.$controllerName.'.class.php';
			if(!is_file($c_file)){
				ob_start();//开启缓冲区,之后所有代码放在该内存中
				include(__DIR__.'/../Template/Controller.tpl');
				$str = ob_get_clean();//读出内容并关闭清空缓冲区
				$c = file_put_contents($c_file, '<?php'.PHP_EOL.$str);
			}
		}

		$dbName = C('DB_NAME');
		$sql = "SELECT data_type `type`,column_name `field`,character_maximum_length `length`,is_nullable `null`,
			column_default `default`,column_comment `comment`,column_type `types`,column_key `key`
			FROM information_schema.columns WHERE table_schema='$dbName' AND table_name='$tableName'";
		$fields = M()->query($sql);//所有字段名称、类型、长度、是否为空、默认值、注释..
		foreach ($fields as $k => &$v) {
			$v['comment2'] = $v['comment'];
			$tmp = preg_split('/[\s:：]+/', $v['comment'], 2);//分隔字段名称和说明
			$v['comment'] = $tmp[0];
			$v['note'] = empty($tmp[1]) ? $tmp[0] : $tmp[1];
		}
		unset($v);

		/* 生成模型 */
		if(isset($_POST['is_model'])){
			$modelName = $tn.'Model';
			//检查文件
			$m_file = $mDir.$modelName.'.class.php';
			if(!is_file($m_file)){
				ob_start();
				include __DIR__.'/../Template/Model.tpl';
				$str = ob_get_clean();
				$m = file_put_contents($m_file, '<?php'.PHP_EOL.$str);
			}
		}

		/* 生成模板 */
		if(isset($_POST['is_view'])){
			$vfDir = $vDir.$tn;
			mkdir($vfDir,0777,true);
			//生成add.html
			if(!is_file($vfDir.'/add.html')){
				/*ob_start();
				include './Application/Gii/Template/add'.$ver.'.html';
				$str = ob_get_clean();*/
				$content = file_get_contents(__DIR__.'/../Template/add.html');
				$str = GiiModel::parseAddTemplate($content, $fields, $pri, $comment);
				$add = file_put_contents($vfDir.'/add.html',$str);
			}
			//生成save.html
			if(!is_file($vfDir.'/save.html')){
				/*ob_start();
				include './Application/Gii/Template/save'.$ver.'.html';
				$str = ob_get_clean();*/
				$content = file_get_contents(__DIR__.'/../Template/save.html');
				$str = GiiModel::parseSaveTemplate($content, $fields, $pri, $comment);
				$save = file_put_contents($vfDir.'/save.html',$str);
			}
			//生成lst.html
			if(!is_file($vfDir.'/index.html')){
				/*ob_start();
				include './Application/Gii/Template/index'.$ver.'.html';
				$str = ob_get_clean();*/
				$content = file_get_contents(__DIR__.'/../Template/index.html');
				$str = GiiModel::parseIndexTemplate($content, $fields, $pri, $comment);
				$lst = file_put_contents($vfDir.'/index.html',$str);
			}
		}

		//添加菜单
		if(isset($_POST['is_menu'])){
			$menu = D('Menu');
			$btn = I('post.btnName');
			$data = array(
				'name' => $btn,
				'module' => $moduleName,
				'controller' => $tn,
				'action' => 'index',
			);
			empty($_POST['icon']) or $data['icon'] = I('post.icon');
			if($id = $menu->where(array('name' => $btn, 'module' => $moduleName))->getField('menu_id')){
				$res = $menu->where(array('menu_id' => $id))->save($data);//已经有菜单则更新
				$pid = $id;
			}else{
				$res = $menu->add($data);
				$pid = $res;
			}
			$this->module = $moduleName;
			$this->controller = $tn;
			//添加其它菜单项search,add,addPost,del,bdel,save,savePost
			$map[] = array(
				'parent_id' => $pid,
				'name' => '搜索'.$comment,
				'action' => 'search',
			);
			$map[] = array(
				'parent_id' => $pid,
				'name' => '添加'.$comment,
				'action' => 'add',
			);
			$map[] = array(
				'parent_id' => $pid,
				'name' => $comment.'表单添加提交',
				'action' => 'addPost',
			);
			$map[] = array(
				'parent_id' => $pid,
				'name' => '删除'.$comment,
				'action' => 'del',
			);
			$map[] = array(
				'parent_id' => $pid,
				'name' => '批量删除'.$comment,
				'action' => 'bdel',
			);
			$map[] = array(
				'parent_id' => $pid,
				'name' => '修改'.$comment,
				'action' => 'save',
			);
			$map[] = array(
				'parent_id' => $pid,
				'name' => $comment.'表单修改提交',
				'action' => 'savePost',
			);
			foreach ($map as $val){
				$this->addMenu($val);
			}
		}

		$info = array('控制器', '模型', '添加模板', '修改模板', '列表模板', "{$comment}菜单",);
		$result = array($c, $m, $add, $save, $lst, $res);//如果用关联数组，可能会有覆盖情况
		$all = '';
		$fail = 0;
		foreach ($info as $key => $val) {
			if ($result[$key] === false) {
				++$fail;
				$all .= '生成 '.$val.' 失败<br />';
			} else if ($result[$key] == 0)
				$all .= '未生成  '.$val.'<br />';
			else
				$all .= '生成 '.$val.' 成功<br />';
		}
		if (0 < $fail)
			$this->error($fail);
		$this->success($all);
	}

	public function addMenu($map=array()){
		$menu = D('Menu');
		$id = $menu->where($map)->getField('menu_id');
		if(empty($id)){
			$arr = array(
					'module' => $this->module,
					'controller' => $this->controller,
					'is_display' => '隐藏',
			);
			$data = array_merge($map, $arr);
			$menu->add($data);
		}
	}
	
	public function tableName2TpName($tableName)
	{
		// tp名称的规则：
		//1.去掉表前缀
		$dp = C('DB_PREFIX');  // 从配置文件中取出当前表前缀
		$tableName = ltrim($tableName, $dp); // 去掉前缀
		//2.去掉_并_后面的单词首字母大写，如：sh_goods_images  --> GoodsImages
		$tableName = explode('_', $tableName);
		// 把数组中每个元素的首字母大写
		$tableName = array_map('ucfirst', $tableName);
		// 再把数组中每个单词拼到一起
		return implode('', $tableName);
	}

	/**
	 * 分析菜单
	 */
	public function analyze() {
		$module = ucfirst(I('post.moduleName'));
		$controller = ucfirst(I('post.controllerName'));
		$con = $module.'\Controller\\'.$controller.'Controller';
		$methods = get_class_methods($con);
		$arr = get_class_methods($this);
		$action = array_diff($methods, $arr);
		$model = M('Menu');
		$map = array_merge(compact('module'), compact('controller'));
		$act = empty($action) ? 0 : implode("','", $action);
		$has = $model->where($map)->where("`action` IN ('$act')")->getField('action', true);
		empty($has) and $has = array();
		$menu = array_diff($action, $has);
		empty($menu) and exit('暂时没有菜单可添加！');
		$this->assign(array(
			'menu' => $menu,
			'con' => $con,
			'module' => $module,
			'controller' => $controller,
		));
		$this->display();
	}

	/**
	 * 生成菜单
	 */
	public function buildMenu() {
		$menu = I('post.menu');
		$module = I('post.module');
		$controller = I('post.controller');
		$map = array(
			'module' => $module,
			'controller' => $controller,
			'action' => 'index',
		);
		$model = M('Menu');
		//分析上一级菜单
		$pid = $model->where($map)->getField('menu_id');
		if (empty($pid)) {
			//没有找到，创建菜单
			if (empty($menu['index']))
				$this->error("请先添加{$controller}Controller的一级菜单！");
			$map['icon'] = 'icon-list';
			$map['name'] = $menu['index'];
			$pid = $model->add($map);
		}
		if (isset($menu['index'])) unset($menu['index']);
		$dataList = array();
		$mess = '生成';
		foreach ($menu as $action => $name) {
			if (!empty($name)) {
				$dataList[] = array(
					'parent_id' => $pid,
					'module' => $module,
					'controller' => $controller,
					'action' => $action,
					'is_display' => '隐藏',
					'name' => $name,
				);
				$mess .= $name.'<br />';
			}
		}
		$mess .= '成功';
		$model->addAll($dataList);
		$this->success($mess, U('index'));
	}
}