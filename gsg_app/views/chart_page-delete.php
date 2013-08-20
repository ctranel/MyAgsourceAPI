<?php 
if (!empty($page_header)) echo $page_header;
if(isset($herd_code) && $herd_code == '35990571'): ?>
	<p class="important-message">This is sample herd data.  If you have signed up for the online report, please <?php echo anchor('auth/login','log in'); ?> or <?php echo anchor('auth/create_user','register'); ?> for your Internet account.</p>
<?php endif;
if (!empty ($filters)) echo $filters;
if (!empty ($download_links)) echo $download_links;
if (!empty ($herd_data)) echo $herd_data; 
if (!empty ($report_nav)) echo $report_nav; 
if(isset($charts) && is_array($charts)){
	foreach($charts as $c){
		echo $c;
	}
}
if(!empty($page_footer)) echo $page_footer;
