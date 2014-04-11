<?php if(isset($sections) && is_array($sections)): 
/* -----------------------------------------------------------------
 *  UPDATE comment
 *  @author: carolmd
 *  @date: Jan 9, 2014
 *
 *  @description: added 'ion_auth' to $this->config->item
 *  
 *  -----------------------------------------------------------------
 */
?>
	<div class="widget-content">
		<div id="past-test"><p>Select any number of the following products and click &quot;Request More Information.&quot;  An <?php $this->config->item('cust_serv_company')?> representative will follow-up with you and answer any questions you have.</p>
			<form action="auth/section_info" id="benchmark-form" method="post">
				<?php
				foreach($sections as $a):
					?><p><?php
						echo form_checkbox('sections[]', $a);
						echo form_label($a);
					?></p><?php
				endforeach;
				?><p><?php
				echo form_label('Comments or Questions');
				?></p><?php
				?><p><?php
				echo form_textarea(array('name'=>'comments', 'id'=>'comments', 'rows'=>'3', 'cols'=>'30'));
				?></p><?php
				?><p><?php
				echo form_submit('submit_sections','Request More Information', 'class="button"') ?>
				</p>
			</form>
		<?php
		if(isset($inner_html)) echo $inner_html; ?>
		</div>
	</div>
<?php endif; ?>