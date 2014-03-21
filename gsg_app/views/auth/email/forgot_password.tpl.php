<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div style="background-color: #D3D09E; color:#000; border:solid 5px #D3D09E;"><img src="<?php echo $this->config->item('base_url')?>/img/agsource_logo_sm.jpg" alt="<?php echo $this->config->item('cust_serv_company'); ?>"></div>
	<div style="color: #D3D09E; background-color:#004147; border:solid 5px #004147; font-weight:bold;"><?php echo $this->config->item("product_name"); ?> - <?php echo $this->config->item('cust_serv_company'); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#004147;">Reset Password Request</h1>
	<p>Please click <?php echo anchor('auth/reset_password/'. $forgotten_password_code, 'this link'); ?> to verify that you requested a password change for <?php echo $identity;?> on <?php echo $this->config->item("product_name"); ?>.  If that link does not work, please cut and paste the following URL into your browser: <?php echo $this->config->item('base_url') . $this->config->item('index_page') . 'auth/reset_password/'. $forgotten_password_code; ?>.  After you have verified that you made this request, a new password will be created and sent to this e-mail address.</p>
	<p>Thank you for using <?php echo $this->config->item("product_name"); ?>.  If you have any questions or concerns, please contact us at <?php echo $this->config->item('cust_serv_email'); ?> or <?php echo $this->config->item('cust_serv_phone'); ?>.</p>
</body>
</html>