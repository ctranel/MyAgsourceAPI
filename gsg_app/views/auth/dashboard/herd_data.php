<div id="herd-text">
	<?php if(!empty($herd_code)):?>
		<div><label>Herd Code</label><?php echo $herd_code; ?></div>
	<?php endif; ?>
	<?php if(!empty($farm_name)):?>
		<div><label>Name</label><?php echo $farm_name; ?></div>
	<?php endif; ?>
	<?php if(!empty($herd_owner)):?>
		<div><label>Owner</label><?php echo $herd_owner; ?></div>
	<?php endif; ?>
	<?php if(!empty($test_date)):?>
		<div><label>Test Date</label><?php echo $test_date; ?></div>
	<?php endif; ?>
	<?php if(!empty($assoc_manager) && ($this->session->userdata('active_group_id') == 1 || $this->session->userdata('active_group_id') == 6 || $this->session->userdata('active_group_id') == 7)):?>
		<div><label>Region/Association Manager</label><?php echo $assoc_manager; ?></div>
	<?php endif; ?>
	<?php if(!empty($num_animals)):?>
		<div><label>Animals Tested</label><?php echo $num_animals; ?></div>
	<?php endif; ?>
	<?php if(isset($inner_html)) echo $inner_html; ?>
</div>
