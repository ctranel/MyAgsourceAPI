<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div style="background-color: #D3D09E; color:#000; border:solid 5px #D3D09E;"><img src="<?php echo $this->config->item('base_url')?>/img/agsource_logo_sm.jpg" alt="<?php echo $this->config->item("cust_serv_company","ion_auth"); ?>"></div>
	<div style="color: #D3D09E; background-color:#004147; border:solid 5px #004147; font-weight:bold;"><?php echo $this->config->item("product_name"); ?> - <?php echo $this->config->item("cust_serv_company","ion_auth"); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#004147;">Access Granted for Herd <?php echo $herd_code; ?></h1>
	<p>You have been granted access to Herd <?php echo $herd_code; ?>, <?php echo $herd_owner; ?> owner.  To access herd information, log in to <?php echo anchor('auth', $this->config->item("product_name"));?>, click on the &quot;Select Herd&quot; link, and select the desired herd.</p>
	<p>If you have any questions or concerns, please contact us at <?php echo $this->config->item("cust_serv_email"); ?> or <?php echo $this->config->item("cust_serv_phone","ion_auth"); ?>.</p>
</body>
</html>