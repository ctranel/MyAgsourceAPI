	</div> <!-- main_content -->
</div><!-- container -->
	
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-48524987-1', 'myagsource.com');
  ga('send', 'pageview');

</script>
<?php
	if(!empty($arr_foot_line) && is_array($arr_foot_line) !== FALSE):
		foreach ($arr_foot_line as $fl):
			echo $fl . "\r\n";
		endforeach;
	endif;

	if (!empty($arr_deferred_js) && is_array($arr_deferred_js) !== FALSE): ?>
		<script type="text/javascript">
			 // Add a script element as a child of the body
			 function downloadJSAtOnload() {
				<?php foreach($arr_deferred_js as $s): ?>
					var element = document.createElement("script");
					element.src = "<?php echo $s; ?>";
					document.body.appendChild(element);
				<?php endforeach; ?>
			 }
			 // Check for browser support of event handling capability
			 if (window.addEventListener) window.addEventListener("load", downloadJSAtOnload, false);
			 else if (window.attachEvent) window.attachEvent("onload", downloadJSAtOnload);
			 else window.onload = downloadJSAtOnload;
		</script>	<?php
	endif; ?>
	<!-- UserVoice JavaScript SDK (only needed once on a page) -->
	<script>(function(){var uv=document.createElement('script');uv.type='text/javascript';uv.async=true;uv.src='//widget.uservoice.com/i0OA99pO0xLAAKRentx5A.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(uv,s)})()</script>

	<!-- A tab to launch the Classic Widget -->
	<script>
		UserVoice = window.UserVoice || [];
		UserVoice.push(['showTab', 'classic_widget', {
		  mode: 'full',
		  primary_color: '#006d73',
		  link_color: '#006d73',
		  default_mode: 'support',
		  forum_id: 240012,
		  support_tab_name: 'Report a bug',
		  feedback_tab_name: 'Request a new feature',
		  tab_label: 'Features & Bugs',
		  tab_color: '#006d73',
		  tab_position: 'bottom-right',
		  tab_inverted: false
		}]);
	</script>
</body>
</html>
