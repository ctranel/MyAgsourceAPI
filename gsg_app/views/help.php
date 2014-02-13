<?php

	echo $page_header;

	$filepath = APPPATH.'helpdocs'.FS_SEP.$product_name.'.html';

	echo $filepath;
	
	if (file_exists($filepath)) {
		echo $filepath;
		ob_start();
		include $filepath;
		echo ob_get_clean();
	}
	