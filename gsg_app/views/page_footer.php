	</div> <!-- main_content -->
</div><!-- container -->
	
	<script type="text/javascript">
	
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-5296967-20']);
	  _gaq.push(['_trackPageview']);
	
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	
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
	
</body>
</html>
