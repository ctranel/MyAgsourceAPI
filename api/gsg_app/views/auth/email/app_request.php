<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div><img src="<?php echo $this->config->item('base_url')?>/img/logo-m.png" alt="<?php echo $this->config->item('cust_serv_company'); ?>"></div>
	<div style="color: #E6F4F4; background-color:#00958E; border-top:solid 5px #EF5C29; font-weight:bold; padding: 5px"><?php echo $this->config->item('product_name'); ?> - <?php echo $this->config->item('cust_serv_company'); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#003C39;">Product Information Request</h1>
	<p>Thank you for requesting additional information about <?php echo $this->config->item("cust_serv_company"); ?> products.  An <?php echo $this->config->item("cust_serv_company"); ?> representative will follow-up with you about the following products:</p>
	<ul>
		<?php 
		if(isset($sections) && is_array($sections)):
			foreach($sections as $a):
				echo '<li>' . $a . '</li>';
			endforeach;
		endif;
		?>
	</ul>
	<?php if(isset($comments) && !empty($comments)): ?>
		<p>Your comments:</p>
		<p><?php echo $comments; ?></p>
	<?php endif; ?>
	
	<p>The contact information we have on record for you is:</p>
	<ul style = "list-style-type:none;">
		<?php if(isset($name) && !empty($name)) echo '<li>Name: ' . $name . '</li>'; ?>
		<?php if(isset($email) && !empty($email)) echo '<li>E-mail: ' . $email . '</li>'; ?>
		<?php if(isset($herd_code) && !empty($herd_code)) echo '<li>Herd Code: ' . $herd_code . '</li>'; ?>
	</ul>
	
	<p>If you have any questions or concerns, please contact us at <?php echo $this->config->item("cust_serv_email"); ?> or <?php echo $this->config->item("cust_serv_phone"); ?>.</p>
</body>
</html>