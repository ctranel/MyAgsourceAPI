<?php 
if (is_array($data) && !empty($data)):
?>
<?php //$title = (empty($_GET['feed'])) ? 'Dairy Articles' : 'Dairy Articles: ' . $feed->get_title(); ?>
<div class="widget-content">
	<div id="dairy-feed">
		<div id="sp_results">
			<?php foreach($data as $item): ?>
				<div class="chunk">
					<h3><a href="<?php echo $item['link']; ?>" target="_blank"><?php echo $item['title']; ?></a></h3>
					<p class = "date"><?php echo $item['pubDate']; ?> - agweb.com</p>
					<?php echo $item['description']; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php endif; ?>