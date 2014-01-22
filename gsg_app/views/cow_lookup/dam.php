<div class="row">
	<div class="col-sm-12">
		<div class="box">
			<h3>Dam</h3>
			<div class="row">
				<div class="col-sm-3">
					<label>Cntl#</label> <?php echo $dam_control_num; ?>
				</div>
				<div class="col-sm-3">
					<label>Name</label> <?php echo $dam_name; ?>
				</div>
				<div class="col-sm-3">
					<label>VisID</label> <?php echo $visible_id; ?>
				</div>
				<div class="col-sm-3">
					<label>Breed</label> <?php echo $dam_breed_code; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-3">
					<label>ID</label> <?php echo $dam_id; ?>
				</div>
				<div class="col-sm-3">
					<label>DOB</label> <?php echo $birth_date; ?>
				</div>
				<div class="col-sm-3">
					<label>Avg ME Milk Dev</label> <?php echo $me_avg_lbs_dev_milk; ?>
				</div>
				<div class="col-sm-3">
					<label>NM$</label> <?php echo $net_merit_amt; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-4">
		<div class="box">
			<h3>Maternal Grand Dam</h3>
			<div class="row">
				<div class="col-sm-6">
					<label>Cntl#</label> <?php echo $mgr_dam_control_num; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label>Breed</label> <?php echo $mgr_dam_breed_code; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label>ID</label> <?php echo $mgr_dam_id; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label>Name</label> <?php echo $mgr_dam_name; ?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="box">
			<h3>Maternal Grand Sire</h3>
			<div class="row">
				<div class="col-sm-12">
					<label>NAAB</label> <?php echo $mgr_sire_primary_naab; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<label>Name</label> <?php echo $mgr_sire_name; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<label>Breed</label> <?php echo $mgr_sire_breed_code; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<label>ID</label> <?php echo $mgr_sire_id; ?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="box">
			<h3>Maternal Great Grand Sire</h3>
			<div class="row">
				<div class="col-sm-12">
					<label>NAAB</label> <?php echo $mgrtgr_sire_naab; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<label>Name</label> <?php echo $mgrtgr_sire_name; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<label>Breed</label> <?php echo $mgrtgr_sire_breed_code; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<label>ID</label> <?php echo $mgrtgr_sire_id; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
if(isset($lact_tables) && !empty($lact_tables)):
	echo $lact_tables; 
else: ?>
	<div>No lactation data found for <?php echo $barn_name; ?></div>
<?php 
endif; ?>
