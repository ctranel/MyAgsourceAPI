<?php
if (!empty($page_header)) echo $page_header;
if (!empty($page_heading)) echo heading($page_heading);
if (!empty($report_nav)) echo $report_nav; ?>
<div id="d-herd" class="col-sm-3">
	<?php if(isset($widget['herd'])): 
		foreach($widget['herd'] as $w): ?>
			<div id="<?php echo str_replace(' ', '-', $w['title']); ?>" class="box">
				<h2><?php echo $w['title']; ?></h2>
				<?php echo $w['content']; ?>
			</div>
		<?php endforeach;
	endif; ?>
</div>
<div id="d-feature" class="col-sm-6">
	<?php if(isset($widget['feature'])): 
		foreach($widget['feature'] as $w): ?>
			<div id="<?php echo str_replace(' ', '-', $w['title']); ?>" class="box">
				<h2><?php echo $w['title']; ?></h2>
				<?php echo $w['content']; ?>
			</div>
		<?php endforeach;
	endif; ?>
</div>
<div id="d-info" class="col-sm-3">
	<?php if(isset($widget['info'])): 
		foreach($widget['info'] as $w): ?>
			<div id="<?php echo str_replace(' ', '-', $w['title']); ?>" class="box">
				<h2><?php echo $w['title']; ?></h2>
				<?php echo $w['content']; ?>
			</div>
		<?php endforeach;
	endif; ?>
</div>

<?php if(isset($widget['full_width'])): ?>
	<div id="d-full-width" class="col-sm-12">
		<?php foreach($widget['full_width'] as $w): ?>
			<div class="box">
				<h2><?php echo $w['title']; ?></h2>
				<?php echo $w['content']; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
<?php 
if(!empty($page_footer)) echo $page_footer;