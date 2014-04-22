<?php if(isset($page_header) !== false) echo $page_header; ?>
<div class='mainInfo'>
	<?php if(isset($page_heading) !== false) echo heading($page_heading); ?>
<?php echo form_open("auth/service_grp_access");
if(isset($sg_user_id)):
echo form_input($sg_user_id);
    endif; ?>
	<p>Please submit the form below to grant or deny <?php echo $requester_name; ?> access to your herd&apos;s data.</p>
    <?php
    if(isset($write_data) || isset($section_options)): ?>
      	<?php echo form_fieldset('Data to Share', array('id' => 'data_shared')); 
			if(isset($write_data)): ?>
	      		<p><?php echo form_checkbox($write_data); ?> <label for=write_data> Allow Consultant to Enter Event Data</label></p>
			<?php endif;
			if(isset($section_options)):
				if(!empty($section_options)):?>
				<p>
					<?php echo form_fieldset('Sections', array('id' => 'sections-fieldset'));
					foreach($section_options as $k=>$v):
						if(!empty($k)): ?>
								<span class="form-checkbox"><?php echo form_checkbox('section_id[]', $k, in_array($k, $sections_selected) !== false, 'class = "section-checkbox"'); echo $v; ?></span>
						<?php endif;
					endforeach;
					echo form_fieldset_close(); ?>
				</p>
				<?php endif;
			endif;
		echo form_fieldset_close();
		?>
      <?php endif;
      if(isset($request_granted) && isset($request_denied)): ?>
      	<p>
      		<label for="request_status_id">Request Status</label>
			<?php echo form_radio($request_granted); ?> Grant&nbsp;&nbsp;&nbsp;
			<?php echo form_radio($request_denied); ?> Deny
		</p>
	  <?php endif; ?>
      <p>
      	<label for="exp_date">Expiration Date</label>
      	<?php echo form_input($exp_date);?>
      </p>
    <p><?php echo form_checkbox($disclaimer); ?> I understand that if I grant a consultant access to my herd&apos;s information, that consultant will be able to use any animal and herd summary data through their own <?php echo $this->config->item('product_name') ?> account. This consultant will not have access to my account information. An email will be sent to the consultant to inform them whether access has been granted or denied, and include any expiration date that is specified above.</p><p>Because relationships with consultants change over time, it is highly recommended that you do not share your login information with any consultant.</p>
      <p><?php echo form_submit('submit', 'Set Consultant Access', 'class="button"');?></p>
    <?php echo form_close();?>
	</div>
<?php if(isset($page_footer) !== false) echo $page_footer;