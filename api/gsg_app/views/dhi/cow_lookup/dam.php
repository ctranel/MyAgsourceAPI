<?php 
if(isset($dam) && !empty($dam)):
?>
	<div class="row">
		<div class="col-sm-12 col-xs-6">
			<div class="box">
				<h2>Dam</h2>
				<div class="row">
					<div class="col-sm-3 col-xs-12">
						<label>Cntl#</label> <?php echo $dam['dam_control_num']; ?>
					</div>
					<div class="col-sm-3 col-xs-12">
						<label>Name</label> <?php echo $dam['dam_name']; ?>
					</div>
					<div class="col-sm-3 col-xs-12">
						<label>Breed</label> <?php echo $dam['dam_breed_code']; ?>
					</div>
					<div class="col-sm-3 col-xs-12">
						<label>ID</label> <?php echo $dam['dam_id']; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-4 col-xs-6">
			<div class="box">
				<h2>Maternal Grand Dam</h2>
				<div class="row">
					<div class="col-xs-12">
						<label>Cntl#</label> <?php echo $dam['mgr_dam_control_num']; ?>
					</div>
					<div class="col-xs-12">
						<label>Breed</label> <?php echo $dam['mgr_dam_breed_code']; ?>
					</div>
					<div class="col-xs-12">
						<label>ID</label> <?php echo $dam['mgr_dam_id']; ?>
					</div>
					<div class="col-xs-12">
						<label>Name</label> <?php echo $dam['mgr_dam_name']; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-4 col-xs-6">
			<div class="box">
				<h2>Maternal Grand Sire</h2>
				<div class="row">
					<div class="col-xs-12">
						<label>NAAB</label> <?php echo $dam['mgr_sire_primary_naab']; ?>
					</div>
					<div class="col-xs-12">
						<label>Name</label> <?php echo $dam['mgr_sire_name']; ?>
					</div>
					<div class="col-xs-12">
						<label>Breed</label> <?php echo $dam['mgr_sire_breed_code']; ?>
					</div>
					<div class="col-xs-12">
						<label>ID</label> <?php echo $dam['mgr_sire_id']; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-4 col-xs-6">
			<div class="box">
				<h2>Maternal Great Grand Sire</h2>
				<div class="row">
					<div class="col-xs-12">
						<label>NAAB</label> <?php echo $dam['mgrtgr_sire_naab']; ?>
					</div>
					<div class="col-xs-12">
						<label>Name</label> <?php echo $dam['mgrtgr_sire_name']; ?>
					</div>
					<div class="col-xs-12">
						<label>Breed</label> <?php echo $dam['mgrtgr_sire_breed_code']; ?>
					</div>
					<div class="col-xs-12">
						<label>ID</label> <?php echo $dam['mgrtgr_sire_id']; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
else: ?>
	No dam ID information found.
<?php
endif;

if(isset($lact_tables) && !empty($lact_tables)):
	echo $lact_tables; 
else: ?>
	<div>No lactation data found.</div>
<?php 
endif; ?>
