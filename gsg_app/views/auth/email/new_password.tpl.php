<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div style="background-color: #D3D09E; color:#000; border:solid 5px #D3D09E;"><img src="<?php echo $this->config->item('base_url')?>/img/agsource_logo_sm.jpg" alt="<?php echo $this->config->item('cust_serv_company'); ?>"></div>
	<div style="color: #D3D09E; background-color:#004147; border:solid 5px #004147; font-weight:bold;"><?php echo $this->config->item("product_name"); ?> - <?php echo $this->config->item('cust_serv_company'); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#004147;">New <?php echo $this->config->item("product_name"); ?> Password</h1>
	
	<p>The password for <?php echo $identity;?> has been reset to: <?php echo $new_password;?></p>
	<p>Thank you for using <?php echo $this->config->item("product_name"); ?>.  If you have any questions or concerns, please contact us at <?php echo $this->config->item("cust_serv_email"); ?> or <?php echo $this->config->item('cust_serv_phone'); ?>.</p>
</body>
</html>