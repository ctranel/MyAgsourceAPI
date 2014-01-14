<div class="row">
	<div class="col-sm-3">
		<label>CNTL #</label> <?php echo $control_num; ?>
	</div>
	<div class="col-sm-3">
		<label>Name</label> <?php echo $barn_name; ?>
	</div>
	<div class="col-sm-3">
		<label>Visible ID</label> <?php echo $visible_id; ?>
	</div>
	<div class="col-sm-3">
		<label>Reg #</label> <?php echo $country_code . $cow_id; ?>
	</div>
</div>
<div class="row">
	<div class="col-sm-3">
		<label>Lact #</label> <?php echo $lact_num; ?>
	</div>
	<div class="col-sm-3">
		<label>Breed</label> <?php echo $breed_code; ?>
	</div>
	<div class="col-sm-3">
		<label>DOB</label> <?php echo $birth_date; ?>
	</div>
	<div class="col-sm-3">
		<label>Twin Code</label> <?php echo $twin_code; ?>
	</div>
</div>
<div class="row">
	<div class="col-sm-6">
		<div class="box">
			<h3>Sire</h3>
			<div class="row">
				<div class="col-sm-6">
					<label>NAAB</label> <?php echo $sire_naab; ?>
				</div>
				<div class="col-sm-6">
					<label>Name</label> <?php echo $sire_name; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label>Reg #</label> <?php echo $sire_id; ?>
				</div>
				<div class="col-sm-6">
					<label>Breed</label> <?php echo $sire_breed_code; ?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="box">
			<h3>Dam</h3>
			<div class="row">
				<div class="col-sm-6">
					<label>CNTL #</label> <?php echo $dam_control_num; ?>
				</div>
				<div class="col-sm-6">
					<label>Name</label> <?php echo $dam_name; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label>Reg #</label> <?php echo $dam_country_code . $dam_id; ?>
				</div>
				<div class="col-sm-6">
					<label>Breed</label> <?php echo $dam_breed_code; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-4">
		<div class="box">
			<h3>Maternal Grand Sire</h3>
			<div class="row">
				<div class="col-sm-6">
					<label>NAAB</label> <?php echo $mgr_sire_naab; ?>
				</div>
				<div class="col-sm-6">
					<label>Name</label> <?php echo $mgr_sire_name; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label>Reg #</label> <?php echo $mgr_sire_id; ?>
				</div>
				<div class="col-sm-6">
					<label>Breed</label> <?php echo $mgr_sire_breed_code; ?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="box">
			<h3>Maternal Great Grand Sire</h3>
			<div class="row">
				<div class="col-sm-6">
					<label>NAAB</label> <?php echo $mgrtgr_sire_naab; ?>
				</div>
				<div class="col-sm-6">
					<label>Name</label> <?php echo $mgrtgr_sire_name; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label>Reg #</label> <?php echo $mgrtgr_sire_id; ?>
				</div>
				<div class="col-sm-6">
					<label>Breed</label> <?php echo $mgrtgr_sire_breed_code; ?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="box">
			<h3>Maternal Grand Dam</h3>
			<div class="row">
				<div class="col-sm-6">
					<label>CNTL #</label> <?php echo $mgr_dam_control_num; ?>
				</div>
				<div class="col-sm-6">
					<label>Name</label> <?php echo $mgr_dam_name; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label>Reg #</label> <?php echo $mgr_dam_country_code . $mgr_dam_id; ?>
				</div>
				<div class="col-sm-6">
					<label>Breed</label> <?php echo $mgr_dam_breed_code; ?>
				</div>
			</div>
		</div>
	</div>
</div>