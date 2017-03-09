<?php
/*
 * view for the report tables
 * @author ctranel
 * 
 */
	
	$title = $block->title();
	if (!empty($title)): ?>
		<h2 class="block">
			<?php echo $title; ?>
		</h2>
	<?php endif; 
	$subtitle = $block->subtitle();
	if (!empty($subtitle)): ?>
		<h3 class="block">
			<?php echo $subtitle; ?>
		</h3>
	<?php endif; ?>	
	<?php if (!empty($benchmark_text)): ?>
		<h3 class="block">
		<?php echo $benchmark_text; ?>
		</h3>
	<?php endif;	
	if (isset($supplemental['links']) && is_array($supplemental['links'])):
		foreach($supplemental['links'] as $s): ?>
			<h3 class="block block-supplemental"><?php echo $s; ?></h3><?php
		endforeach;
	endif;
	if (isset($supplemental['comments']) && is_array($supplemental['comments'])):
		foreach($supplemental['comments'] as $s): ?>
			<h3 class="block block-supplemental"><?php echo $s; ?></h3><?php
		endforeach;
	endif;
	?>
	<table id="<?php echo $block->path(); ?>" class="tbl">
		<?php if (!empty($table_header)): ?>
				<thead> <?php echo $table_header; ?> </thead>
<?php elseif($block->hasPivot() && false): ?>
			<thead><th class="subcat-heading">Metric</th> <?php
			foreach($data[$block->pivotFieldName()] as $c): ?>
				<th class="subcat-heading">
					<?php echo $c; ?>
				</th><?php
			endforeach;
			 ?></thead>
		<?php endif;
		 ?><tbody>
			<?php $c = 1;
			if(isset($data) && is_array($data) && !empty($data)):
			$fields = $block->reportFields();
				if($fields):
					if($block->hasPivot()):
						foreach($fields as $f):
							if(isset($data[$f->dbFieldName()]) && is_array($data[$f->dbFieldName()])):
								$row_class = $c % 2 == 1?'odd':'even';
								?><tr class="<?php echo $row_class; ?>"><?php
								if(!$f->isDisplayed()){
									continue;
								}
								
								displayHeaderCell($f, $f->displayName());
								foreach($data[$f->dbFieldName()] as $k => $v):
									displayCell($f, $v);
								endforeach;
								$c++;
							endif;
						endforeach;
					else:
						foreach($data as $cr):
							$row_class = $c % 2 == 1?'odd':'even';
							?><tr class="<?php echo $row_class; ?>"><?php
							//@todo: pull this logic out of view?
							foreach($fields as $f)://$field_display => $field_name):
								if(!$f->isDisplayed()){
									continue;
								}
								$field_name = $f->dbFieldName();
								if(is_array($cr) && array_key_exists($field_name, $cr)){
									$value = $cr[$field_name];
								}
								elseif(is_object($cr) && property_exists($cr, $field_name)){
									$value = $cr->$field_name;
								}
								else{
									$value = '';
								}
								if($c > (count($data) - $block->getAppendedRowsCount())) {
    								displayCell($f, $value, $cr, true);
								}
                                else {
                                    displayCell($f, $value, $cr, false);
                                }
								endforeach;
							?></tr><?php
							$c++;
						endforeach;
					endif;
				else: 	
					?><td>No display fields were found.  Please make sure at least one field is selected in the settings section.</td><?php 
				endif;
			else:
				?><tr><td colspan="<?php echo $num_columns; ?>">No data was found.</td></tr><?php
			endif; 
		?></tbody>
	</table>
	<?php if(count($data) > 20): ?>
		<table id="fh-<?php echo $block->path(); ?>" class="fixed-header"></table>
	<?php endif; 
	
	function displayCell($f, $value, $cr = null, $appended_row = false){
		$field_name = $f->dbFieldName();
		if($f->isNumeric() && is_numeric($value)){// && $tmp_key != $value){
			$value = number_format($value, $f->decimalScale());
		}
		
		$supplemental = $f->dataSupplementalContent();
		if(isset($supplemental) && !$appended_row){
			//@todo: supplemental comments are not currently an option, only supplemental links
			$value = $supplemental['links'][0];
			preg_match_all('~\{(.*?)\}~', $value, $tmp);
			$arr_param_fields = $tmp[1];
			if(isset($arr_param_fields) && is_array($arr_param_fields) && !empty($arr_param_fields)){
				foreach($arr_param_fields as $p){
					//replace placeholder with row value
					if(isset($cr[$p])){
						$value = str_replace('{' . $p . '}', $cr[$p], $value);
					}
				}
			}
			//replace anchor tag content with field value
			//@todo: this should be a function with parameter values of $value and $field_name
			$doc = DOMDocument::loadXML($value);
			$tag = $doc->getElementsByTagName('a')->item(0);
			$newText = new DOMText($cr[$field_name]);
			$tag->removeChild($tag->firstChild);
			$tag->appendChild($newText);
			$value = $doc->saveXML();
		}
		?><td><?php echo $value; ?></td><?php
	}

	function displayHeaderCell($f, $value){
		$supplemental = $f->headerSupplementalContent();
		if(isset($supplemental)){
			//@todo: supplemental comments are not currently an option, only supplemental links
			$value = $value . ' ' . $supplemental['links'][0];
		}
		?><td><?php echo $value; ?></td><?php
	}
		
	
	
	?>
