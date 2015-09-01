<?php if(!isset($sire_naab)): ?>
	No sire data found
<?php else: ?>
	<div class="row">
		<div class="col-sm-8 col-xs-12">
			<div class="box">
				<h2>Sire</h2>
				<div class="row">
					<div class="col-xs-6">
						<label>NAAB</label> <?php echo $sire_naab; ?>
					</div>
					<div class="col-xs-6">
						<label>Name</label> <?php echo $sire_name; ?>
					</div>
	
					<div class="col-xs-6">
						<label>ID</label> <?php echo $sire_id; ?>
					</div>
					<div class="col-xs-6">
						<label>Reg Name</label> <?php echo $sire_registered_name; ?>
					</div>
	
					<div class="col-xs-6">
						<label>Breed</label> <?php echo $sire_breed_code; ?>
					</div>
					<div class="col-xs-6">
						&nbsp;
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-4 col-xs-12">
			<div class="box">
				<h2>Paternal Grand Sire</h2>
				<div class="row">
					<div class="col-xs-12">
						<label>NAAB</label> <?php echo $pgr_sire_primary_naab; ?>
					</div>
	
					<div class="col-xs-12">
						<label>Name</label> <?php echo $pgr_sire_short_ai_name; ?>
					</div>
	
					<div class="col-xs-12">
						<label>Reg Name</label> <?php echo $pgr_sire_registered_name; ?>
					</div>
				</div>
			</div>
		</div>
	
		<div class="col-xs-12">
			<div class="box">
				<h2>Sire PTA</h2>
				<div class="row">
					<div class="col-sm-3 col-xs-6">
						<label>Eval Date</label> <?php echo $sire_load_date; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>Protein</label> <?php echo $sire_pta_pro_lbs; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>Prod Life Reliab.</label> <?php echo $sire_pta_prod_life_reliab; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>NM$ %tile</label> <?php echo $sire_net_merit_pctile; ?>
					</div>
	
					<div class="col-sm-3 col-xs-6">
						<label>Milk</label> <?php echo $sire_pta_milk_lbs; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>Protein %</label> <?php echo $sire_pta_pro_pct; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>Productive Life</label> <?php echo $sire_pta_prod_life; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>NM$ Reliab.</label> <?php echo $sire_net_merit_reliab; ?>
					</div>
	
					<div class="col-sm-3 col-xs-6">
						<label>Fat</label> <?php echo $sire_pta_fat_lbs; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>Fluid Net Merit $</label> <?php echo $sire_fluid_merit_amt; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>SCS Reliab.</label> <?php echo $sire_pta_scs_reliab; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>NM$</label> <?php echo $sire_net_merit_amt; ?>
					</div>
	
					<div class="col-sm-3 col-xs-6">
						<label>Fat %</label> <?php echo $sire_pta_fat_pct; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>Cheese Net Merit $</label> <?php echo $sire_cheese_merit_amt; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>SCS</label> <?php echo $sire_pta_scs; ?>
					</div>
					<div class="col-sm-3 col-xs-6">
						<label>Inbred Coef.</label> <?php echo $sire_inbreeding_coeff_pct; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php 
endif;
?>