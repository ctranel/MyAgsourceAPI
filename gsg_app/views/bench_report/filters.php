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
				<?php if(!empty($page_options)):
					echo form_fieldset('Block', array('id' => 'pages-fieldset'));
					foreach($page_options as $k=>$v):
						if(!empty($k)): ?>
								<span class="filter-checkbox"><?php echo form_checkbox('page_id[]', $k, in_array($k, $pages_selected) !== false, 'class = "page-checkbox"'); echo $v; ?></span>
						<?php endif;
					endforeach;
					echo form_fieldset_close();
				endif;
				break;
			case 'user': ?>
				<p class = "filter-group"><?php echo form_label('User&#39;s Region', 'user_region_id'); //echo form_input($user_region_id) ?><?php echo form_dropdown('user_region_id', $region_options, $region_selected, $user_region_id)?>
				</p>
				<?php break;
			case 'access_time': ?>
				<p class = "filter-group">
					<?php echo form_label('Access Date (MM-DD-YYYY)', 'access_time_dbfrom'); ?>		
					Between <?php echo form_input($access_time_dbfrom); ?>
					and <?php echo form_input($access_time_dbto); ?>
				</p>
				<?php break;
				endswitch; 
	endforeach;
endif; ?>
<div class="submit"><?php echo form_submit('filter_submit', 'Apply Filter');?>&nbsp;&nbsp;&nbsp;<?php echo form_button('reset_filter', 'Reset Filter', 'onclick="form_reset()"')?>
</div>
<?php echo form_close();?>
</div>

<?php if(isset($page_footer) !== false) echo $page_footer; ?>