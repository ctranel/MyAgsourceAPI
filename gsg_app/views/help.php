<?php

	echo $page_header;

	$filepath = APPPATH.'helpdocs'.FS_SEP.$product_name.'.html';

	if (file_exists($filepath)) {
		ob_start();
		include $filepath;
		echo ob_get_clean();
	}
	