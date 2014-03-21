<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div style="background-color: #D3D09E; color:#000; border:solid 5px #D3D09E;"><img src="<?php echo $this->config->item('base_url')?>/img/agsource_logo_sm.jpg" alt="<?php echo $this->config->item("cust_serv_company"); ?>"></div>
	<div style="color: #D3D09E; background-color:#004147; border:solid 5px #004147; font-weight:bold;"><?php echo $this->config->item("product_name"); ?> - <?php echo $this->config->item("cust_serv_company"); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#004147;">Product Information Request</h1>
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