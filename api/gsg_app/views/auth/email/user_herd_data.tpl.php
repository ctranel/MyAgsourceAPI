<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div><img src="<?php echo $this->config->item('base_url')?>/img/logo-m.png" alt="<?php echo $this->config->item('cust_serv_company'); ?>"></div>
	<div style="color: #E6F4F4; background-color:#00958E; border-top:solid 5px #EF5C29; font-weight:bold; padding: 5px"><?php echo $this->config->item('product_name'); ?> - <?php echo $this->config->item('cust_serv_company'); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#003C39;">New <?php echo $this->config->item('product_name'); ?> Account Information</h1>
	<h2 style="font-size: 1em;"><?php echo (int)$group === 9 ? 'Consultant' : 'Producer'; ?> Registration Information</h2>
	<div>Name: <?php echo $first_name . ' ' . $last_name; ?></div>
	<div>Herd: <?php echo $herd_code; ?></div>
	<div>Email: <?php echo $email; ?></div>
	<div>Phone: <?php echo $phone; ?></div>

	<?php if((int)$group === 2){ ?>
		<h2 style="font-size: 1em;">Herd Information</h2>
		<div>Owner: <?php echo $arr_herd['herd_owner']; ?></div>
		<div>Farm Name: <?php echo $arr_herd['farm_name']; ?></div>
		<div>Address: <?php echo $arr_herd['address_1'] . '<br>' . $arr_herd['address_2'] . '<br>' . $arr_herd['city'] . ', ' . $arr_herd['state'] . ' ' . $arr_herd['zip_5']; ?></div>
		<div>Contact: <?php echo $arr_herd['contact_fn'] . ' ' . $arr_herd['contact_ln']; ?></div>
		<div>Email: <?php echo $arr_herd['email']; ?></div>
		<div>Primary Phone: <?php echo $arr_herd['primary_area_code'] . '-' . $arr_herd['primary_phone_num']; ?></div>
		<h2 style="font-size: 1em;">Tech Information</h2>
		<div>Name: <?php echo $arr_tech['first_name'] . ' ' . $arr_tech['last_name']; ?></div>
		<div>Home Phone: <?php echo $arr_tech['home_phone']; ?></div>
		<div>Cell Phone: <?php echo $arr_tech['cell_phone']; ?></div>
		<div>Association: <?php echo $arr_tech['association_num']; ?></div>
	<?php } ?>
</body>
</html>