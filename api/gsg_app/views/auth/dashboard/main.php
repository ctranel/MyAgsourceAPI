<?php
if (!empty($page_header)) echo $page_header;
if (!empty($page_heading)) echo heading($page_heading);
if(isset($herd_code) && $herd_code == $this->config->item('default_herd')): ?>
	<p class="important-message">This is sample herd data, please <?php echo anchor('auth/login', 'login'); ?> or <?php echo anchor('auth/create_user', 'register'); ?> to see your herd's data.</p>
<?php endif; ?>
<?php if(isset($widget['full_width_top'])): ?>
	<div id="d-full-width" class="col-sm-12">
		<?php foreach($widget['full_width_top'] as $w): ?>
			<div class="box">
				<h2><?php echo $w['title']; ?></h2>
				<?php if(isset($w['subtitle'])): ?>
					<h3><?php echo $w['subtitle']; ?></h3>
				<?php endif; ?>
				<div class="widget-content"><?php echo $w['content']; ?></div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
<div id="d-herd" class="col-sm-3">
	<?php if(isset($widget['herd'])): 
		foreach($widget['herd'] as $w): ?>
			<div id="<?php echo str_replace(' ', '-', $w['title']); ?>" class="box">
				<h2><?php echo $w['title']; ?></h2>
				<?php if(isset($w['subtitle'])): ?>
					<h3><?php echo $w['subtitle']; ?></h3>
				<?php endif; ?>
				<div class="widget-content"><?php echo $w['content']; ?></div>
			</div>
		<?php endforeach;
	endif; ?>
</div>
<div id="d-feature" class="col-sm-5">
	<?php if(isset($widget['feature'])): 
		foreach($widget['feature'] as $w): ?>
			<div id="<?php echo str_replace(' ', '-', $w['title']); ?>" class="box">
				<h2><?php echo $w['title']; ?></h2>
				<?php if(isset($w['subtitle'])): ?>
					<h3><?php echo $w['subtitle']; ?></h3>
				<?php endif; ?>
				<div class="widget-content"><?php echo $w['content']; ?></div>
			</div>
		<?php endforeach;
	endif; ?>
</div>
<div id="d-info" class="col-sm-4">
	<?php if(isset($widget['info'])): 
		foreach($widget['info'] as $w): ?>
			<div id="<?php echo str_replace(' ', '-', $w['title']); ?>" class="box">
				<h2><?php echo $w['title']; ?></h2>
				<?php if(isset($w['subtitle'])): ?>
					<h3><?php echo $w['subtitle']; ?></h3>
				<?php endif; ?>
				<div class="widget-content"><?php echo $w['content']; ?></div>
			</div>
		<?php endforeach;
	endif; ?>
</div>

<?php if(isset($widget['full_width_bot'])): ?>
	<div id="d-full-width" class="col-sm-12">
		<?php foreach($widget['full_width_bot'] as $w): ?>
			<div class="box">
				<h2><?php echo $w['title']; ?></h2>
				<?php if(isset($w['subtitle'])): ?>
					<h3><?php echo $w['subtitle']; ?></h3>
				<?php endif; ?>
				<div class="widget-content"><?php echo $w['content']; ?></div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
<?php 
if(!empty($page_footer)) echo $page_footer;