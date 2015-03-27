<?php
//if sort info is not set, set it to an array with one empty element
if(!isset($arr_sort_by)) $arr_sort_by = array('');
if(!isset($arr_sort_order)) $arr_sort_order = array('');
//var_dump($sorts);
if(isset($structure) && is_array($structure)):
	foreach($structure as $row): ?>
	<tr>
		<?php foreach($row as $k => $th):
			/* sort
			$sort_by = $arr_sort_by[0];
			$sort_order = $arr_sort_order[0];
			if(!empty($arr_field_sort) && !empty($th['field_name']) && !empty($arr_field_sort[$th['field_name']])):
				$default_order = $arr_field_sort[$th['field_name']];
				$non_default_order = $default_order == 'ASC'?'DESC':'ASC';
				$link_sort_order = (($sort_order == $default_order && $sort_by == $th['field_name']) ? $non_default_order : $default_order);
			else:
				$link_sort_order = 'DESC';
			endif;
			*/
			//html properties
			$th_id = str_replace(' ', '_', strtolower($th->text()));
			$class = ($th->colspan() > '1') ? 'cat-heading' : 'subcat-heading';
			$class .= " $th_id";
			 ?><th id="<?php echo $th_id; ?>" colspan="<?php echo $th->colspan(); ?>" rowspan="<?php echo $th->rowspan(); ?>"<?php 
			$inner_html = $th->text();
			$after_text = '';
	//echo $th->defaultSortOrder();
	/*		if (isset($th['field_name'])):
				if ($sort_by == $th['field_name']):
					$after_text = $sort_order=='ASC'?"▲":"▼";
				endif;
	 			//$extra = array('rel'=>$th->text(), 'id'=>$th['field_name'] . '_tip');
				if (is_array($arr_unsortable_columns) && !in_array($th['field_name'], $arr_unsortable_columns)):
					$submit_url = '#';
					$extra = Array('onclick'=>"return updateBlock('block-canvas" . $report_count . "', '$block', $report_count, '". $th['field_name'] . "', '$link_sort_order', true);");
	
					$inner_html = anchor($submit_url, $th->text(), $extra);
				endif;
				$inner_html .= $after_text;
			endif;//isset($th['field_name'])
	*/
			//Supplemental
			$supplemental = $th->supplementalLink();
			if(isset($supplemental)):
				$inner_html = '<div class="tip">' . $supplemental . '</div>' . $th->text();
			endif;
	
			?> class = "<?php echo $class ?>"><?php
			echo $inner_html;
			?></th><?php
		endforeach;
	?></tr><?php
	endforeach;
endif;