<script type="text/javascript">
window.onload=function(){
	UserVoice.push(['addTrigger', '#contact_us', {}]);
}
</script>
<?php

if(isset($page_header)){
	echo $page_header;
}
?>
<p>Sorry, the page <strong><?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?></strong> was not found.  Please use the navigation above to continue.</p>

<p>If you continue to have problems, please <a id="contact_us" href="mailto:support@myagsource.com">click here</a> and
		describe the problem.  Also, clicking on the camera icon on the form will send us a screenshot of the page, which is very helpful.</p>

<?php
if(isset($page_footer)){
	echo $page_footer;
}