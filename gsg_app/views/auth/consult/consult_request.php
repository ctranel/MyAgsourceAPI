<?php if(isset($page_header) !== FALSE) echo $page_header; ?>
<div class='mainInfo'>
	<?php if(isset($page_heading) !== FALSE) echo heading($page_heading); ?>
	<p>Please fill in the data.</p>
    <?php echo form_open("auth/consult_request");?>
      <p>
      	<label for="herd_code">Herd Code</label>
      	<?php echo form_input($herd_code);?>
      </p>
      <p>
      	<?php echo form_fieldset('Access Details', array('id' => 'data_shared')); ?>
      		<p><?php echo form_checkbox($write_data); ?><label for=write_data> Allow Me to Enter Event Data (Health, Repro, etc.)</label></p>
			<?php if(!empty($section_options)):?>
			<p>
				<?php echo form_fieldset('Products', array('id' => 'sections-fieldset'));
				foreach($section_options as $k=>$v):
					if(!empty($k)): ?>
							<span class="form-checkbox"><?php echo form_checkbox('section_id[]', $k, in_array($k, $sections_selected) !== false, 'class = "section-checkbox"'); echo $v; ?></span>
					<?php endif;
				endforeach;
				echo form_fieldset_close(); ?>
			</p>
			<?php endif;
		echo form_fieldset_close();
		?>
      </p>
      <p>
      	<label for="exp_date">Expiration Date</label>
      	<?php echo form_input($exp_date);?>
      </p>
    <p><?php echo form_checkbox($disclaimer); ?> I understand that when I allow a consultant to access my information, that consultant can access my herd and animal data through their own <?php echo $this->config->item("product_name"); ?> account.  At no point does any consultant have access to my account information...e-mail will be sent....</p>
    <p><?php echo form_submit('submit', 'Request Access');?></p>
    <?php echo form_close();?>
</div>
<?php if(isset($page_footer) !== false) echo $page_footer;