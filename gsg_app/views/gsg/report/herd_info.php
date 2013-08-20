<div id="herd-info">
	<table id="quartile-herd">
		<tr>
			<th colspan=2 class="heading" id="qtile-table"><?php echo $cow_heifer; ?></th>
		</tr>
		<tr>
			<th class="subheading">Quartile Averages</th>
			<th class="subheading"><?php echo $nm_column_header; ?></th>
		</tr>
		<tr>
			<th class="subcat-heading">Quartile 1 Average</th>
			<td class="qtile1"><?php echo $obj_herd_data->{$quartile_fields[0]}; ?></td>
		</tr>
		<tr>
			<th class="subcat-heading">Quartile 2 Average</th>
			<td class="qtile2"><?php echo $obj_herd_data->{$quartile_fields[1]}; ?></td>
		</tr>
		<tr>
			<th class="subcat-heading">Quartile 3 Average</th>
			<td class="qtile3"><?php echo $obj_herd_data->{$quartile_fields[2]}; ?></td>
		</tr>
		<tr>
			<th class="subcat-heading">Quartile 4 Average</th>
			<td class="qtile4"><?php echo $obj_herd_data->{$quartile_fields[3]}; ?></td>
		</tr>
	</table>
	<div id="herd-text">
		<?php if(!empty($obj_herd_data->herd_code)):?>
			<div><label>Herd Code</label><?php echo $obj_herd_data->herd_code; ?></div>
		<?php endif; ?>
		<?php if(!empty($obj_herd_data->farm_name)):?>
			<div><label>Name</label><?php echo $obj_herd_data->farm_name; ?></div>
		<?php endif; ?>
		<?php if(!empty($obj_herd_data->herd_owner)):?>
			<div><label>Owner</label><?php echo $obj_herd_data->herd_owner; ?></div>
		<?php endif; ?>
		<?php if(!empty($obj_herd_data->test_date)):?>
			<div><label>Test Date</label><?php echo $obj_herd_data->test_date; ?></div>
		<?php endif; ?>
		<?php if(!empty($assoc_manager) && ($this->session->userdata('active_group_id') == 1 || $this->session->userdata('active_group_id') == 6 || $this->session->userdata('active_group_id') == 7)):?>
			<div><label>Region/Association Manager</label><?php echo $assoc_manager; ?></div>
		<?php endif; ?>
		<?php if($this->session->userdata('active_group_id') != 2):?>
			<div><label>Online GSG Subscription</label><?php echo isset($access_level)?$access_level:'none'; ?></div>
			<div><label>Animals in this Report</label><?php echo $num_results; ?></div>
		<?php endif; ?>
	</div>
</div>