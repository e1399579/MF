namespace <?php echo $moduleName;?>\Model;
use Think\Model;
class <?php echo $tn;?>Model extends Model {
	protected $_validate = array(
		<?php foreach($fields as $k => $v):
			if($v['field'] == $pri)
				continue;
			if($v['default'] !== null)
				continue;
		?>array('<?php echo $v['field'];?>','require','<?php echo $v['comment'];?>不能为空'),
	<?php endforeach;?>);

	public function search() {
		$list = 10;
		$where = '1';
		<?php
    	foreach($fields as $row):
    	if($row['field']==$pri || empty($row['key']))
			continue;
    	?>if ($<?php echo $row['field'];?> = I('get.<?php echo $row['field'];?>'))
			$where .= " AND <?php echo $row['field'];?> LIKE '%{$<?php echo $row['field'];?>}%'";
    	<?php endforeach;?>$total = $this->where($where)->count();
		$page = new \Think\Page($total,$list);
		$page->setConfig('first','首页');
		$page->setConfig('last','尾页');
		$page->setConfig('prev','上一页');
		$page->setConfig('next','下一页');
		$pageStr = $page->show();
		$data = $this->where($where)->limit($page->firstRow.','.$page->listRows)
				->order('<?php echo $pri.' DESC';?>')->select();
		return array(
			'page' => $pageStr,
			'data' => $data,
		);
	}
}