<?php
if (!empty($page_header)) echo $page_header;
if (!empty($page_heading)) echo heading($page_heading); ?>
<div id="d-sections" class="d-column accordion">
	<?php if(isset($widget['blocks'])): 
		foreach($widget['blocks'] as $w): ?>
			<div id="<?php echo str_replace(' ', '-', $w['title']); ?>" class="accordion-section widget">
				<h2><a href="<?php echo '#' . str_replace(' ', '-', $w['title']) ?>" title="Show <?php echo $w['title']; ?>"><?php echo $w['title']; ?></a></h2>
				<?php echo $w['content']; ?>
			</div>
		<?php endforeach;
	endif; ?>
</div>
<div class="d-space">&nbsp;</div>
<div id="d-info" class="d-column accordion">
	<?php if(isset($widget['info'])): 
		foreach($widget['info'] as $w): ?>
			<div id="<?php echo str_replace(' ', '-', $w['title']); ?>" class="accordion-section widget">
				<h2><a href="<?php echo '#' . str_replace(' ', '-', $w['title']) ?>" title="Show <?php echo $w['title']; ?>"><?php echo $w['title']; ?></a></h2>
				<?php echo $w['content']; ?>
			</div>
		<?php endforeach;
	endif; ?>
</div>
<div class="d-space">&nbsp;</div>
<div id="d-agsource" class="d-column accordion">
	<?php if(isset($widget['agsource'])): 
		foreach($widget['agsource'] as $w): ?>
			<div id="<?php echo str_replace(' ', '-', $w['title']); ?>" class="accordion-section widget">
				<h2><a href="<?php echo '#' . str_replace(' ', '-', $w['title']) ?>" title="Show <?php echo $w['title']; ?>"><?php echo $w['title']; ?></a></h2>
				<?php echo $w['content']; ?>
			</div>
		<?php endforeach;
	endif; ?>
</div>

<?php if(isset($widget['full_width'])): ?>
	<div id="d-full-width" class="accordion">
		<?php foreach($widget['full_width'] as $w): ?>
			<div class="accordion-section widget">
				<h2><a href="<?php echo '#' . str_replace(' ', '-', $w['title']) ?>" title="Show <?php echo $w['title']; ?>"><?php echo $w['title']; ?></a></h2>
				<?php echo $w['content']; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
<?php 
if(!empty($page_footer)) echo $page_footer;