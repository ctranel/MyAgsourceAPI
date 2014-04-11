<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div><img src="<?php echo $this->config->item('base_url')?>/img/logo-m.png" alt="<?php echo $this->config->item('cust_serv_company'); ?>"></div>
	<div style="color: #E6F4F4; background-color:#00958E; border-top:solid 5px #EF5C29; font-weight:bold; padding: 5px"><?php echo $this->config->item('product_name'); ?> - <?php echo $this->config->item('cust_serv_company'); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#003C39;">Reset Password Request</h1>
	<p>Please click <?php echo anchor('auth/reset_password/'. $forgotten_password_code, 'this link'); ?> to verify that you requested a password change for <?php echo $identity;?> on <?php echo $this->config->item("product_name"); ?>.  If that link does not work, please cut and paste the following URL into your browser: <?php echo $this->config->item('base_url') . $this->config->item('index_page') . 'auth/reset_password/'. $forgotten_password_code; ?>.  After you have verified that you made this request, a new password will be created and sent to this e-mail address.</p>
	<p>Thank you for using <?php echo $this->config->item("product_name"); ?>.  If you have any questions or concerns, please contact us at <?php echo $this->config->item('cust_serv_email'); ?> or <?php echo $this->config->item('cust_serv_phone'); ?>.</p>
</body>
</html>