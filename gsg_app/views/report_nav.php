<div id="report-links">
<?php

	$id_type = '';
	$select_label = '';
	$key_name = '';
	$value_name = '';

	//determine report section and which set of links (if any) to display
	
	if($class == 'genetic_summary') {

		$id_type = 'breed';
		$id_label = 'Breed';
		$select_label = 'Select Breed:';
		$key_name = 'breed_code';
		$value_name = 'breed_name';

	} else {
	
		$id_type = 'pstring';
		$id_label = 'PString';
		$select_label = 'Select Pstring:';
		$key_name = 'pstring';
		$value_name = 'publication_name';
	
	}

	if(isset($arr_links) && !empty($arr_links) && count($arr_links) > 1) {
		echo '<div id="'.$id_type.'-links">';
		echo '<a name="'.$id_type.'-nav" class="section-header" id="select-'.$id_type.'">Select '.$id_label.':</a>';
		echo '<ul class="pstring-nav">';
		$top = count($arr_links);
		$first = TRUE;
		for( $c=0; $c < $top; $c++) {
			if($first) {
				$li_class = 'first';
				$first = FALSE;
			} else {
				$li_class = FALSE;
			}
			echo '<li';
			if($li_class) {
				echo ' class="first" style="font-weight: bold"';
			}
			echo '><a href="#chart" class="pstring-link';
			if ($arr_links[$c][$key_name] == $curr_base_filter) {
				echo ' current';
			}
			echo '" id="'.$arr_links[$c][$key_name].'" onclick="$(\'.'.$id_type.'-filter-item > input\').prop(\'checked\', false); $(\'input:checkbox[value='.$arr_links[$c][$key_name].']\').prop(\'checked\', true); return updatePage(this);">'.$arr_links[$c][$value_name];			
		}
		echo '</ul>';
		echo '</div>';
	}
		
if(isset($arr_pages) && is_array($arr_pages) && count($arr_pages) > 1): ?>
	<div id="block-links">
		<a class="section-header" id="select-block">Select Report Page:</a>
		<ul class="report-nav" id="current">
		<?php
		$first = TRUE;
			foreach($arr_pages as $e):
				if($first):
					$li_class = 'first';
					$first = FALSE;
				else :
					$li_class = FALSE; 
				endif; ?>
				<li<?php if($li_class) echo ' class="first"'; ?>><a href="<?php echo site_url($section_path . '/' . $e['url_segment']); ?>" id="<?php echo $e['url_segment']; ?>"<?php if($e['url_segment'] == $curr_page) echo ' class="current"'; ?>><?php echo $e['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php
endif; ?>
</div>