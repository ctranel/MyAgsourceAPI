<?php if(isset($page_header) !== false) echo $page_header; ?>
<?php $this->load->helper('html_helper'); ?>
<div id="filters" style="max-width:80%;">
<?php echo validation_errors(); ?>
<?php if(isset($form_url) === false) $form_url = current_url(); ?>
<?php //$link_url = str_replace('display', 'filter', $form_url)?>
<div class="handle"><a id="set-filters" class="handle">Set Filters</a></div>
<?php //echo anchor("#","Apply Filters", 'class="handle"'); ?>
<?php echo form_open($form_url, array('name'=>'filter-form', 'id'=>'filter-form')); ?>
<?php if(is_array($arr_filters)):
	 foreach($arr_filters as $f):
		switch($f): 
			case 'pages': ?>
				<?php if(!empty($page_options)): ?>
					<p>
						<?php echo form_label('Events', 'page_id');
						foreach($page_options as $k=>$v):
							if(!empty($k)): ?>
									<span class="filter-checkbox"><?php echo form_checkbox('page_id[]', $k, in_array($k, $pages_selected) !== false); echo $v; ?></span>
							<?php endif;
						endforeach; ?>
					</p>
				<?php endif; ?>
				<?php break;
			case 'groups': ?>
				<?php if(!empty($group_options)): ?>
					<p>
<?php //print_r($pages_selected); ?>
						<?php echo form_label('User&#39;s Groups', 'group_id'); ?>
						<?php //echo form_dropdown('group_id[]', $group_options, $group_selected, $group_id)?>
						<?php $top=count($group_options);
						foreach($group_options as $k=>$v):
							if(!empty($k)): ?>
									<span class="filter-checkbox"><?php echo form_checkbox('group_id[]', $k, in_array($k, $group_selected) !== false); echo $v; ?></span>
							<?php endif;
						endforeach; ?>
					</p>
				<?php endif; ?>
				<?php break;
			case 'herd_code': ?>
				<p><?php echo form_label('Herd Code (any part)', 'herd_code'); echo form_input($herd_code) ?></p>
				<?php break;
			case 'user_association_num': ?>
				<p><?php echo form_label('User&#39;s Association', 'user_association_num'); //echo form_input($user_association_num) ?><?php echo form_dropdown('user_association_num', $association_options, $association_selected, $user_association_num)?>
				</p>
				<?php break;
			case 'user_tech_num': ?>
				<p><?php echo form_label('User&#39;s Technician Number (any part)', 'user_tech_num'); echo form_input($user_tech_num) ?></p>
				<?php break;
			case 'access_time': ?>
				<p>
					<?php echo form_label('Access Data (MM-DD-YYYY)', 'access_time_dbfrom'); ?>		
					Between <?php echo form_input($access_time_dbfrom); ?>
					and <?php echo form_input($access_time_dbto); ?>
				</p>
				<?php break;
				endswitch; 
	endforeach;
endif; ?>
<div class="submit"><?php echo form_submit('filter_submit', 'Apply Filter', 'class="button"');?>&nbsp;&nbsp;&nbsp;<?php echo form_button('reset_filter', 'Reset Filter', 'onclick="form_reset()"')?>
</div>
<?php echo form_close();?>
</div>

<?php if(isset($page_footer) !== false) echo $page_footer; ?>