<?php
if(isset($structure) && is_array($structure)):
	$sort_array = $block->getSortArray();
	foreach($structure as $row): ?>
	<tr>
		<?php foreach($row as $k => $th):
			//html properties
			$th_id = str_replace(' ', '_', strtolower($th->text()));
			$class = ($th->colspan() > '1') ? 'cat-heading' : 'subcat-heading';
			$class .= " $th_id";
			 ?><th id="<?php echo $th_id; ?>" colspan="<?php echo $th->colspan(); ?>" rowspan="<?php echo $th->rowspan(); ?>"<?php 
			$inner_html = $th->text();
			$after_text = '';

			//Sort
			$field_name = $th->dbFieldName();
			if (isset($field_name)):
				if (isset($sort_array[$field_name])):
					$after_text = $sort_array[$field_name] === 'ASC' ? "▲" : "▼";
					$link_sort_order = $sort_array[$field_name] === 'ASC' ? "desc" : "asc";
				else:
					$link_sort_order = $th->defaultSortOrder();
				endif;
	 			//$extra = array('rel'=>$th->text(), 'id'=>$field_name . '_tip');
				if ($th->isSortable()):
					$submit_url = '#';
					$extra = ['onclick'=>"return updateBlock('block-canvas" . $report_count . "', '" . $block->path() . "', $report_count, '". $field_name . "', '$link_sort_order', true);"];
	
					$inner_html = anchor($submit_url, $th->text(), $extra);
				endif;
				$inner_html .= $after_text;
			endif;
	
			//Supplemental
			$supplemental = $th->supplementalLink();
			if(isset($supplemental)):
				$inner_html = '<div class="tip">' . $supplemental . '</div>' . $inner_html;
			endif;
	
			?> class = "<?php echo $class ?>"><?php
			echo $inner_html;
			?></th><?php
		endforeach;
	?></tr><?php
	endforeach;
endif;
