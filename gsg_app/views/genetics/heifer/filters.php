<?php if(isset($page_header) !== false) echo $page_header; ?>
<?php $this->load->helper('html_helper'); ?>
<div id="filters">
<?php echo validation_errors(); ?>
<?php if(isset($form_url) === false) $form_url = current_url(); ?>
<?php //$link_url = str_replace('display', 'filter', $form_url)?>
<div class="handle"><a id="set_filters" class="handle">Set Filters</a></div>
<?php //echo anchor("#","Apply Filters", 'class="handle"'); ?>
<?php echo form_open($form_url, array('name'=>'report-filter', 'id'=>'report-filter')); ?>
<?php if(is_array($arr_filters)):
	 foreach($arr_filters as $f):
		switch($f): 
			case 'pstring': ?>
				<?php if(!empty($arr_pstring) && count($arr_pstring) > 1):
					echo form_fieldset('PString', array('id' => 'pages-fieldset'));
						$top=count($arr_pstring);
						for( $c=0; $c < $top; $c++): ?>
							<span class="pstring-filter-item filter-checkbox">
								<?php //echo form_radio('pstring', $arr_pstring[$c]['pstring'], $filter_selected['pstring'] == $arr_pstring[$c]['pstring']);
								echo form_checkbox('pstring[]', $arr_pstring[$c]['pstring'], in_array($arr_pstring[$c]['pstring'], $filter_selected['pstring']) !== FALSE);
								echo $arr_pstring[$c]['publication_name']; ?>
							</span>
						<?php endfor;
					echo form_fieldset_close(); ?>
				<?php endif; ?>
				<?php break;
			case 'decision_guide_qtile_num': ?>
				<p class = "filter-group">
					<?php echo form_fieldset('Quartile', array('id' => 'quartile-fieldset')); ?>
					<span class="filter-checkbox">
						<?php echo form_checkbox(array('name'=>'decision_guide_qtile_num[]', 'id'=>'decision_guide_qtile_num0'), '0', in_array('0', $filter_selected['decision_guide_qtile_num']) !== false); ?>
						None&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_checkbox(array('name'=>'decision_guide_qtile_num[]', 'id'=>'decision_guide_qtile_num1'), '1', in_array('1', $filter_selected['decision_guide_qtile_num']) !== false); ?>
						1st&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_checkbox(array('name'=>'decision_guide_qtile_num[]', 'id'=>'decision_guide_qtile_num2'), '2', in_array('2', $filter_selected['decision_guide_qtile_num']) !== false); ?>
						2nd&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_checkbox(array('name'=>'decision_guide_qtile_num[]', 'id'=>'decision_guide_qtile_num3'), '3', in_array('3', $filter_selected['decision_guide_qtile_num']) !== false); ?>
						3rd&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_checkbox(array('name'=>'decision_guide_qtile_num[]', 'id'=>'decision_guide_qtile_num4'), '4', in_array('4', $filter_selected['decision_guide_qtile_num']) !== false); ?>
						4th&nbsp;&nbsp;
					</span>
				</p>
				<?php echo form_fieldset_close();
				break;
			case 'lact_num': ?>
					<?php echo form_fieldset('Lactation #', array('id' => 'lact-num-fieldset')); ?>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'lact_num[]', 'id'=>'lact_num1'), '1', in_array('1', $filter_selected['lact_num']) !== false); ?>
					1&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'lact_num[]', 'id'=>'lact_num2'), '2', in_array('2', $filter_selected['lact_num']) !== false); ?>
					2&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'lact_num[]', 'id'=>'lact_num3'), '>3', in_array('>3', $filter_selected['lact_num']) !== false); ?>
					3+&nbsp;&nbsp;</span>
				<?php echo form_fieldset_close();
				break;
			case 'dim': ?>
				<p class = "filter-group">
					<?php echo form_label('DIM Range', 'curr_lact_dim_dbfrom'); ?>
					Between <?php echo form_input(array('name'=>'curr_lact_dim_dbfrom', 'value'=>$filter_selected['curr_lact_dim_dbfrom'], 'size'=>'3', 'maxlength'=>'3', 'id'=>'curr_lact_dim_dbfrom')); ?>
					and <?php echo form_input(array('name'=>'curr_lact_dim_dbto', 'value'=>$filter_selected['curr_lact_dim_dbto'], 'size'=>'3', 'maxlength'=>'3', 'id'=>'curr_lact_dim_dbto')); ?>
				</p>
				<?php break;
			case 'age_months': ?>
				<p class = "filter-group">
					<?php echo form_label('Age Range (months)', 'age_months_dbfrom'); ?>		
					Between <?php echo form_input(array('name'=>'age_months_dbfrom', 'value'=>$filter_selected['age_months_dbfrom'], 'size'=>'4', 'maxlength'=>'4', 'id'=>'age_months_dbfrom')); ?>
					and <?php echo form_input(array('name'=>'age_months_dbto', 'value'=>$filter_selected['age_months_dbto'], 'size'=>'4', 'maxlength'=>'4', 'id'=>'age_months_dbto')); ?>
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