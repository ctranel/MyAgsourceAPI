<?php
if (!empty($page_header)) echo $page_header;
if (!empty($page_heading)) echo heading($page_heading); 
?>
	<?php if (!empty($herd_data)): 
		echo $herd_data;
	endif;	?>
	<?php if (!empty($table_heading)): 
		?><h2 class="block"><?php echo $table_heading; ?></h2><?php
	endif;	?>	
	<?php if (!empty($table_sub_heading)): 
		?><h3 class="block"><?php echo $table_sub_heading; ?></h3><?php
	endif;	?>	
	<?php if (!empty($table_benchmark_text)): 
		?><h3 class="block"><?php echo $table_benchmark_text; ?></h3><?php
	endif;
$arr_cls = array('' => 'b-same', '-' => 'b-down', '+' => 'b-up');
if(isset($bench_data) && is_array($bench_data)): ?>
	<table class="tbl"> <!-- 23 rows -->
		<tr><th class="subcat-heading">Metric</th><th class="subcat-heading">Prev Test</th><th class="subcat-heading">Trend</th><th class="subcat-heading">Curr Test</th><th class="subcat-heading">80th %tile</th></tr>
		<?php $s = get_trend_symbol($bench_data['prev_daily_milk_production'], $bench_data['curr_daily_milk_production'], TRUE);?>
		<tr class="odd"><td>Daily Milk Production</td><td><?php echo $bench_data['prev_daily_milk_production']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_daily_milk_production']; ?></td><td><?php echo $bench_data['bench_daily_milk_production']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_management_level_milk'], $bench_data['curr_management_level_milk'], TRUE);?>
		<tr class="even"><td>MLM</td><td><?php echo $bench_data['prev_management_level_milk']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_management_level_milk']; ?></td><td><?php echo $bench_data['bench_management_level_milk']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_fat_pct'], $bench_data['curr_fat_pct'], TRUE);?>
		<tr class="odd"><td>% Fat</td><td><?php echo $bench_data['prev_fat_pct']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_fat_pct']; ?></td><td><?php echo $bench_data['bench_fat_pct']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_pro_pct'], $bench_data['curr_pro_pct'], TRUE);?>
		<tr class="even"><td>% Pro</td><td><?php echo $bench_data['prev_pro_pct']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_pro_pct']; ?></td><td><?php echo $bench_data['bench_pro_pct']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_l1_peak_milk'], $bench_data['curr_l1_peak_milk'], TRUE);?>
		<tr class="odd"><td>Lact 1 Peak Milk</td><td><?php echo $bench_data['prev_l1_peak_milk']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_l1_peak_milk']; ?></td><td><?php echo $bench_data['bench_l1_peak_milk']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_l2_peak_milk'], $bench_data['curr_l2_peak_milk'], TRUE);?>
		<tr class="even"><td>Lact 2 Peak Milk</td><td><?php echo $bench_data['prev_l2_peak_milk']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_l2_peak_milk']; ?></td><td><?php echo $bench_data['bench_l2_peak_milk']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_l3_peak_milk'], $bench_data['curr_l3_peak_milk'], TRUE);?>
		<tr class="odd"><td>Lact 3+ Peak Milk</td><td><?php echo $bench_data['prev_l3_peak_milk']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_l3_peak_milk']; ?></td><td><?php echo $bench_data['bench_l3_peak_milk']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_l1_avg_linear_score'], $bench_data['curr_l1_avg_linear_score'], FALSE);?>
		<tr class="even"><td>Lact 1 Linear Score</td><td><?php echo $bench_data['prev_l1_avg_linear_score']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_l1_avg_linear_score']; ?></td><td><?php echo $bench_data['bench_l1_avg_linear_score']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_l4_avg_linear_score'], $bench_data['curr_l4_avg_linear_score'], FALSE);?>
		<tr class="odd"><td>Lact 2+ Linear Score</td><td><?php echo $bench_data['prev_l4_avg_linear_score']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_l4_avg_linear_score']; ?></td><td><?php echo $bench_data['bench_l4_avg_linear_score']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_wtd_avg_scc'], $bench_data['curr_wtd_avg_scc'], FALSE);?>
		<tr class="even"><td>Weighted Avg SCC</td><td><?php echo $bench_data['prev_wtd_avg_scc']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_wtd_avg_scc']; ?></td><td><?php echo $bench_data['bench_wtd_avg_scc']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_l0_new_infection_pct'], $bench_data['curr_l0_new_infection_pct'], FALSE);?>
		<tr class="odd"><td>% New Infections</td><td><?php echo $bench_data['prev_l0_new_infection_pct']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_l0_new_infection_pct']; ?></td><td><?php echo $bench_data['bench_l0_new_infection_pct']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_chronic_cases_pct'], $bench_data['curr_chronic_cases_pct'], FALSE);?>
		<tr class="even"><td>% Chronic Infections</td><td><?php echo $bench_data['prev_chronic_cases_pct']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_chronic_cases_pct']; ?></td><td><?php echo $bench_data['bench_chronic_cases_pct']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_l1_1st_new_infection_pct'], $bench_data['curr_l1_1st_new_infection_pct'], FALSE);?>
		<tr class="odd"><td>Lact 1 New Infections</td><td><?php echo $bench_data['prev_l1_1st_new_infection_pct']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_l1_1st_new_infection_pct']; ?></td><td><?php echo $bench_data['bench_l1_1st_new_infection_pct']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_l4_dry_cow_cured_pct'], $bench_data['curr_l4_dry_cow_cured_pct'], TRUE);?>
		<tr class="even"><td>Lact 2+ Dry Period Cures</td><td><?php echo $bench_data['prev_l4_dry_cow_cured_pct']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_l4_dry_cow_cured_pct']; ?></td><td><?php echo $bench_data['bench_l4_dry_cow_cured_pct']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_avg_tci'], $bench_data['curr_avg_tci'], TRUE);?>
		<tr class="odd"><td>Avg TCI&reg;</td><td><?php echo $bench_data['prev_avg_tci']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_avg_tci']; ?></td><td><?php echo $bench_data['bench_avg_tci']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_service_rate_pct'], $bench_data['curr_service_rate_pct'], TRUE);?>
		<tr class="even"><td>Service Rate</td><td><?php echo $bench_data['prev_service_rate_pct']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_service_rate_pct']; ?></td><td><?php echo $bench_data['bench_service_rate_pct']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_pregnancy_rate_pct'], $bench_data['curr_pregnancy_rate_pct'], TRUE);?>
		<tr class="odd"><td>Preg Rate</td><td><?php echo $bench_data['prev_pregnancy_rate_pct']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_pregnancy_rate_pct']; ?></td><td><?php echo $bench_data['bench_pregnancy_rate_pct']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_pregnancy_loss_pct'], $bench_data['curr_pregnancy_loss_pct'], TRUE);?>
		<tr class="even"><td>Preg Loss Rate</td><td><?php echo $bench_data['prev_pregnancy_loss_pct']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_pregnancy_loss_pct']; ?></td><td><?php echo $bench_data['bench_pregnancy_loss_pct']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_l0_exit_died_percent'], $bench_data['curr_l0_exit_died_percent'], FALSE);?>
		<tr class="odd"><td>% Died</td><td><?php echo $bench_data['prev_l0_exit_died_percent']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_l0_exit_died_percent']; ?></td><td><?php echo $bench_data['bench_l0_exit_died_percent']; ?></td></tr>
 		<?php $s = get_trend_symbol($bench_data['prev_l0_died_60_dim_pct'], $bench_data['curr_l0_died_60_dim_pct'], FALSE);?>
		<tr class="even"><td>% Died < 60 DIM</td><td><?php echo $bench_data['prev_l0_died_60_dim_pct']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_l0_died_60_dim_pct']; ?></td><td><?php echo $bench_data['bench_l0_died_60_dim_pct']; ?></td></tr>
	</table>
<?php else: ?>
	<p>There is no benchmark data available at this time.</p>	
<?php endif; ?>
<?php 
if(!empty($page_footer)) echo $page_footer;

function get_trend_symbol($val1, $val2, $high_is_good = TRUE){
	if($val1 == $val2 || !isset($val1)) return '';
	if(($val1 < $val2 && $high_is_good) || ($val1 > $val2 && !$high_is_good)) return '+';
	else return '-';
}
/*
 * 		<?php $s = get_trend_symbol($bench_data['prev_'], $bench_data['curr_'], TRUE);?>
		<tr><td></td><td><?php echo $bench_data['prev_']; ?></td><td class="<?php echo $arr_cls[$s]; ?>"><?php echo $s; ?></td><td><?php echo $bench_data['curr_']; ?></td><td><?php echo $bench_data['bench_']; ?></td></tr>

 */