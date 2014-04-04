<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div style="background-color: #D3D09E; color:#000; border:solid 5px #D3D09E;"><img src="<?php echo $this->config->item('base_url')?>/img/agsource_logo_sm.jpg" alt="<?php echo $this->config->item('cust_serv_company'); ?>"></div>
	<div style="color: #D3D09E; background-color:#004147; border:solid 5px #004147; font-weight:bold;"><?php echo $this->config->item('product_name'); ?> - <?php echo $this->config->item('cust_serv_company'); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#004147;">Consultant Access Requested</h1>
	<p><?php echo $first_name . ' ' . $last_name; ?><?php if(!empty($company)) echo ' of ' . $company; ?> (acct num: <?php echo $sg_acct_num; ?>) has requested access to data for herd <?php echo $herd_code; ?>.  <?php echo anchor('auth/service_grp_access/'. $id, 'Click this link');?> to allow or deny the requested access.  If that link does not work, please cut and paste the following URL into your browser: <?php echo $this->config->item('base_url') . $this->config->item('index_page') . 'auth/grant_consult/'. $id; ?></p>
	<p><?php echo $this->config->item('product_name'); ?> gives you access to a number of <?php echo $this->config->item('cust_serv_company'); ?> products with one convenient login.</p>
	<p>If you have any questions or concerns, please contact <?php echo $this->config->item('cust_serv_company'); ?> at <?php echo $this->config->item('cust_serv_email'); ?> or <?php echo $this->config->item('cust_serv_phone'); ?>.</p>
</body>
</html>
