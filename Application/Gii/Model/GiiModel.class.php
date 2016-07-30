<?php
namespace Gii\Model;

class GiiModel {
	/**
	 * 解析列表模板
	 * @param string $content 模板内容
	 * @param array $fields 表的所有字段及属性
	 * @param string $pri 主键字段
	 * @param string $comment 表注释
	 * @return string
	 */
	static public function parseIndexTemplate($content, array $fields, $pri, $comment) {
		/*搜索部分*/
		$head = '<form class=" form-inline" method="get" action="__ACTION__" id="form-search">';
		foreach ($fields as $row) {
			if($row['field']==$pri || empty($row['key']))
				continue;
			$head .= $row['comment'].'<input type="text" placeholder="'.$row['note'].'" name="'.$row['field'].'"
				class="form-control" value="'."<?php echo I('get.{$row['field']}');?>".'">';
		}
		$head .= '<button type="submit" class="btn btn-info"  onclick="return search(\'#form-search\')">搜索</button>
					<a class="btn btn-info pull-right" href="{:U(\'add\')}" data-toggle="modal" data-target="#myModal">
						<i class="fa fa-plus"></i>添加'.$comment.'
					</a>
				  </form>';

		/*标题部分*/
		$th = '';
		foreach ($fields as $row) {
			$th .= '<th>'.$row['comment'].'</th>';
		}
		$title =
			"<tr>
				<th><input type=\"checkbox\" class=\"check_all\" /></th>
				{$th}
				<th>操作</th>
			 </tr>";

		/*列表部分*/
		$td = '';
		foreach ($fields as $row) {
			$td .= '<td>' . "<?php echo \$row['{$row['field']}'];?>" . '</td>';
		}

		/*页码*/
		$page = '<?php echo $page;?>';

		/*JS部分*/
		$td = '';
		foreach ($fields as $row) {
			$td .= 'html += "<td>"+list[i].'.$row['field'].'+"</td>";
					';
		}
		$list = <<<JS
	function getList(form){
	    $.ajax({
	        type:"get",
	        url:"__CONTROLLER__/search",
	        data:$(form).serialize()+"&p="+curr,
	        dataType:"json",
	        success:function(data){
	            var list = data.data;
	            if(list == null){
	                $("#form-list").html("");
	                $("#page").html("");
	                return;
	            }
				var html = "";
				for(var i in list){
				    html += '<tr><td><input type="checkbox" name="delid[]" data="id" value="'+list[i].{$pri}+'"></td>';
				    {$td}
				    html += '<td><a class="btn btn-xs btn-warning a-color" href="__CONTROLLER__/save/id/'+list[i].{$pri}+
				            '" data-toggle="modal" data-target="#myModal"><i class="fa fa-pencil"></i></a> ';
				    html += '<button type="button" class="btn btn-xs btn-danger" data-toggle="popover" data-placement="left" data-title="是否删除？" data-html="true" '+
				        'data-content="<button type=\'button\' class=\'btn btn-danger\' onclick=\'del(this)\'>确定</button> '+
				        '<button type=\'button\' class=\'btn btn-default\' onclick=\'$(this).parent().parent().prev().click()\'>取消</button>">'+
				        '<a class="a-color" href="__CONTROLLER__/del/id/'+list[i].{$pri}+'" onclick="return false;"><i class="fa fa-remove"></i></a></button></td></tr>';
				}
				$("#form-list").html(html);
	            var page = data.page;
	            page = page.replace(/<a(.*?)>(.*?)<\/a>/gi, "<li><a$1>$2</a></li>");
	            page = page.replace(/<span(.*?)>(.*?)<\/span>/, "<li><span$1>$2</span></li>");
	            $("#page").html(page);
	            $('button[data-toggle="popover"]').popover();//重新载入弹出框
	            replace();
	        }
	    });
	    return false;
	}
	$(function(){search('#form-search')});
JS;
		/*其它*/
		$icon = "<?php echo \$menu['icon'];?>";
		$menu = "<?php echo \$menu['name'];?>";
		$search = array('%HEAD%', '%TITLE%', '%PAGE%', '%LIST%', '%COMMENT%', '%ICON%', '%MENU%');
		$replace = array($head, $title, $page, $list, $comment, $icon, $menu);
		return str_replace($search, $replace, $content);
	}

