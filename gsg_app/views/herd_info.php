	<div id="herd-text">
		<div class="general-herd">
			<?php if(!empty($herd_code)):?>
				<div><label>Herd Code</label> <?php echo $herd_code; ?></div>
			<?php endif; ?>
			<?php if(!empty($farm_name)):?>
				<div><label>Name</label> <?php echo $farm_name; ?></div>
			<?php endif; ?>
			<?php if(!empty($herd_owner)):?>
				<div><label>Owner</label> <?php echo $herd_owner; ?></div>
			<?php endif; ?>
			<?php if(!empty($association_num)):?>
				<div><label>Association</label> <?php echo $association_num; ?></div>
			<?php endif; ?>
			<?php if(!empty($supervisor_num)):?>
				<div><label>Tech Num</label> <?php echo $supervisor_num; ?></div>
			<?php endif; ?>
			<?php if(!empty($test_date)):?>
				<div><label>Test Date</label> <?php echo $test_date; ?></div>
			<?php endif; ?>
		</div>
		<?php if(!empty($assoc_manager) && ($this->session->userdata('active_group_id') == 1 || $this->session->userdata('active_group_id') == 6 || $this->session->userdata('active_group_id') == 7)):?>
			<div class="general-herd"><label>Region/Association Manager</label> <?php echo $assoc_manager; ?></div>
		<?php endif; ?>
	</div>
