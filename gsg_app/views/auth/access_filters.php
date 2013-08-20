<?php if(isset($page_header) !== false) echo $page_header; ?>
<?php $this->load->helper('html_helper'); ?>
<div id="filters" style="max-width:80%;">
<!--[if IE 8]>
<style type="text/css">
	fieldset legend {position: relative; top: -1em;}
</style>
<![endif]-->

<?php echo validation_errors(); ?>
<?php if(isset($form_url) === false) $form_url = current_url(); ?>
<?php //$link_url = str_replace('display', 'filter', $form_url)?>
<div class="handle"><a id="set_filters" class="handle">Set Filters</a></div>
<?php //echo anchor("#","Apply Filters", 'class="handle"'); ?>
<?php echo form_open($form_url, array('name'=>'report_search', 'id'=>'report_search')); ?>
<?php if(is_array($arr_filters)):
	 foreach($arr_filters as $f):
		switch($f): 
			case 'sections':
				if(!empty($section_options)):
					echo form_fieldset('Sections', array('id' => 'sections-fieldset'));
					foreach($section_options as $k=>$v):
						if(!empty($k)): ?>
								<span class="filter-checkbox"><?php echo form_checkbox('section_id[]', $k, in_array($k, $section_selected) !== false, 'class = "section-checkbox"'); echo $v; ?></span>
						<?php endif;
					endforeach;
					echo form_fieldset_close();
				endif;
				break;
			case 'pages':
				if(!empty($page_options)):
					echo form_fieldset('Pages', array('id' => 'pages-fieldset'));
					foreach($page_options as $a=>$arr):
						//start section group.  if in_array($section_selected) show, else hide
						$style = in_array ($a, $section_selected)?'display:inline':'display:none';
						echo form_fieldset('', array('id' => $a, 'class' => 'pages', 'style' => $style));
						foreach($arr as $k=>$v): 
							if(!empty($k)): ?>
								<span class="filter-checkbox"><?php echo form_checkbox('page_id[]', $k, in_array($k, $page_selected) !== false, 'class = "page-checkbox"'); echo $v; ?></span>
							<?php endif;
						endforeach;
						echo form_fieldset_close();
					endforeach; 
					echo form_fieldset_close(); ?>
				<?php endif; ?>
				<?php break;
			case 'groups': ?>
				<?php if(!empty($group_options)): ?>
						<?php $top=count($group_options);
						echo form_fieldset('User Groups', array('id' => 'user-group'));
						foreach($group_options as $k=>$v):
							if(!empty($k)): ?>
									<span class="filter-checkbox"><?php echo form_checkbox('group_id[]', $k, in_array($k, $group_selected) !== false); echo $v; ?></span>
							<?php endif;
						endforeach;
						echo form_fieldset_close(); ?>
				<?php endif; ?>
				<?php break;
			case 'format': ?>
				<?php if(!empty($format_options)): ?>
						<?php $top=count($format_options);
						echo form_fieldset('Format', array('id' => 'format'));
						foreach($format_options as $k=>$v):
							if(!empty($k)): ?>
									<span class="filter-checkbox"><?php echo form_checkbox('format[]', $k, in_array($k, $format_selected) !== false); echo $v; ?></span>
							<?php endif;
						endforeach;
						echo form_fieldset_close(); ?>
				<?php endif; ?>
				<?php break;
			case 'herd_code': ?>
				<p><?php echo form_label('Herd Code (any part)', 'herd_code'); echo form_input($herd_code) ?></p>
				<?php break;
			case 'user_region_id': ?>
				<p><?php echo form_label('User&#39;s Region', 'user_region_id'); //echo form_input($user_region_id) ?><?php echo form_dropdown('user_region_id', $region_options, $region_selected, $user_region_id)?>
				</p>
				<?php break;
			case 'user_tech_num': ?>
				<p><?php echo form_label('User&#39;s Technician Number (any part)', 'user_tech_num'); echo form_input($user_tech_num) ?></p>
				<?php break;
			case 'access_time': ?>
				<p>
					<?php echo form_label('Access Time (MM-DD-YYYY)', 'access_time_dbfrom'); ?>		
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