	/**
	 * 解析添加模板
	 * @param string $content 模板内容
	 * @param array $fields 表的所有字段及属性
	 * @param string $pri 主键字段
	 * @param string $comment 表注释
	 * @return string
	 */
	static public function parseAddTemplate($content, array $fields, $pri, $comment) {
		$table = '';
		foreach ($fields as $row) {
			if($row['field']==$pri || $row['type']=='timestamp')
				continue;
			$default = is_null($row['default']) ? '' : $row['default'];
			$td = '';
			switch ($row['type']){
				case 'char':
				case 'varchar'://文本框
					$td = '<input type="text" class="form-control" id="'.$row['field'].'" placeholder="'.$row['note'].'" name="'.$row['field'].'" value="'.$default.'" maxlength="'.$row['length'].'" />';
					break;
				case 'text'://文本域
					$td =  '<textarea class="form-control" id="'.$row['field'].'" name="'.$row['field'].'" rows="10" placeholder="'.$row['note'].'"></textarea>';
					break;
				case 'enum'://单选框
					$list = explode(',', substr($row['types'], 5, -1));//Array ( [0] => '显示' [1] => '隐藏' )
					foreach ($list as $val){
						$val = mb_substr($val, 1, -1, 'utf-8');//引号去掉
						$check = $val==$default ? ' checked="checked"' : '';
						$td .= ' <input type="radio" name="'.$row['field'].'" value="'.$val.'"'.$check.' />'.$val;
					}
					break;
				case 'smallint':
				case 'tinyint'://选择框
					preg_match_all('/(?:.*[:：])?(?:(-?\d)([^,.]+)),?/', $row['comment2'], $matches);
					if (!empty($matches[1])) {
						$td =  '<select id="'.$row['field'].'" name="'.$row['field'].'" class="form-control">';
						foreach ($matches[1] as $key => $val){
							$select = $val==$default ? ' selected="selected"' : '';
							$td .= '<option value="'.$val.'"'.$select.'>'.$matches[2][$key].'</option>';
						}
						$td .= '</select>';
					} else {
						$td = '<input type="text" class="form-control" id='.$row['field'].' placeholder="'.$row['note'].'" name="'.$row['field'].'" value="'.$default.'" />';
					}
					break;
				case 'set'://复选框
					$str = substr($row['types'], strpos($row['types'], '(')+1, -1);
					$list = explode(',', $str);
					foreach ($list as $val){
						$val = mb_substr($val, 1, -1, 'utf-8');
						$td .= ' <input type="checkbox" class="" name="'.$row['field'].'[]" value="'.$val.'" />'.$val;
					}
					break;
				case 'datetime':
				case 'date':
				case 'decimal':
				default :
					$td = '<input type="text" class="form-control" id="'.$row['field'].'" placeholder="'.$row['note'].'" name="'.$row['field'].'" value="'.$default.'" />';
					break;
			}

			$tip = '';
			if(($row['null']=='NO') && is_null($row['default']))
				$tip = '<span class="text-info">*</span>';
			$table .=
			"<div class=\"form-group\">
				<label for=\"{$row['field']}\" class=\"col-sm-2 control-label\">{$row['comment']}</label>
				<div class=\"col-sm-9\">
					{$td}
				</div>
				<div class=\"col-sm-1\"  style=\"line-height: 34px;\">
					{$tip}
				</div>
			</div>
			";
		}
		$search = array('%TABLE%', '%COMMENT%');
		$replace = array($table, $comment);
		return str_replace($search, $replace, $content);
	}

