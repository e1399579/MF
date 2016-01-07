<?php
namespace Home\Controller;
use Layout\Controller\HomeController;
class IndexController extends HomeController {
    public function index(){
        $this->display();
    }

    public function getTree($pid=0) {
        $res = M('Menu')->where("parent_id=$pid")->select();
        foreach ($res as $key => $row) {
            $res[$key]['child'] = $this->getTree($row['menu_id']);
        }
        return $res;
    }
}