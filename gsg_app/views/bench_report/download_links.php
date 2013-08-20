<?php $this->load->helper('html_helper'); ?>
<div id="downloads">
<?php echo validation_errors(); ?>
<?php //$link_url = current_url() . '/csv'; ?>
<?php $link_url = site_url($report_path . '/display/' . $arr_sort_by[0] . '/' . $arr_sort_order[0]);
	$original_url = site_url($report_path . '/display/' . $arr_sort_by[0] . '/' . $arr_sort_order[0]);?>
<div class="handle"><a class="handle" id="file_exports">File Exports</a></div>
<p><?php echo anchor($link_url . '/csv', "CSV (Excel)", array('id'=>'csv', 'onclick'=>"return submit_table_sort_link('$form_id', '$link_url/csv', '$original_url');")); ?></p>
<p><?php echo anchor($link_url . '/pdf', "PDF", array('id'=>'pdf', 'onclick'=>"return submit_table_sort_link('$form_id', '$link_url/pdf', '$original_url');")); ?></p>
</div>