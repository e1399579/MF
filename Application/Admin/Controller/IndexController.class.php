<?php
namespace Admin\Controller;
class IndexController extends AuthController {
    public function index(){
        //缓存
        $menu = F('menu');
        if (empty($menu)) {
            $role_id = $_SESSION['role_id'];
            if ($role_id == 1) {//管理员显示全部菜单
                $menu = $this->getTree(null);
            } else {
                $ids = M('Role')->where("role_id=$role_id")->getField('menu_id_list');
                if ($ids == '*')
                    $ids = null;
                $menu = $this->getTree($ids);
            }
            F('menu', $menu);
        }

        $this->assign('menu', $menu);
        $this->display();
    }

    public function main() {
        $mysql = M()->query('SELECT version()');
        $mysql = current($mysql[0]);
        $ext = array_chunk(get_loaded_extensions(), 15);
        $extension = '';
        foreach ($ext as $val) {
            $extension .= implode(', ', $val) . '<br />';
        }
        //服务器信息
        $info = array(
            '服务器时间' => date('Y-m-d H:i:s') . ' ' . date_default_timezone_get(),
            '操作系统' => PHP_OS,
            '运行环境' => $_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式' => php_sapi_name(),
            'PHP版本' => PHP_VERSION,
            'MYSQL版本' => $mysql,
            '支持的扩展' => $extension,
            '上传附件限制' => ini_get('upload_max_filesize'),
            '执行时间限制' => ini_get('max_execution_time') . "秒",
            '剩余空间' => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
        );
        $this->assign('server_info', $info);
        $this->display();
    }

    /**
     * 无限分类
     * @param int $pid
     * @return mixed
     */
    public function getTree($ids, $pid=0) {
        $where = "parent_id=$pid AND is_display='显示'";
        is_null($ids) or $where .= " AND menu_id IN($ids)";
        $res = M('Menu')->where($where)->order('list_order ASC')->select();
        foreach ($res as $key => $row) {
            $res[$key]['child'] = $this->getTree($ids, $row['menu_id']);
        }
        return $res;
    }

    public function info() {
        phpinfo();
    }
}