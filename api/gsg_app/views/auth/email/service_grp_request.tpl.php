<html>
<body style="font-family: Helvetica,Arial,Verdana,sans-serif">
	<div><img src="<?php echo $this->config->item('base_url')?>/img/logo-m.png" alt="<?php echo $this->config->item('cust_serv_company'); ?>"></div>
	<div style="color: #E6F4F4; background-color:#00958E; border-top:solid 5px #EF5C29; font-weight:bold; padding: 5px"><?php echo $this->config->item('product_name'); ?> - <?php echo $this->config->item('cust_serv_company'); ?></div>
	<h1 style="font-size: 1.1em; background-color: #fff; color:#003C39;">Consultant Access Requested</h1>
	<p><?php echo $first_name . ' ' . $last_name; ?><?php if(!empty($company)) echo ' of ' . $company; ?> <?php if(!empty($sg_acct_num)) echo "(acct num: $sg_acct_num; )" ?> has requested access to data for herd <?php echo $herd_code; ?>.  <?php echo anchor('auth/service_grp_access/'. $id, 'Click this link');?> to allow or deny the requested access.  If that link does not work, please cut and paste the following URL into your browser: <?php echo $this->config->item('base_url') . $this->config->item('index_page') . 'auth/service_grp_access/'. $id; ?></p>
	<p><?php echo $this->config->item('product_name'); ?> gives you access to a number of <?php echo $this->config->item('cust_serv_company'); ?> products with one convenient login.</p>
	<p>If you have any questions or concerns, please contact <?php echo $this->config->item('cust_serv_company'); ?> at <?php echo $this->config->item('cust_serv_email'); ?> or <?php echo $this->config->item('cust_serv_phone'); ?>.</p>
</body>
</html>
