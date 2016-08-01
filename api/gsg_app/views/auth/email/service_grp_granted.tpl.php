<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div><img src="<?php echo $this->config->item('base_url')?>/img/logo-m.png" alt="<?php echo $this->config->item('cust_serv_company'); ?>"></div>
	<div style="color: #E6F4F4; background-color:#00958E; border-top:solid 5px #EF5C29; font-weight:bold; padding: 5px"><?php echo $this->config->item('product_name'); ?> - <?php echo $this->config->item('cust_serv_company'); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#003C39;">Access Granted for Herd <?php echo $herd_code; ?></h1>
	<p>You have been granted access to Herd <?php echo $herd_code; ?>, <?php echo $herd_owner; ?> owner.  To access herd information, log in to <?php echo anchor('auth', $this->config->item('product_name'));?>, click on the &quot;Select Herd&quot; link, and select the desired herd.</p>
	<p>If you have any questions or concerns, please contact <?php echo $this->config->item('cust_serv_company'); ?> at <?php echo $this->config->item('cust_serv_email'); ?> or <?php echo $this->config->item('cust_serv_phone'); ?>.</p>
</body>
</html>