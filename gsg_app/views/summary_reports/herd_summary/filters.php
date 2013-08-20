<?php if(isset($page_header) !== false) echo $page_header; ?>
<?php $this->load->helper('html_helper'); ?>
<div id="filters">
<?php echo validation_errors(); ?>
<?php if(isset($form_url) === false) $form_url = current_url(); ?>
<?php //$link_url = str_replace('display', 'filter', $form_url)?>
<div class="handle"><a id="set_filters" class="handle">Select Report Criteria</a></div>
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
								<?php echo form_radio('pstring', $arr_pstring[$c]['pstring'], $filter_selected['pstring'][0] == $arr_pstring[$c]['pstring']);
								// echo form_checkbox('pstring[]', $arr_pstring[$c]['pstring'], $filter_selected['pstring'][0] == $arr_pstring[$c]['pstring']);
								echo $arr_pstring[$c]['publication_name']; ?>
							</span>
						<?php endfor;
					echo form_fieldset_close(); ?>

					
					<!-- <p class = "filter-group">
						<?php echo form_label('Pstring', 'pstring'); ?>
						<?php //echo form_multiselect('f_quartile', $quart_options, $quart_selected); ?>
						<?php $top=count($arr_pstring);
						for( $c=0; $c < $top; $c++): ?>
							<p class="pstring-filter-item filter-checkbox">
								<?php echo form_radio('pstring', $arr_pstring[$c]['pstring'], $filter_selected['pstring'][0] == $arr_pstring[$c]['pstring']);
								// echo form_checkbox('pstring[]', $arr_pstring[$c]['pstring'], $filter_selected['pstring'][0] == $arr_pstring[$c]['pstring']);
								echo $arr_pstring[$c]['publication_name']; ?>
							</p>
						<?php endfor; ?>
					</p> -->
				<?php endif; ?>
				<?php break;
			case 'block': ?>
					<?php if(isset($arr_pages) && is_array($arr_pages)):
						echo form_fieldset('Block', array('id' => 'pages-fieldset'));
						foreach($arr_pages as $k=>$e):
							if(!empty($k)): ?>
									<span class="filter-checkbox"><?php echo form_radio(array('name'=>'block', 'id'=>'block' . $e['url_segment']), $e['url_segment'], ($filter_selected['block'][0] == $e['url_segment'])); echo $e['name']; ?></span>
							<?php endif;
						endforeach;
						echo form_fieldset_close();
					
					endif;
				break;
		endswitch; 
	endforeach;
endif; ?>
<div class="submit"><?php echo form_submit('filter_submit', 'Apply Criteria');?>&nbsp;&nbsp;&nbsp;<?php echo form_button('reset_filter', 'Reset Criteria', 'onclick="form_reset()"')?>
</div>
<?php echo form_close();?>
</div>

<?php if(isset($page_footer) !== false) echo $page_footer; ?>