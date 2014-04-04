<?php
/*
 * view for the report tables
 * @author ctranel
 * 
 */
	
	if (!empty($table_heading)): ?>
		<h2 class="block">
			<?php echo $table_heading; ?>
		</h2>
	<?php endif; ?>	
	<?php if (!empty($table_sub_heading)): ?>
		<h3 class="block">
			<?php echo $table_sub_heading; ?>
		</h3>
	<?php endif; ?>	
	<?php if (!empty($table_benchmark_text)): ?>
		<h3 class="block">
		<?php echo $table_benchmark_text; ?>
		</h3>
	<?php endif; ?>	
	<table id="<?php echo $table_id; ?>" class="tbl">
		<?php if (!empty($table_header)): ?>
				<thead> <?php echo $table_header; ?> </thead>
		<?php endif;
		 ?><tbody>
			<?php $c = 1;
			if(!empty($report_data) && is_array($report_data)):
				foreach($report_data as $cr):
				$row_class = $c % 2 == 1?'odd':'even'; 
					?><tr class="<?php echo $row_class; ?>"><?php
					if(is_array($fields)):
						//@todo: pull this logic out of view?
						foreach($fields as $field_display => $field_name):
							if(is_array($cr) && array_key_exists($field_name, $cr)) $value = $cr[$field_name];
							elseif(is_object($cr) && property_exists($cr, $field_name)) $value = $cr->$field_name;
							else $value = '';
							if($field_name == 'control_num' || $field_name == 'list_order_num') $value = number_format($value,$arr_decimal_places[$field_name],'.','');
							elseif(in_array($field_name,$arr_numeric_fields)) $value = number_format($value, $arr_decimal_places[$field_name]);
							if(isset($arr_field_links[$field_name])){
								$link = $arr_field_links[$field_name]['href'];
								$params = '';
								$site_params = '';
								$class = !empty($arr_field_links[$field_name]['class']) ? ' class="' . $arr_field_links[$field_name]['class'] . '"' : '';
								$rel = !empty($arr_field_links[$field_name]['rel']) ? ' rel="' . $arr_field_links[$field_name]['rel'] . '"' : '';
								$title = !empty($arr_field_links[$field_name]['title']) ? ' title="' . $arr_field_links[$field_name]['title'] . '"' : '';
								if(is_array($arr_field_links[$field_name]['params']) && !empty($arr_field_links[$field_name]['params'])){
									foreach($arr_field_links[$field_name]['params'] as $k => $v){
										if(isset($cr[$v['field']])){
											$params .= "$k=" . urlencode($cr[$v['field']]) . "&";
											$site_params .= "/" . urlencode($cr[$v['field']]);
										}
										else{
											$params .= "$k=" . urlencode($v['value']) . "&";
											$site_params .= "/" . urlencode($v['value']);
										}
										$params = substr($params, 0, -1);
									}
								}
								if(substr($link, 0, 1) == '#'){
									$value = anchor($link, $value, $rel . $title . $class);
								}
								elseif((strpos($link, 'http') === FALSE && !empty($link)) || strpos($link, 'myagsource.com') !== FALSE){
									$value = anchor(site_url($link . $site_params), $value, $rel . $title . $class);
								}
								elseif(!empty($link)){
									$value = anchor(site_url($link . '?' . $params), $value, $rel . $title . $class);
								}
							}
							?><td><?php echo $value; ?></td><?php
						endforeach;
					else: 	
						?><td>No display fields were found.  Please make sure at least one field is selected in the settings section.</td><?php 
					endif;
					?></tr><?php
					$c++;
				endforeach;
			else: //@todo should generate colspan when creating header, but this will do for now 
				?><tr><td colspan="25">No data was found.</td></tr><?php
			endif; 
		?></tbody>
	</table>
	
	<!-- popup div -->
	<div id="datafield-popup" class="datafield-popup mfp-hide">
		Popup content
	</div>