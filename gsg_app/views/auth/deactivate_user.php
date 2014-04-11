<?php if(isset($page_header) !== false) echo $page_header; ?>
<div class='mainInfo'>

	<?php if(isset($page_heading) !== false) echo heading($page_heading); ?>
	<p>Are you sure you want to deactivate '<?php echo $user->first_name . ' ' . $user->last_name; ?>'</p>
	
    <?php echo form_open("auth/deactivate/".$user->id);?>
    	
      <p>
      	<label for="confirm">Yes:</label>
		<input type="radio" name="confirm" value="yes" checked="checked" />
      	<label for="confirm">No:</label>
		<input type="radio" name="confirm" value="no" />
      </p>
      
      <?php echo form_hidden($csrf); ?>
      <?php echo form_hidden(array('id'=>$user->id)); ?>
      
      <p><?php echo form_submit('submit', 'Submit', 'class="button"');?></p>

    <?php echo form_close();?>

</div>
<?php if(isset($page_footer) !== false) echo $page_footer;