<?php
namespace Util\Excel;

class ExcelUtil {
	/**
	 * 数据导出到excel
	 * @param array $data 二维数据
	 * @param string $filename 文件名
	 * @param array $title 表的标题
	 * @param string $sheet_title 工作薄名称
	 * @param string $type excel类型:Excel2007|Excel5
	 * @throws \PHPExcel_Exception
	 */
	static public function dataToExcel($data, $filename, $title=array(), $sheet_title='', $type='Excel2007') {
		$word = range('A', 'Z');
		$cols = count(current($data));
		if ($cols > 26) {
			$cols = min(256, $cols);//excel最大256列:A-IV
			$num = $cols - 26;
			for ($i = 0; $i < $num; ++$i) {
				$word[] = $word[floor($i / 26)] . $word[$i % 26];
			}
		}
		$excel = new \PHPExcel();
		$sheet = $excel->setActiveSheetIndex(0);
		$sheet_title and $sheet->setTitle($sheet_title);
		$first = 1;
		if (!empty($title)) {
			$sheet->getStyle(1)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);//设置第一行为文本
			foreach ($title as $key => $val) {
				$sheet->setCellValueExplicit($word[$key] . '1', $val);
				$sheet->getColumnDimension($word[$key])->setAutoSize(true);
			}
			$first = 2;
		}
		$i = 0;//行索引
		foreach ($data as $row) {
			$j = 0;//列索引
			$current_row = $i + $first;//表格当前的行号
			$sheet->getStyle($current_row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);//设置当前行为文本
			foreach ($row as $val) {
				$sheet->setCellValueExplicit($word[$j] . $current_row, $val);
				$sheet->getColumnDimension($word[$j])->setAutoSize(true);//列自适应宽度
				++$j;
			}
			++$i;
		}
		$writer = \PHPExcel_IOFactory::createWriter($excel, $type);
		$writer->save($filename);
	}

	/**
	 * 读取excel数据
	 * @param string $filename 文件名
	 * @param array $filed 字段名
	 * @param int $start 开始读取的行数
	 * @return array
	 * @throws \PHPExcel_Exception
	 */
	static public function excelToData($filename, $filed=array(), $start=2) {
		$excel = \PHPExcel_IOFactory::load($filename);
		$sheets = $excel->getAllSheets();
		$data = array();
		foreach ($sheets as $sheet) {
			$rows = $sheet->getHighestRow();//总行数
			$cols = $sheet->getHighestColumn();//总列数
			$cols = \PHPExcel_Cell::columnIndexFromString($cols);//字母转成数据列
			empty($filed) and $filed = range(0, $cols - 1);
			$cols = min($cols, count($filed));//防止字段index溢出
			for ($row = $start; $row <= $rows; ++$row) {
				for ($col = 0; $col < $cols; ++$col) {
					$column = $filed[$col];
					$data[$row][$column] = $sheet->getCellByColumnAndRow($col, $row)->getValue();
				}
			}
		}
		return $data;
	}

	/**
	 * 修改Excel内容
	 * @param array $data 二维数据
	 * @param string $filename 需要修改的文件
	 * @param string $output 输出的文件
	 * @param int $start 开始编辑的行数
	 * @param int $sheet_index 工作簿的下标
	 * @param string $sheet_title 工作薄名称
	 * @param string $type excel类型:Excel2007|Excel5
	 * @throws \PHPExcel_Exception
	 */
	static public function updateExcel($data, $filename, $output='php://output', $start=2, $sheet_index=0, $sheet_title='', $type='Excel2007') {
		$excel = \PHPExcel_IOFactory::load($filename);
		$excel->getSheetCount() < $sheet_index + 1 and $excel->createSheet($sheet_index);//如果没有当前的工作簿，则创建
		$sheet = $excel->getSheet($sheet_index);
		$sheet_title and $sheet->setTitle($sheet_title);
		foreach ($data as $row) {
			$j = 0;
			$sheet->getStyle($start)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);//设置当前行为文本
			foreach ($row as $val) {
				$sheet->setCellValueExplicitByColumnAndRow($j, $start, $val);
				$sheet->getColumnDimensionByColumn($j)->setAutoSize(true);//列自适应宽度
				++$j;
			}
			++$start;
		}
		$writer = \PHPExcel_IOFactory::createWriter($excel, $type);
		$writer->save($output);
	}
}