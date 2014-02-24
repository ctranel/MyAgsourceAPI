<?php if(isset($page_header) !== false) echo $page_header; ?>
<?php $this->load->helper('html_helper'); ?>
<div id="filters">
<?php echo validation_errors(); ?>
<?php if(isset($form_url) === false) $form_url = current_url(); ?>
<?php //$link_url = str_replace('display', 'filter', $form_url)?>
<div class="handle"><a id="set-filters" class="handle">Set Filters </a></div>
<?php //echo anchor("#","Apply Filters", 'class="handle"'); ?>
<?php echo form_open($form_url, array('name'=>'filter-form', 'id'=>'filter-form'));?>
<p></p>
<?php if(is_array($arr_filters)):

	 
	 foreach($arr_filters as $f):
	 
		switch($f): 
			case 'pstring':
				if(!empty($arr_pstring) && count($arr_pstring) > 1):
					echo form_fieldset('PString', array('id' => 'pages-fieldset'));
						$top=count($arr_pstring);
						for( $c=0; $c < $top; $c++): ?>
							<span class="pstring-filter-item filter-checkbox">
								<?php echo form_checkbox('pstring[]', $arr_pstring[$c]['pstring'], in_array($arr_pstring[$c]['pstring'], $filter_selected['pstring']) !== FALSE);
								echo $arr_pstring[$c]['publication_name']; ?>
							</span>
						<?php endfor;
					echo form_fieldset_close();
				endif;
				break;
			case 'tstring':
				$arr_tstring = $this->session->userdata('arr_tstring');
				if(!empty($arr_tstring) && count($arr_tstring) > 1):
					echo form_fieldset('Pen', array('id' => 'pages-fieldset'));
						$top=count($arr_tstring);
						for( $c=0; $c < $top; $c++): ?>
							<span class="tstring-filter-item filter-checkbox">
								<?php echo form_checkbox('tstring[]', $arr_tstring[$c]['tstring'], in_array($arr_tstring[$c]['tstring'], $filter_selected['tstring']) !== FALSE);
								echo $arr_tstring[$c]['tstring']; ?>
							</span>
						<?php endfor;
					echo form_fieldset_close();
				endif;
				break;
			case 'decision_guide_qtile_num': ?>
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
				<?php echo form_fieldset_close();
				break;
			case 'lact_num': ?>
				<?php echo form_fieldset('Lactation #', array('id' => 'lact-num-fieldset')); ?>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'lact_num[]', 'id'=>'lact_num1'), '1', in_array('1', $filter_selected['lact_num']) !== false); ?>
					1&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'lact_num[]', 'id'=>'lact_num2'), '2', in_array('2', $filter_selected['lact_num']) !== false); ?>
					2&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php $arr_intersect = array_intersect(array(3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20), $filter_selected['lact_num']); echo form_checkbox(array('name'=>'lact_num[]', 'id'=>'lact_num3'), '3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20', !empty($arr_intersect)); ?>
					3+&nbsp;&nbsp;</span>
				<?php echo form_fieldset_close();
				break;
			case 'curr_lact_num': ?>
				<?php echo form_fieldset('Lactation #', array('id' => 'lact-num-fieldset')); ?>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'curr_lact_num[]', 'id'=>'curr_lact_num1'), '1', in_array('1', $filter_selected['curr_lact_num']) !== false); ?>
					1&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'curr_lact_num[]', 'id'=>'curr_lact_num2'), '2', in_array('2', $filter_selected['curr_lact_num']) !== false); ?>
					2&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php $arr_intersect = array_intersect(array(3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20), $filter_selected['curr_lact_num']); echo form_checkbox(array('name'=>'curr_lact_num[]', 'id'=>'curr_lact_num3'), '3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20', !empty($arr_intersect)); ?>
					3+&nbsp;&nbsp;</span>
				<?php echo form_fieldset_close();
				break;
			case 'dam_lact_num': ?>
				<?php echo form_fieldset('Dam Lactation #', array('id' => 'dam_lact_num-fieldset')); ?>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'dam_lact_num[]', 'id'=>'dam_lact_num0'), '0', in_array('0', $filter_selected['dam_lact_num']) !== false); ?>
					0&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'dam_lact_num[]', 'id'=>'dam_lact_num1'), '1', in_array('1', $filter_selected['dam_lact_num']) !== false); ?>
					1&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'dam_lact_num[]', 'id'=>'dam_lact_num2'), '2', in_array('2', $filter_selected['dam_lact_num']) !== false); ?>
					2&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php $arr_intersect = array_intersect(array(3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20), $filter_selected['dam_lact_num']); echo form_checkbox(array('name'=>'dam_lact_num[]', 'id'=>'dam_lact_num3'), '3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20', !empty($arr_intersect)); ?>
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
			case 'curr_ltd_dim':
				echo form_fieldset('DIM Range', array('id' => 'curr_ltd_dim-fieldset')); ?>
					Between <?php echo form_input(array('name'=>'curr_ltd_dim_dbfrom', 'value'=>$filter_selected['curr_ltd_dim_dbfrom'], 'size'=>'3', 'maxlength'=>'3', 'id'=>'curr_ltd_dim_dbfrom')); ?>
					and <?php echo form_input(array('name'=>'curr_ltd_dim_dbto', 'value'=>$filter_selected['curr_ltd_dim_dbto'], 'size'=>'3', 'maxlength'=>'3', 'id'=>'curr_ltd_dim_dbto')); ?>
				<?php echo form_fieldset_close();
				break;
			case 'curr_lact_dim':
				echo form_fieldset('DIM Range', array('id' => 'dam_lact_num-fieldset')); ?>
					Between <?php echo form_input(array('name'=>'curr_lact_dim_dbfrom', 'value'=>$filter_selected['curr_lact_dim_dbfrom'], 'size'=>'3', 'maxlength'=>'3', 'id'=>'curr_lact_dim_dbfrom')); ?>
					and <?php echo form_input(array('name'=>'curr_lact_dim_dbto', 'value'=>$filter_selected['curr_lact_dim_dbto'], 'size'=>'3', 'maxlength'=>'3', 'id'=>'curr_lact_dim_dbto')); ?>
				<?php echo form_fieldset_close();
				break;
			case 'age_months':
				echo form_fieldset('Age Range (months)', array('id' => 'dam_lact_num-fieldset')); ?>
					<?php //echo form_label('Age Range (months)', 'age_months_dbfrom'); ?>		
					Between <?php echo form_input(array('name'=>'age_months_dbfrom', 'value'=>$filter_selected['age_months_dbfrom'], 'size'=>'4', 'maxlength'=>'4', 'id'=>'age_months_dbfrom')); ?>
					and <?php echo form_input(array('name'=>'age_months_dbto', 'value'=>$filter_selected['age_months_dbto'], 'size'=>'4', 'maxlength'=>'4', 'id'=>'age_months_dbto')); ?>
				<?php echo form_fieldset_close();
				break;
			case 'scc_cnt_1':
				echo form_fieldset('Current SCC Range', array('id' => 'scc_cnt_1-fieldset')); ?>
					Between <?php echo form_input(array('name'=>'scc_cnt_1_dbfrom', 'value'=>$filter_selected['scc_cnt_1_dbfrom'], 'size'=>'5', 'maxlength'=>'5', 'id'=>'scc_cnt_1_dbfrom')); ?>
					and <?php echo form_input(array('name'=>'scc_cnt_1_dbto', 'value'=>$filter_selected['scc_cnt_1_dbto'], 'size'=>'5', 'maxlength'=>'5', 'id'=>'scc_cnt_1_dbto')); ?>
				<?php echo form_fieldset_close();
				break;
			case 'test_result':
				echo form_fieldset('Test Result', array('id' => 'test_result-fieldset')); ?>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'test_result[]', 'id'=>'test_resultstrong'), 'strong positive', in_array('strong positive', $filter_selected['test_result']) !== false); ?>
					Strong Positive&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'test_result[]', 'id'=>'test_resultpositive'), 'positive', in_array('positive', $filter_selected['test_result']) !== false); ?>
					Positive&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'test_result[]', 'id'=>'test_resultsuspect'), 'suspect', in_array('suspect', $filter_selected['test_result']) !== false); ?>
					Suspect&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'test_result[]', 'id'=>'test_resultnegative'), 'negative', in_array('negative', $filter_selected['test_result']) !== false); ?>
					Negative&nbsp;&nbsp;</span>
				<?php echo form_fieldset_close();
				break;
			case 'final_result':
				echo form_fieldset('Final Result', array('id' => 'final_result-fieldset')); ?>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'final_result[]', 'id'=>'final_resultpositive'), 'POS', in_array('POS', $filter_selected['final_result']) !== false); ?>
					Positive&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'final_result[]', 'id'=>'final_resultnegative'), 'NEG', in_array('NEG', $filter_selected['final_result']) !== false); ?>
					Negative&nbsp;&nbsp;</span>
					<span class="filter-checkbox"><?php echo form_checkbox(array('name'=>'final_result[]', 'id'=>'final_resultrecheck'), 'RECHECK', in_array('RECHECK', $filter_selected['final_result']) !== false); ?>
					Recheck&nbsp;&nbsp;</span>
					<?php echo form_fieldset_close();
				break;
		endswitch; 
	endforeach;
endif; ?>
<div class="submit"><?php echo form_submit('filter_submit', 'Apply Filter');?>&nbsp;&nbsp;&nbsp;<?php echo form_button('reset_filter', 'Reset Filter', 'onclick="form_reset()"')?>
</div>
<?php echo form_close();?>
</div>

<?php if(isset($page_footer) !== false) echo $page_footer; ?>