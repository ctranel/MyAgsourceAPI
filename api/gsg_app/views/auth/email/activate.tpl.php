<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div><img src="<?php echo $this->config->item('base_url')?>/img/logo-m.png" alt="<?php echo $this->config->item('cust_serv_company'); ?>"></div>
	<div style="color: #E6F4F4; background-color:#00958E; border-top:solid 5px #EF5C29; font-weight:bold; padding: 5px"><?php echo $this->config->item('product_name'); ?> - <?php echo $this->config->item('cust_serv_company'); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#003C39;">Activate your <?php echo $this->config->item("product_name"); ?> account</h1>
	<p>Your <?php echo $this->config->item("product_name"); ?> account has been created.  Before you login, please <?php echo anchor('auth/activate/'. $id .'/'. $activation, 'click this link');?> to verify this e-mail address and activate your account.  If that link does not work, please cut and paste the following URL into your browser: <?php echo $this->config->item('base_url') . $this->config->item('index_page') . 'auth/activate/'. $id .'/'. $activation; ?></p>
	<p><?php echo $this->config->item("product_name"); ?> gives you access to a number of <?php echo $this->config->item("cust_serv_company"); ?> products with one convenient login.  You are enrolled in a <?php echo $this->config->item("trial_period"); ?> day free trial during which you will have access to all content available on MyAgSource.  At the end of that period, you may choose the product for which you want to enroll.</p>
	<p>If you have any questions or concerns, please contact us at <?php echo $this->config->item("cust_serv_email"); ?> or <?php echo $this->config->item("cust_serv_phone"); ?>.</p>
</body>
</html>