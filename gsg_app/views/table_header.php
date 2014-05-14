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
			if(isset($arr_header_links[$th['field_name']])):
				$tip_text = '<div class="tip">';
				$link = $arr_header_links[$th['field_name']]['href'];
				$params = '';
				$site_params = '';
				if(isset($arr_header_links[$th['field_name']]['comment_id']) && !empty($arr_header_links[$th['field_name']]['comment_id'])){
					$arr_header_links[$th['field_name']]['params']['comment_id']['value'] = $arr_header_links[$th['field_name']]['comment_id'];
				}
				$t_class = !empty($arr_header_links[$th['field_name']]['class']) ? ' class="' . $arr_header_links[$th['field_name']]['class'] . '"' : '';
				$rel = !empty($arr_header_links[$th['field_name']]['rel']) ? ' rel="' . $arr_header_links[$th['field_name']]['rel'] . '"' : '';
				$title = !empty($arr_header_links[$th['field_name']]['title']) ? ' title="' . $arr_header_links[$th['field_name']]['title'] . '"' : '';
				if(is_array($arr_header_links[$th['field_name']]['params']) && !empty($arr_header_links[$th['field_name']]['params'])):
					foreach($arr_header_links[$th['field_name']]['params'] as $k => $v):
						if(isset($cr[$v['field']])):
							$params .= "$k=" . urlencode($cr[$v['field']]) . "&";
							$site_params .= "/" . urlencode($cr[$v['field']]);
						else:
							$params .= "$k=" . urlencode($v['value']) . "&";
							$site_params .= "/" . urlencode($v['value']);
						endif;
						$params = substr($params, 0, -1);
					endforeach;
				endif;
				if(substr($link, 0, 1) == '#'):
					$tip_text .= anchor($link, 'tip', $rel . $title . $t_class);
				elseif((strpos($link, 'http') === FALSE && !empty($link)) || strpos($link, 'myagsource.com') !== FALSE):
					$tip_text .= anchor(site_url($link . $site_params), 'tip', $rel . $title . $t_class);
				elseif(!empty($link)):
					$tip_text .= anchor(site_url($link . '?' . $params), 'tip', $rel . $title . $t_class);
				endif;
				$tip_text .= '</div>';
				$th['text'] = $tip_text . $th['text'];
			endif; //isset: arr_header_links
		//End column tips
		endif;//isset($th['field_name'])
		?> class = "<?php echo $class ?>"><?php
		echo $th['text'];
		?></th><?php
	endforeach;
?></tr><?php
endforeach;