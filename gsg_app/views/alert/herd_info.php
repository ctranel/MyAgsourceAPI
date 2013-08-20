<div id="herd-info">
	<div id="herd-text">
		<?php if(!empty($herd_data->herd_code)):?>
			<h3>Herd <?php echo $herd_data->herd_code; ?></h3>
		<?php endif; ?>
		<?php if(!empty($herd_data->farm_name)):?>
			<div><label>Name</label><?php echo $herd_data->farm_name; ?></div>
		<?php endif; ?>
		<?php if(!empty($herd_data->herd_owner)):?>
			<div><label>Owner</label><?php echo $herd_data->herd_owner; ?></div>
		<?php endif; ?>
		<?php if(!empty($herd_data->test_date)):?>
			<div><label>Test Date</label><?php echo $herd_data->test_date; ?></div>
		<?php endif; ?>
		<?php if(!empty($assoc_manager) && ($this->session->userdata('active_group_id') == 1 || $this->session->userdata('active_group_id') == 6 || $this->session->userdata('active_group_id') == 7)):?>
			<div><label>Region/Association Manager</label><?php echo $assoc_manager; ?></div>
		<?php endif; ?>
		<?php if(!empty($herd_data->weighted_scc_avg)):?>
			<div><label>Weighted Herd SCC Avg</label><?php echo $herd_data->weighted_scc_avg; ?></div>
		<?php endif; ?>
		<?php if($this->session->userdata('active_group_id') != 2):?>
			<div><label>Animals in this Report</label><?php echo $num_results; ?></div>
		<?php endif; ?>
		<div><?php echo anchor('alert/display/pdf', "View Printable PDF", array('id'=>'pdf')); ?></div>
		
	</div>
	<p>Alert provides you information about 
            your high SCC cows immediately after your test day information is 
            processed. If you would like ALL of your production information with 
            the same speed, consider using AgSourceDM.com. Easy to understand 
            graphs, unlimited report generating capability and the ability to 
            carry all your records in a PDA with DairyHand.com. To learn more, 
                <a href="http://agsource.crinet.com/page473/AgSourceDMAndDairyHand">click here</a>.</p>
</div>