<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div style="background-color: #D3D09E; color:#000; border:solid 5px #D3D09E;"><img src="<?php echo $this->config->item('base_url')?>/img/agsource_logo_sm.jpg" alt="<?php echo $this->config->item("cust_serv_company","ion_auth"); ?>"></div>
	<div style="color: #D3D09E; background-color:#004147; border:solid 5px #004147; font-weight:bold;"><?php echo $this->config->item("product_name"); ?> - <?php echo $this->config->item("cust_serv_company","ion_auth"); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#004147;">Activate your <?php echo $this->config->item("product_name"); ?> account</h1>
	<p>Your <?php echo $this->config->item("product_name"); ?> account has been created.  Before you login, please <?php echo anchor('auth/activate/'. $id .'/'. $activation, 'click this link');?> to verify this e-mail address and activate your account.  If that link does not work, please cut and paste the following URL into your browser: <?php echo $this->config->item('base_url') . $this->config->item('index_page') . 'auth/activate/'. $id .'/'. $activation; ?></p>
	<p><?php echo $this->config->item("product_name"); ?> gives you access to a number of <?php echo $this->config->item("cust_serv_company","ion_auth"); ?> products with one convenient login.</p>
	<p>If you have any questions or concerns, please contact us at <?php echo $this->config->item("cust_serv_email"); ?> or <?php echo $this->config->item("cust_serv_phone","ion_auth"); ?>.</p>
</body>
</html>