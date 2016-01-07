<?php
namespace Gii\Controller;
use \Admin\Controller\AuthController;
header('content-type:text/html;charset=utf-8');
class IndexController extends AuthController {
	public $module;
	public $controller;
	public function index(){
		if(IS_POST){
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
			
			$c = $m = $add = $save = $lst = $res = -1;
			/* 生成控制器 */
			//表名转换
			$tn = $this->tableName2TpName($tableName);
			$ver = I('post.version', '');
			
			//读取模板文件生成控制器
			if(isset($_POST['is_controller'])){
				//校验文件存在
				$c_file = $cDir.$tn.'Controller.class.php';
				if(!is_file($c_file)){
					//$this->error($moduleName.' 中 '.$comment.' 控制器已经存在！');
					ob_start();//开启缓冲区,之后所有代码放在该内存中
					include('./Application/Gii/Template/Controller'.$ver.'.tpl');
					$str = ob_get_clean();//读出内容并关闭清空缓冲区
					$c = file_put_contents($c_file,"<?php\r\n".$str);
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
				//检查文件
				$m_file = $mDir.$tn.'Model.class.php';
				if(!is_file($m_file)){
					//$this->error($moduleName.' 中 '.$comment.' 模型已经存在！');
					//$db = M();
					//$sql = "SHOW FULL FIELDS FROM $tableName";
					ob_start();
					include './Application/Gii/Template/Model.tpl';
					$str = ob_get_clean();
					$m = file_put_contents($m_file,"<?php\r\n".$str);
				}
			}
			/* 生成模板 */
			if(isset($_POST['is_view'])){
				$vfDir = $vDir.$tn;
				mkdir($vfDir,0777,true);
				//生成add.html
				if(!is_file($vfDir.'/add.html')){
					ob_start();
					include './Application/Gii/Template/add'.$ver.'.html';
					$str = ob_get_clean();
					$add = file_put_contents($vfDir.'/add.html',$str);
				}
				//生成save.html
				if(!is_file($vfDir.'/save.html')){
					ob_start();
					include './Application/Gii/Template/save'.$ver.'.html';
					$str = ob_get_clean();
					$save = file_put_contents($vfDir.'/save.html',$str);
				}
				//生成lst.html
				if(!is_file($vfDir.'/index.html')){
					ob_start();
					include './Application/Gii/Template/index'.$ver.'.html';
					$str = ob_get_clean();
					$lst = file_put_contents($vfDir.'/index.html',$str);
				}
			}
			
			/* //判断按钮是否顶级权限
			$btnName = I('post.btnName');
			$pri = M('Privilege');
			$topPri = $pri->field('id')->where("pri_name='$btnName' AND parent_id=0")->find();
			//没有时添加顶级权限，返回ID作为二级权限依据
			if(!$topPri){
				$topPri['id'] = $pri->add(array(
					'pri_name' => $btnName,
					'parent_id' => '0',
					'module_name' => 'null',
					'controller_name' => 'null',
					'action_name' => 'null',
				));
			}
			//添加二级权限
			$id = $pri->add(array(
				'pri_name' => $comment.'列表',
				'parent_id' => $topPri['id'],
				'module_name' => $moduleName,
				'controller_name' => $tn,
				'action_name' => 'lst',
			));
			// 为lst方法再添加三个子权限
			$pri->add(array(
					'pri_name' => '添加'.$comment,
					'parent_id' => $id,
					'module_name' => $moduleName,
					'controller_name' => $tn,
					'action_name' => 'add',
			));
			$pri->add(array(
					'pri_name' => '修改'.$comment,
					'parent_id' => $id,
					'module_name' => $moduleName,
					'controller_name' => $tn,
					'action_name' => 'save',
			));
			$pri->add(array(
					'pri_name' => '删除'.$comment,
					'parent_id' => $id,
					'module_name' => $moduleName,
					'controller_name' => $tn,
					'action_name' => 'del',
			));
			$pri->add(array(
					'pri_name' => '批量删除'.$comment,
					'parent_id' => $id,
					'module_name' => $moduleName,
					'controller_name' => $tn,
					'action_name' => 'bdel',
			)); */		//添加菜单
			if(isset($_POST['is_menu'])){
				//清空缓存
				F('menu', null);
				$menu = D('Menu');
				$btn = I('post.btnName');
				$data = array(
						'name' => $btn,
						'module' => $moduleName,
						'controller' => $tn,
						'action' => 'index',
				);
				empty($_POST['icon']) or $data['icon'] = I('post.icon');
				if($id = $menu->where("name='$btn' AND module='$moduleName'")->getField('menu_id')){
				     $res = $menu->where("menu_id=$id")->save($data);//已经有菜单则更新
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
			$result = array($c, $m, $add, $save, $lst, $res);
			$all = '';
			foreach ($info as $k=>$v){
				if($result[$k]>=1)
					$all .= '生成 '.$v.' 成功<br />';
				elseif($result[$k]==0)
					$all .= '生成 '.$v.' 失败<br />';
				elseif($result[$k]==-1)
					$all .= '未生成  '.$v.'<br />';
			}
			$this->success($all);
			die;
		}
		$dbName = C('DB_NAME');
		$tables = M()->query("select table_name from information_schema.tables where table_schema='".$dbName."' and table_type='base table'");
		$this->assign('tables', $tables);
		$this->display();
	}
	
	
	 /*array(
				'parentid' => $pid,
				'name' => '搜索'.$comment,
				'app' => $moduleName,
				'model' => $tn,
				'action' => 'search',
		);*/
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
		// 如果表名中有前缀就从前缀之后截取
		if(strpos($tableName, $dp) === 0)
		{
			$len = strlen($dp);
			$tableName = substr($tableName, $len);
		}
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
	public function Analyze() {
		$module = ucfirst(I('post.moduleName'));
		$controller = ucfirst(I('post.controllerName'));
		$con = $module.'\Controller\\'.$controller.'Controller';
		$methods = get_class_methods(new $con());
		$arr = get_class_methods($this);
		$action = array_diff($methods, $arr);
		$model = M('Menu');
		$temp = implode("','", $action);
		$has = $model->where("module='$module' AND controller='$controller' AND action IN('$temp')")->getField('action', true);
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