<?php
/**
 * 布局控制
 */
namespace Layout\Controller;
use Think\Controller;
class HomeController extends Controller{
	public function __construct(){
		parent::__construct();
        $music = $this->getMusic();
		$this->assign(array(
				'title' => '首页',
				'css' => array(),
				'js' => array(),
                'music' => $music,
		));
	}

	public function getMusic() {
        $model = M('Music');
        return $model->field(array('title', 'artist', 'path'))
            ->order('list_order ASC,music_id DESC')
            ->where(array('status'=>1))->select();
	}
}