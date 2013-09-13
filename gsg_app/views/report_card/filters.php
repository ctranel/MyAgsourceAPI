<?php if(isset($page_header) !== false) echo $page_header; ?>
<?php $this->load->helper('html_helper'); ?>
<div id="filters">
<?php echo validation_errors(); ?>
<?php if(isset($form_url) === false) $form_url = current_url(); ?>
<?php //$link_url = str_replace('display', 'filter', $form_url)?>
<div class="handle"><a id="set-filters" class="handle">Select Report Criteria</a></div>
<?php //echo anchor("#","Apply Filters", 'class="handle"'); ?>
<?php echo form_open($form_url, array('name'=>'report_criteria', 'id'=>'report_criteria')); ?>
<?php if(is_array($arr_filters)):
	 foreach($arr_filters as $f):
		switch($f): 
			case 'pstring': ?>
				<?php if(!empty($arr_pstring) && count($arr_pstring) > 1):
					echo form_fieldset('PString', array('id' => 'pages-fieldset'));
						$top=count($arr_pstring);
						for( $c=0; $c < $top; $c++): ?>
							<span class="pstring-filter-item filter-checkbox">
								<?php echo form_radio('pstring', $arr_pstring[$c]['pstring'], $filter_selected['pstring'] == $arr_pstring[$c]['pstring']);
								// echo form_checkbox('pstring[]', $arr_pstring[$c]['pstring'], $filter_selected['pstring'] == $arr_pstring[$c]['pstring']);
								echo $arr_pstring[$c]['publication_name']; ?>
							</span>
						<?php endfor;
					echo form_fieldset_close(); ?>
				<?php endif; ?>
				<?php break;
			case 'benchmarks': ?>
					<?php echo form_fieldset('Benchmarks', array('id' => 'benchmarks-fieldset')); ?>
					<span class="filter-checkbox">
						<?php echo form_radio(array('name'=>'benchmarks_id', 'id'=>'benchmarks_id11'), '1-1', ($filter_selected['benchmarks_id'] == '1' &&  $filter_selected['all_breeds_code'] == '1')); ?>
						Holstein, 1-100&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_radio(array('name'=>'benchmarks_id', 'id'=>'benchmarks_id12'), '1-2', ($filter_selected['benchmarks_id'] == '2' &&  $filter_selected['all_breeds_code'] == '1')); ?>
						Holstein, 101-250&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_radio(array('name'=>'benchmarks_id', 'id'=>'benchmarks_id13'), '1-3', ($filter_selected['benchmarks_id'] == '3' &&  $filter_selected['all_breeds_code'] == '1')); ?>
						Holstein, 251-500&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_radio(array('name'=>'benchmarks_id', 'id'=>'benchmarks_id14'), '1-4', ($filter_selected['benchmarks_id'] == '4' &&  $filter_selected['all_breeds_code'] == '1')); ?>
						Holstein, 501-1000&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_radio(array('name'=>'benchmarks_id', 'id'=>'benchmarks_id15'), '1-5', ($filter_selected['benchmarks_id'] == '5' &&  $filter_selected['all_breeds_code'] == '1')); ?>
						Holstein, &gt;1000&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_radio(array('name'=>'benchmarks_id', 'id'=>'benchmarks_id10'), '1-0', $filter_selected['benchmarks_id'] == '0'); ?>
						Jersey&nbsp;&nbsp;
					</span>
					<?php echo form_fieldset_close();
					 echo form_fieldset('Combined-breed Benchmarks', array('id' => 'combined-benchmarks-fieldset')); ?>
					<span class="filter-checkbox">
						<?php echo form_radio(array('name'=>'benchmarks_id', 'id'=>'benchmarks_id21'), '2-1', ($filter_selected['benchmarks_id'] == '1' &&  $filter_selected['all_breeds_code'] == '2')); ?>
						All Herds, 1-100&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_radio(array('name'=>'benchmarks_id', 'id'=>'benchmarks_id22'), '2-2', ($filter_selected['benchmarks_id'] == '2' &&  $filter_selected['all_breeds_code'] == '2')); ?>
						All Herds, 101-250&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_radio(array('name'=>'benchmarks_id', 'id'=>'benchmarks_id23'), '2-3', ($filter_selected['benchmarks_id'] == '3' &&  $filter_selected['all_breeds_code'] == '2')); ?>
						All Herds, 251-500&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_radio(array('name'=>'benchmarks_id', 'id'=>'benchmarks_id24'), '2-4', ($filter_selected['benchmarks_id'] == '4' &&  $filter_selected['all_breeds_code'] == '2')); ?>
						All Herds, 501-1000&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
						<?php echo form_radio(array('name'=>'benchmarks_id', 'id'=>'benchmarks_id25'), '2-5', ($filter_selected['benchmarks_id'] == '5' &&  $filter_selected['all_breeds_code'] == '2')); ?>
						All Herds, &gt;1000&nbsp;&nbsp;
					</span>
				<?php echo form_fieldset_close();
				 break;
			case 'chart': ?>
					<?php echo form_fieldset('Chart', array('id' => 'chart-fieldset')); ?>
					<span class="filter-checkbox">
					<?php echo form_radio(array('name'=>'chart', 'id'=>'chart1'), 'production', $filter_selected['chart'] == 'production'); ?>
					Production&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
					<?php echo form_radio(array('name'=>'chart', 'id'=>'chart2'), 'reproduction', $filter_selected['chart'] == 'reproduction'); ?>
					Reproduction &amp; Genetics&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
					<?php echo form_radio(array('name'=>'chart', 'id'=>'chart3'), 'inventory', $filter_selected['chart'] == 'inventory'); ?>
					Inventory&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
					<?php echo form_radio(array('name'=>'chart', 'id'=>'chart4'), 'uhm', $filter_selected['chart'] == 'uhm'); ?>
					Udder Health&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
					<?php echo form_radio(array('name'=>'chart', 'id'=>'chart5'), 'fresh-cow', $filter_selected['chart'] == 'fresh-cow'); ?>
					Fresh Cow Transition&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
					<?php echo form_radio(array('name'=>'chart', 'id'=>'chart6'), 'long-milk', $filter_selected['chart'] == 'long-milk'); ?>
					Milk Quantity &amp; Quality&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
					<?php echo form_radio(array('name'=>'chart', 'id'=>'chart7'), 'long-trans', $filter_selected['chart'] == 'long-trans'); ?>
					Transition - Mastitis - Reproduction&nbsp;&nbsp;
					</span>
					<span class="filter-checkbox">
					<?php echo form_radio(array('name'=>'chart', 'id'=>'chart8'), 'long-genetics', $filter_selected['chart'] == 'long-genetics'); ?>
					Genetics - Inventory&nbsp;&nbsp;
					</span>
				<?php echo form_fieldset_close();
				break;
		endswitch; 
	endforeach;
endif; ?>
<div class="submit"><?php echo form_submit('filter_submit', 'Apply Criteria');?>&nbsp;&nbsp;&nbsp;<?php echo form_button('reset_filter', 'Reset Criteria', 'onclick="form_reset()"')?>
</div>
<?php echo form_close();?>
</div>

<?php if(isset($page_footer) !== false) echo $page_footer; ?>