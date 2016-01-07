<?php
/**
 * Created by PhpStorm.
 * User: e1399
 * Date: 2015/12/9
 * Time: 20:53
 */
namespace Home\Controller;
use Think\Controller;

require __DIR__ . '/../../Org/Excel/PHPExcel/IOFactory.php';

class ExcelController extends Controller {
    public function index() {
        if (IS_POST) {
            $filename = $_FILES['excel']['tmp_name'];
            $arr = $this->readExcel($filename, array('id', 'field1', 'field2', 'field3', 'field4'));
            print_r($arr);
            die;
        }
        $url = U('index');
        echo <<<HTML
<form action="{$url}" enctype="multipart/form-data" method="post">
<input type="file" name="excel">
<input type="submit" value="上传">
</form>
HTML;
    }

    /**
     * 读取excel
     * @param string $filename 文件名
     * @param array $fields 表的字段
     * @return array 二维数组，多条记录
     * @throws \PHPExcel_Exception
     * @link http://www.thinkphp.cn/code/403.html
     */
    public function readExcel($filename, array $fields) {
        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
        //只有一个工作簿
        /*$objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getSheet(0);
        $cols = $sheet->getHighestColumn();
        $rows = $sheet->getHighestRow();*/
        //多个工作簿
        $sheets = $objPHPExcel->getAllSheets();
        $data = array();
        $i = 0;
        foreach ($sheets as $sheet) {
            //$title = $sheet->getTitle();//工作簿名称
            $rows = $sheet->getHighestRow();//总行数
            $cols = $sheet->getHighestColumn();//总列数 如：I
            $cols = \PHPExcel_Cell::columnIndexFromString($cols);//转成数字列
            //$cols = min($cols, count($fields));//以字段的列为准
            $num = count($fields);
            if ($cols > $num) {
                throw new \PHPExcel_Exception("表格的列数{$cols}大于所需的列数{$num}！");
            }
            $cols = $num;
            //TODO 如果第一行是标题，则$row可以从2开始
            for ($row = 1; $row <= $rows; ++$row) {
                for ($col = 0; $col < $cols; ++$col) {
                    $cell = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                    $field = $fields[$col];
                    $data[$i][$field] = $cell;
                }
                ++$i;
            }
        }
        return $data;
    }
}