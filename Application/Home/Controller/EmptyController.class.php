<?php
namespace Home\Controller;

use Think\Controller;

class EmptyController extends Controller {
	public function index() {
		$this->display('Empty:404');
	}
}