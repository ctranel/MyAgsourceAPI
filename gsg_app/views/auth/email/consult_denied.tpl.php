<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div style="background-color: #D3D09E; color:#000; border:solid 5px #D3D09E;"><img src="<?php echo $this->config->item('base_url')?>/img/agsource_logo_sm.jpg" alt="<?php echo $this->config->item("cust_serv_company") ?>"></div>
	<div style="color: #D3D09E; background-color:#004147; border:solid 5px #004147; font-weight:bold;"><?php echo $this->config->item("product_name"); ?> - <?php echo $this->config->item("cust_serv_company"); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#004147;">Access Denied for Herd <?php echo $herd_code; ?></h1>
	<p>Herd <?php echo $herd_code; ?>, <?php echo $herd_owner; ?> owner, has chosen not to allow access to herd data.  Please contact the herd if you have questions or concerns.</p>
</body>
</html>