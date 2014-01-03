<?php 	//Description: Php script for creating tables in MyAgSource
		//Created by: Chris Tranel

?>

	<?php if (!empty($table_heading)): ?>
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
		<?php endif; ?>
		<tbody>
			<?php $c = 1;
			if(!empty($report_data) && is_array($report_data)):
				foreach($report_data as $cr):
					$row_class = $c % 2 == 1?'odd':'even'; ?>
					<tr class="<?php echo $row_class; ?>">
					<?php if(is_array($fields)):
						foreach($fields as $field_display => $field_name):
							if(is_array($cr) && array_key_exists($field_name, $cr)) $value = $cr[$field_name];
							elseif(is_object($cr) && property_exists($cr, $field_name)) $value = $cr->$field_name;
							else $value = '';
				
					/* @todo Chris: PROGRAM DB_FIELD LINK INFO */
							if(in_array($field_name, array('barn_name', 'control_num', 'visible_id'))) $value = 
								'<a href="http://newdata.crinet.com/agsourcedm/index.php?action=events&comp_num=507&UserID=35999999&Password=12345&source=myagsource" title="View Cow Data">' . $value . '</a>'; ?>
							<td>
								<?php echo $value; ?>
							</td>
						<?php endforeach;
					else: 	?>
						<td>
							No display fields were found.  Please make sure at least one field is selected in the settings section.
						</td>
					<?php endif; ?>
					</tr><?php
					$c++;
				endforeach;
			else: //@todo Chris - should generate colspan when creating header, but this will do for now ?>
				<tr><td colspan="25">No data was found.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>