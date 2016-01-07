<?php
namespace Admin\Model;

use Think\Model;

class MusicModel extends Model {
    protected $_validate = array(
        array('title', 'require', '标题不能为空'),
        array('artist', 'require', '艺术家不能为空'),
        array('path', 'require', '文件路径不能为空'),
    );

    public function search() {
        $perpage = 10;
        $where = 1;
        if ($title = I('get.title'))
            $where .= " AND title LIKE '%{$title}%'";
        if ($artist = I('get.artist'))
            $where .= " AND artist LIKE '%{$artist}%'";
        $total = $this->where($where)->count();
        $page = new \Think\Page($total, $perpage);
        $page->setConfig('first', '首页');
        $page->setConfig('last', '尾页');
        $page->setConfig('prev', '上一页');
        $page->setConfig('next', '下一页');
        $pageStr = $page->show();
        $data = $this->where($where)->limit($page->firstRow . ',' . $page->listRows)
            ->order('list_order ASC,music_id DESC')->select();
        return array(
            'page' => $pageStr,
            'data' => $data,
        );
    }

    /**
     * 处理上传文件
     * @param $data
     * @param $options
     * @return bool
     */
    protected function _before_insert(&$data, $options) {
        if (empty($_FILES['path']['name'])) {
            $this->error = '文件路径不能为空';
            return false;
        }
        set_time_limit(600);
        $info = $this->uploadOne($_FILES['path'], array('mp3', 'wav', 'ogg'), 'music/');
        if ($info['status'] == false) {
            $this->error = $info['message'];
            return false;
        }
        $data['path'] = $info['path'];
        $data['music_md5'] = $info['md5'];
        $data['size'] = $info['size'];
        if (!empty($_FILES['cover']['name'])) {
            $info = $this->uploadOne($_FILES['cover'], array('jpg', 'jpeg', 'gif', 'png'), 'cover/');
            if ($info['status'] == false) {
                $this->error = $info['message'];
                @unlink('.' . $data['path']);//其中一个不成功，删除另一个
                return false;
            }
            $data['cover'] = $info['path'];
        }
        return true;
    }

    protected function _before_delete($options) {
        $paths = $this->field(array('path', 'cover'))->where($options['where'])->select();//考虑批量删除
        foreach ($paths as $row) {
            @unlink('.' . $row['path']);
            @unlink('.' . $row['cover']);
        }
    }

    protected function _before_update(&$data, $options) {
        set_time_limit(600);
        if (!empty($_FILES['path']['name'])) {
            $path = $this->where($options['where'])->getField('path');
            @unlink('.' . $path);
            $info = $this->uploadOne($_FILES['path'], array('mp3', 'wav', 'ogg'), 'music/');
            if ($info['status'] == false) {
                $this->error = $info['message'];
                return false;
            }
            $data['path'] = $info['path'];
            $data['music_md5'] = $info['md5'];
            $data['size'] = $info['size'];
        }
        if (!empty($_FILES['cover']['name'])) {
            $path = $this->where($options['where'])->getField('cover');
            @unlink('.' . $path);
            $info = $this->uploadOne($_FILES['cover'], array('jpg', 'jpeg', 'gif', 'png'), 'cover/');
            if ($info['status'] == false) {
                $this->error = $info['message'];
                empty($_FILES['path']['name']) or @unlink('.' . $data['path']);
                return false;
            }
            $data['cover'] = $info['path'];
        }
        return true;
    }

    public function uploadOne($file, array $exts, $path) {
        $upload = new \Think\Upload();
        $upload->maxSize = min(10 * 1024 * 1024, ini_get('upload_max_filesize') * 1024 * 1024);
        $upload->exts = $exts;
        $upload->savePath = $path;
        $info = $upload->uploadOne($file);
        if ($info) {
            $data = array(
                'status' => true,
                'message' => '成功',
                'path' => '/Uploads/' . $info['savepath'] . $info['savename'],
            );
            return array_merge($data, $info);
        }
        return array(
            'status' => false,
            'message' => $upload->getError(),
        );
    }
}