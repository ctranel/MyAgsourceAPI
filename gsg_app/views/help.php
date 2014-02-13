<?php

	echo $page_header;

	$filepath = APPPATH.'helpdocs/'.$product_name.'.html';

	if (is_file($filepath)) {
		ob_start();
		include $filepath;
		echo ob_get_clean();
	}
	