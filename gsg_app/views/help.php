<?php if(isset($page_header) !== FALSE) echo $page_header;
/*	$filepath = APPPATH.'helpdocs'.FS_SEP.strtolower($product_name).'.html';

	if (file_exists($filepath)) {
		ob_start();
		include $filepath;
		echo ob_get_clean();
	} */
?>
<div class='mainInfo'>
	<h1 style="overflow: wrap;">Support</h1>
	<h2 style="overflow: wrap;">How to reach us</h2>
		<ul style="overflow: wrap;">
			<li style="overflow: wrap;">Uservoice: <a href="http://myagsource.uservoice.com">myagsource.uservoice.com</a><br />
				Please visit our uservoice site and vote on features others have
				proposed there. You can also post about a bug you believe may be
				affecting more than yourself or about a feature you'd like to see
				added to the site. Just visit uservoice via the link above and click
				on &quot;Give Feedback&quot;. We'll be glad to hear what our members think of
				this exciting new venture!
			</li>
			<li style="overflow: wrap;">Email: <a href="mailto:support@myagsource.com">support@myagsource.com</a><br />
				If you would like to reach us regarding something specific, you can
				always email us.
			</li>
		</ul>
<!-- 
	<h2>A Message for our Beta Testers</h2>
		<ul>
			<li>
				<p>We are doing our best to address every issue in a timely manner.
				However, we are undoubtly going to miss at least one thing as that
				is the nature of the programming business.</p>
				<p>This is where you come in. Every new program needs a group of
				potential users to try things in ways the designers couldn't
				possibly have predicted and give feedback. This is why beta-testers
				are an essential and greatly appreciated part of every project's
				community.</p>
				<p>Thank you for helping us make www.myagsource.com fulfill your
				needs and the needs of our cooperative as a whole.</p>
			</li>
		</ul>

	<h2>Known Issues</h2>
		<ul>
			<li>Google Chrome
				<ul>
					<li>Version 32<br /> Chrome 32 contains a bug that affects our
						scatter plot graphs. Google is aware of the issue and has a fix in
						the pipeline that should be released with Chrome 34. Expected
						release date of Chrome 34 is early 2nd quarter.
					</li>
				</ul>
			</li>
			<li>Internet Explorer
				<ul>
					<li>Version 7 and lower<br /> IE 7 and lower are incompatible with
						a code package essential to the site's ability to display cows
						when clicking on their identification links. If you wish to be
						able to use this feature on a windows machine running Windows XP,
						consider using a different browser when visiting
						www.myagsource.com.
					</li>
				</ul>
			</li>
		</ul> <br />
	<br />
	<h2>Recommended Browser</h2>
		<ul>
			<li>While we are constantly addressing cross-browser compatibility
				issues and recognize our members have preferred browsers across the
				spectrum, we cannot promise 100% compatibility at all times with all
				browsers. As such, we can only inform you of compatibility issues as
				they arise and if/when they'll be addressed.<br />
			<br /> For the duration of the beta, we encourage you to use your
				favorite browser as you normally would and please inform us of any
				issues you encounter with the website either via the uservoice or
				email links provided above.<br />
			<br /> In the meantime, if you would like to view the website as it
				is intended to look for comparison, the browser most compatibile
				with the site as of 2/14/2014 is <strong>Firefox</strong>.<br />
			<br /> We will never require you to change browsers if it is within
				our power to fix an issue. However, some things are beyond our
				control and those things will be listed above in our Known Issues
				section. The browser with the least known issues will be recommended
				here.<br />
			<br /> Thanks for your understanding.
			</li>
		</ul>
 -->
</div>
<?php if(isset($page_footer)): 
	echo $page_footer;
endif;?>
