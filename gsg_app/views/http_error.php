<script type="text/javascript">
window.onload=function(){
	UserVoice.push(['addTrigger', '#contact_us', {}]);
}
</script>
<?php

if(isset($page_header)) echo $page_header;
echo 'The address <strong>http://www.myagsource.com'.$_SERVER['REQUEST_URI'].'</strong> is invalid.<br /><br />';

echo 'If you arrived here from a link on our site, please <a id="contact_us" href="mailto:support@myagsource.com">contact us</a>, 
		describe where the link was, and check the box "Include a screenshot of this page" on the feedback window.<br /><br />';

echo '-OR-<br /><br />';

echo 'Email us at <a href="mailto:support@myagsource.com">support@myagsource.com</a><br /><br />.';
if(isset($page_footer)) echo $page_footer;