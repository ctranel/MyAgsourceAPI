<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div style="background-color: #D3D09E; color:#000; border:solid 5px #D3D09E;"><img src="<?php echo $this->config->item('base_url')?>/img/agsource_logo_sm.jpg" alt="<?php echo $this->config->item('cust_serv_company'); ?>"></div>
	<div style="color: #D3D09E; background-color:#004147; border:solid 5px #004147; font-weight:bold;"><?php echo $this->config->item('product_name'); ?> - <?php echo $this->config->item('cust_serv_company'); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#004147;">New <?php echo $this->config->item('product_name'); ?> Account Information</h1>
	<h2>Registration Information</h2>
	<p>Herd: <?php echo $herd_code; ?></p>
	<p>Email: <?php echo $email; ?></p>
	<p>Phone: <?php echo $phone; ?></p>
	<p>Best time to call: <?php echo $best_time; ?></p>
	<h2>Herd Information</h2>
	<p>Owner: <?php echo $arr_herd['herd_owner']; ?></p>
	<p>Farm Name: <?php echo $arr_herd['farm_name']; ?></p>
	<p>Address: <?php echo $arr_herd['address_1'] . '<br>' . $arr_herd['address_2'] . '<br>' . $arr_herd['city'] . ', ' . $arr_herd['state'] . ' ' . $arr_herd['zip_5']; ?></p>
	<p>Contact: <?php echo $arr_herd['contact_fn'] . ' ' . $arr_herd['contact_ln']; ?></p>
	<p>Primary Phone: <?php echo $arr_herd['primary_area_code'] . '-' . $arr_herd['primary_phone_num']; ?></p>
	<h2>Tech Information</h2>
	<p>Name: <?php echo $arr_tech['first_name'] . ' ' . $arr_tech['last_name']; ?></p>
	<p>Home Phone: <?php echo $arr_tech['home_phone']; ?></p>
	<p>Cell Phone: <?php echo $arr_tech['cell_phone']; ?></p>
	<p>Association: <?php echo $arr_tech['association_num']; ?></p>
	</body>
</html>