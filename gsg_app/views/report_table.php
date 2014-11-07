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
	<?php endif;	
	if (isset($supplemental) && is_array($supplemental)): ?>
	<?php 
		foreach($supplemental as $s): ?>
			<h3 class="block block-supplemental"><?php echo $s; ?></h3>
	<?php endforeach;
	?>
	<?php
	endif;
	?>
	<table id="<?php echo $table_id; ?>" class="tbl">
		<?php if (!empty($table_header)): ?>
				<thead> <?php echo $table_header; ?> </thead>
		<?php endif;
		 ?><tbody>
			<?php $c = 1;
			if(!empty($report_data) && is_array($report_data)):
				foreach($report_data as $cr):
				$row_class = $c % 2 == 1?'odd':'even';
				/*
				 * $field_label is used when the data is pivoted.  In those cases, the db_field does not come along with the data, so the label of the
				 * first column is used to look up is_numeric and decimal values
				 */				
				$field_label = current($cr);
				?><tr class="<?php echo $row_class; ?>"><?php
					if(is_array($fields)):
						//@todo: pull this logic out of view?
						foreach($fields as $field_display => $field_name):
							if(is_array($cr) && array_key_exists($field_name, $cr)){
								$value = $cr[$field_name];
							}
							elseif(is_object($cr) && property_exists($cr, $field_name)){
								$value = $cr->$field_name;
							}
							else{
								$value = '';
							}
							//if data is from a pivot, use $field_label else $field_name
							$tmp_key = isset($arr_decimal_places[$field_label]) ? $field_label : $field_name;
							if($field_name == 'control_num' || $field_name == 'list_order_num'){
								$value = number_format($value,$arr_decimal_places[$tmp_key],'.','');
							}
							elseif(in_array($tmp_key, $arr_numeric_fields) && is_numeric($value) && $tmp_key != $value){
								$value = number_format($value, $arr_decimal_places[$tmp_key]);
							}
							if(isset($arr_field_links[$field_name]) && !empty($arr_field_links[$field_name])){
								//if there is a field name place-holder in the link, replace it with field value
								$value = current($arr_field_links[$field_name]);
								preg_match_all('~\{(.*?)\}~', $value, $tmp);
								$arr_param_fields = $tmp[1];
								if(isset($arr_param_fields) && is_array($arr_param_fields) && !empty($arr_param_fields)){
									foreach($arr_param_fields as $p){
										if(isset($cr[$p])){
											$value = str_replace('{' . $p . '}', $cr[$p], $value);
										}
									}
								}
								//replace anchor tag content with field value
								//@todo: this should be a function with parameter values of $value and $cr[$field_name]
								$doc = DOMDocument::loadXML($value);
								$tag = $doc->getElementsByTagName('a')->item(0);
								$newText = new DOMText($cr[$field_name]);
								$tag->removeChild($tag->firstChild);
								$tag->appendChild($newText);
								$value = $doc->saveXML();
							}
							?><td><?php echo $value; ?></td><?php
						endforeach;
					else: 	
						?><td>No display fields were found.  Please make sure at least one field is selected in the settings section.</td><?php 
					endif;
					?></tr><?php
					$c++;
				endforeach;
			else:
				?><tr><td colspan="<?php echo $num_columns; ?>">No data was found.</td></tr><?php
			endif; 
		?></tbody>
	</table>
