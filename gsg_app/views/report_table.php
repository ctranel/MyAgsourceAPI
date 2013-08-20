	<?php if (!empty($table_heading)): 
		?><h2 class="block"><?php echo $table_heading; ?></h2><?php
	endif;	?>	
	<?php if (!empty($table_sub_heading)): 
		?><h3 class="block"><?php echo $table_sub_heading; ?></h3><?php
	endif;	?>	
	<?php if (!empty($table_benchmark_text)): 
		?><h3 class="block"><?php echo $table_benchmark_text; ?></h3><?php
	endif;	?>	
	
	<table id="<?php echo $table_id; ?>" class="tbl"><?php
		if (!empty($table_header)): 
			?><thead><?php echo $table_header; ?></thead><?php
		endif;		
		?><tbody><?php
			$c = 1;
			if(!empty($report_data) && is_array($report_data)):
				foreach($report_data as $cr):
				//if($c % 36 == 0) echo $table_header;
					$row_class = $c % 2 == 1?'odd':'even';
					 ?><tr class="<?php echo $row_class; ?>"><?php 
					 if(is_array($fields)):
							foreach($fields as $field_display => $field_name):
								if(is_array($cr) && array_key_exists($field_name, $cr)) $value = $cr[$field_name];
								elseif(is_object($cr) && property_exists($cr, $field_name)) $value = $cr->$field_name;
				/* PROGRAM DB_FIELD LINK INFO */
								if($field_name == 'barn_name') $value = '<a href="http://newdata.crinet.com/agsourcedm/index.php?action=events&comp_num=507&UserID=35999999&Password=12345&source=myagsource" title="View Cow Data">' . $value . '</a>';
								?><td><?php echo $value; ?></td><?php
							endforeach;
						else:
							?><td>No display fields were found.  Please make sure at least one field is selected in the settings section.</td><?php
						endif;
					 ?></tr><?php
					 $c++;
				endforeach;
			else: //should generate colspan when creating header, but this will do for now ?>
				<tr><td colspan="25">No data was found.</td></tr>
			<?php endif;
		?></tbody>
	</table>