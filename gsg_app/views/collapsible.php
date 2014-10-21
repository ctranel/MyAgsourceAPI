<?php if(isset($page_header) !== false) echo $page_header; ?>
<div class="expand-group" id="<?php echo $id; ?>">
	<div class="handle"><a><?php echo $title; ?></a></div>
	<div class="expand">
		<?php echo $content?>
	</div>
</div>

<?php if(isset($page_footer) !== false) echo $page_footer;