	/**
	 * 解析修改模板
	 * @param string $content 模板内容
	 * @param array $fields 表的所有字段及属性
	 * @param string $pri 主键字段
	 * @param string $comment 表注释
	 * @return string
	 */
	static public function parseSaveTemplate($content, array $fields, $pri, $comment) {
		$pri_str = "<?php echo \$info['$pri'];?>";
		$table = '<input type="hidden" name="'.$pri.'" data-value="'.$pri_str.'" value="'.$pri_str.'" />';
		foreach ($fields as $row) {
			if($row['field']==$pri || $row['type']=='timestamp')
				continue;
			$td = '';
			switch ($row['type']){
				case 'char':
				case 'varchar'://文本框
					$td = '<input type="text" class="form-control" id="'.$row['field'].'" placeholder="'.$row['note'].
						'" name="'.$row['field'].'" value="'."<?php echo \$info['{$row['field']}'];?>".
						'" data-value="'."<?php echo \$info['{$row['field']}'];?>".
						'" maxlength="'.$row['length'].'" />';
					break;
				case 'text'://文本域
					$td = '<textarea class="form-control" id="'.$row['field'].'" name="'.$row['field'].
						'" data-value="'."<?php echo \$info['{$row['field']}'];?>".
						'" rows="10" placeholder="'.$row['note'].'">'."<?php echo \$info['{$row['field']}'];?>".'</textarea>';
					break;
				case 'enum'://单选框
					$list = explode(',', substr($row['types'], 5, -1));//Array ( [0] => '显示' [1] => '隐藏' )
					foreach ($list as $val){
						$val = mb_substr($val, 1, -1, 'utf-8');
						$td .= ' <input type="radio" name="'.$row['field'].'" data-value="'."<?php echo \$info['{$row['field']}'];?>".
							'" value="'.$val.'"'."<?php echo '$val'==\$info['{$row['field']}']?' checked=\"checked\"':'';?>".' />'.$val;
					}
					break;
				case 'smallint':
				case 'tinyint'://选择框
					preg_match_all('/(?:.*[:：])?(?:(-?\d)([^,.]+)),?/', $row['comment2'], $matches);
					if (!empty($matches[1])) {
						$td = '<select id="'.$row['field'].'" name="'.$row['field'].'" data-value="'."<?php echo \$info['{$row['field']}'];?>".'" class="form-control">';
						foreach ($matches[1] as $key => $val){
							$td .= '<option value="'.$val.'"'."<?php echo '$val'==\$info['{$row['field']}']?' selected=\"selected\"':'';?>".'>'.$matches[2][$key].'</option>';
						}
						$td .= '</select>';
					} else {
						$td = '<input type="text" class="form-control" id="'.$row['field'].'" placeholder="'.$row['note'].'" name="'.$row['field'].
							'" data-value="'."<?php echo \$info['{$row['field']}'];?>".
							'" value="'."<?php echo \$info['{$row['field']}'];?>".'" />';
					}
					break;
				case 'set'://复选框
					$str = substr($row['types'], strpos($row['types'], '(')+1, -1);
					$list = explode(',', $str);
					foreach ($list as $val){
						$val = mb_substr($val, 1, -1, 'utf-8');
						$td .= ' <input type="checkbox" class="" name="'.$row['field'].'[]" value="'.$val.'"'.
							"<?php echo '$val'==\$info['{$row['field']}']?' checked=\"checked\"':'';?>".' data-value="'."<?php echo \$info['{$row['field']}'];?>".
							'" />'.$val;
					}
					break;
				case 'datetime':
				case 'date':
				case 'decimal':
				default :
					$td .= '<input type="text" class="form-control" id="'.$row['field'].'" placeholder="'.$row['note'].'" name="'.$row['field'].
						'" data-value="'."<?php echo \$info['{$row['field']}'];?>".'" value="'."<?php echo \$info['{$row['field']}'];?>".'" />';
					break;
			}

			$tip = '';
			if(($row['null']=='NO') && is_null($row['default']))
				$tip = '<span class="text-info">*</span>';
			$table .=
			"<div class=\"form-group\">
				<label for=\"{$row['field']}\" class=\"col-sm-2 control-label\">{$row['comment']}</label>
				<div class=\"col-sm-9\">
					{$td}
				</div>
				<div class=\"col-sm-1\"  style=\"line-height: 34px;\">
					{$tip}
				</div>
			</div>";
		}
		$search = array('%TABLE%', '%COMMENT%');
		$replace = array($table, $comment);
		return str_replace($search, $replace, $content);
	}
}