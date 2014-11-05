<?php
//if sort info is not set, set it to an array with one empty element
if(!isset($arr_sort_by)) $arr_sort_by = array('');
if(!isset($arr_sort_order)) $arr_sort_order = array('');

foreach($structure as $row): ?>
<tr>
	<?php foreach($row as $k => $th):
		$sort_by = $arr_sort_by[0];
		$sort_order = $arr_sort_order[0];
		if(!empty($arr_field_sort) && !empty($th['field_name']) && !empty($arr_field_sort[$th['field_name']])):
			$default_order = $arr_field_sort[$th['field_name']];
			$non_default_order = $default_order == 'ASC'?'DESC':'ASC';
			$link_sort_order = (($sort_order == $default_order && $sort_by == $th['field_name']) ? $non_default_order : $default_order);
		else:
			$link_sort_order = 'DESC';
		endif;
		$th_id = isset($th['field_name'])?$th['field_name']:str_replace(' ', '_', strtolower($th['text']));
		$class = ($th['colspan'] > '1') ? 'cat-heading' : 'subcat-heading';
		$class .= " $th_id";
		 ?><th id="<?php echo $th_id; ?>" colspan="<?php echo $th['colspan']; ?>" rowspan="<?php echo $th['rowspan']; ?>"<?php 
		if (isset($th['field_name'])):
			$after_text = '';
			if ($sort_by == $th['field_name']):
				$after_text = $sort_order=='ASC'?"▲":"▼";
			endif;
 			//$extra = array('rel'=>$th['text'], 'id'=>$th['field_name'] . '_tip');
			if (is_array($arr_unsortable_columns) && !in_array($th['field_name'], $arr_unsortable_columns)):
				$submit_url = '#';
				$extra = Array('onclick'=>"return updateBlock('table-canvas" . $report_count . "', '$block', $report_count, '". $th['field_name'] . "', '$link_sort_order', 'table', true);");

				$th['text'] = anchor($submit_url, $th['text'], $extra);
			endif;
			$th['text'] .= $after_text;
			//Column tips
			if(isset($arr_header_links[$th['field_name']]) && is_array($arr_header_links[$th['field_name']]) && !empty($arr_header_links[$th['field_name']])):
				foreach($arr_header_links[$th['field_name']] as $t):
					$th['text'] = '<div class="tip">' . $t . '</div>' . $th['text'];
				endforeach;
			endif;
			//End column tips
		endif;//isset($th['field_name'])
		?> class = "<?php echo $class ?>"><?php
		echo $th['text'];
		?></th><?php
	endforeach;
?></tr><?php
endforeach;