<?php
if (!empty($page_header)) echo $page_header;
if (!empty($page_heading)) echo heading($page_heading); 
?>
	<?php if (!empty($herd_data)): 
		echo $herd_data;
	endif;	?>
	<p style = "clear:both"></p>
	<?php if (!empty($table_heading)): 
		?><h2 class="block"><?php echo $table_heading; ?></h2><?php
	endif;	?>	
	<?php if (!empty($table_sub_heading)): 
		?><h3 class="block"><?php echo $table_sub_heading; ?></h3><?php
	endif;	?>	
	<?php if (!empty($bench_data['breed_code'])): 
		?><h3 class="block">80th percentile is derived from <?php echo $bench_data['breed_code']; ?> herds<?php if(isset($bench_data['herd_size']) && !empty($bench_data['herd_size'])) echo ' with ' . $bench_data['herd_size'] . ' animals'; ?>.</h3><?php
	endif;
$arr_cls = array('' => 'b-same', '-' => 'b-down', '+' => 'b-up');
if(isset($bench_data) && is_array($bench_data)): ?>
	<table class="tbl"> <!-- 23 rows -->
<?php 
/*		Current Column Order
		Curr
		Trend
		Prev
		80th
*/
//	This array determines column order

	$arr_order = array(
		0 => "Metric",
		1 => "Curr",
		2 => "Trend",
		3 => "Prev",
		4 => "80th"
	);

//	Initialize data arrays

	$arr_Metric = array();
	$arr_Prev = array();
	$arr_Trend = array();
	$arr_Curr = array();
	$arr_80th = array();

	$arr_columns = array();

//	Populate data arrays

	$arr_Metric[] = '<th class="subcat-heading">Metric</th>';
	$arr_Metric[] = '<td>Daily Milk Production</td>';
	$arr_Metric[] = '<td>MLM</td>';
	$arr_Metric[] = '<td>% Fat</td>';
	$arr_Metric[] = '<td>% Pro</td>';
	$arr_Metric[] = '<td>Lact 1 Peak Milk</td>';
	$arr_Metric[] = '<td>Lact 2 Peak Milk</td>';
	$arr_Metric[] = '<td>Lact 3+ Peak Milk</td>';
	$arr_Metric[] = '<td>Lact 1 Linear Score</td>';
	$arr_Metric[] = '<td>Lact 2+ Linear Score</td>';
	$arr_Metric[] = '<td>Weighted Avg SCC</td>';
	$arr_Metric[] = '<td>% New Infections</td>';
	$arr_Metric[] = '<td>% Chronic Infections</td>';
	$arr_Metric[] = '<td>Lact 1 New Infections</td>';
	$arr_Metric[] = '<td>Lact 2+ Dry Period Cures</td>';
	$arr_Metric[] = '<td>Avg TCI&reg;</td>';
	$arr_Metric[] = '<td>Service Rate</td>';
	$arr_Metric[] = '<td>Preg Rate</td>';
	$arr_Metric[] = '<td>Preg Loss Rate</td>';
	$arr_Metric[] = '<td>% Left</td>';
	$arr_Metric[] = '<td>% Left < 60 DIM</td>';
	
	$arr_Prev[] = '<th class="subcat-heading">Prev Test</th>';
	$arr_Prev[] = '<td>'.$bench_data['prev_daily_milk_production'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_management_level_milk'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_fat_pct'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_pro_pct'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_l1_peak_milk'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_l2_peak_milk'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_l3_peak_milk'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_l1_avg_linear_score'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_l4_avg_linear_score'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_wtd_avg_scc'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_l0_new_infection_pct'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_chronic_cases_pct'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_l1_1st_new_infection_pct'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_l4_dry_cow_cured_pct'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_avg_tci'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_service_rate_pct'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_pregnancy_rate_pct'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_pregnancy_loss_pct'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_l0_left_herd_percent'].'</td>';
	$arr_Prev[] = '<td>'.$bench_data['prev_l0_left_60_dim_pct'].'</td>';
	
	$arr_Trend[] = '<th class="subcat-heading">Trend</th>';
	$s = get_trend_symbol($bench_data['prev_daily_milk_production'], $bench_data['curr_daily_milk_production'], TRUE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_management_level_milk'], $bench_data['curr_management_level_milk'], TRUE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_fat_pct'], $bench_data['curr_fat_pct'], TRUE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_pro_pct'], $bench_data['curr_pro_pct'], TRUE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_l1_peak_milk'], $bench_data['curr_l1_peak_milk'], TRUE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_l2_peak_milk'], $bench_data['curr_l2_peak_milk'], TRUE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_l3_peak_milk'], $bench_data['curr_l3_peak_milk'], TRUE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_l1_avg_linear_score'], $bench_data['curr_l1_avg_linear_score'], FALSE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_l4_avg_linear_score'], $bench_data['curr_l4_avg_linear_score'], FALSE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_wtd_avg_scc'], $bench_data['curr_wtd_avg_scc'], FALSE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_l0_new_infection_pct'], $bench_data['curr_l0_new_infection_pct'], FALSE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_chronic_cases_pct'], $bench_data['curr_chronic_cases_pct'], FALSE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_l1_1st_new_infection_pct'], $bench_data['curr_l1_1st_new_infection_pct'], FALSE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_l4_dry_cow_cured_pct'], $bench_data['curr_l4_dry_cow_cured_pct'], TRUE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_avg_tci'], $bench_data['curr_avg_tci'], TRUE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_service_rate_pct'], $bench_data['curr_service_rate_pct'], TRUE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_pregnancy_rate_pct'], $bench_data['curr_pregnancy_rate_pct'], TRUE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_pregnancy_loss_pct'], $bench_data['curr_pregnancy_loss_pct'], FALSE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_l0_left_herd_percent'], $bench_data['curr_l0_left_herd_percent'], FALSE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	$s = get_trend_symbol($bench_data['prev_l0_left_60_dim_pct'], $bench_data['curr_l0_left_60_dim_pct'], FALSE);
	$arr_Trend[] = '<td class="'.$arr_cls[$s].'">'.$s.'</td>';
	
	
	$arr_Curr[] = '<th class="subcat-heading">Curr Test</th>';
	$arr_Curr[] = '<td>'.$bench_data['curr_daily_milk_production'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_management_level_milk'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_fat_pct'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_pro_pct'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_l1_peak_milk'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_l2_peak_milk'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_l3_peak_milk'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_l1_avg_linear_score'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_l4_avg_linear_score'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_wtd_avg_scc'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_l0_new_infection_pct'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_chronic_cases_pct'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_l1_1st_new_infection_pct'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_l4_dry_cow_cured_pct'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_avg_tci'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_service_rate_pct'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_pregnancy_rate_pct'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_pregnancy_loss_pct'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_l0_left_herd_percent'].'</td>';
	$arr_Curr[] = '<td>'.$bench_data['curr_l0_left_60_dim_pct'].'</td>';
	
	$arr_80th[] = '<th class="subcat-heading">80th %tile</th>';
	$arr_80th[] = '<td>'.$bench_data['bench_daily_milk_production'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_management_level_milk'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_fat_pct'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_pro_pct'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_l1_peak_milk'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_l2_peak_milk'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_l3_peak_milk'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_l1_avg_linear_score'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_l4_avg_linear_score'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_wtd_avg_scc'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_l0_new_infection_pct'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_chronic_cases_pct'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_l1_1st_new_infection_pct'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_l4_dry_cow_cured_pct'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_avg_tci'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_service_rate_pct'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_pregnancy_rate_pct'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_pregnancy_loss_pct'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_l0_left_herd_percent'].'</td>';
	$arr_80th[] = '<td>'.$bench_data['bench_l0_left_60_dim_pct'].'</td>';
	
	//	Add arrays in sorted order to arr_columns
	
	for ($i = 0; $i < count($arr_order); $i++) {
		switch($arr_order[$i]) {
			case 'Metric':
				$arr_columns[] = $arr_Metric;
			break;
			case 'Prev':
				$arr_columns[] = $arr_Prev;
				break;
			case 'Trend':
				$arr_columns[] = $arr_Trend;
				break;
			case 'Curr':
				$arr_columns[] = $arr_Curr;
				break;
			case '80th':
				$arr_columns[] = $arr_80th;
				break;
			default:
				$arr_columns[] = array();
		}
	}
	
	$body = '';
	
	for ($k = 0; $k < count($arr_Metric); $k++) {
	
		if ($k==0) {
			$body.='<tr>'.PHP_EOL;
		}
		elseif ($k%2==1) {
			$body.='<tr class="odd">';
		}
		else {
			$body.='<tr class="even">';
		}
		for ($j = 0; $j < count($arr_order); $j++) {
			$body.=$arr_columns[$j][$k].PHP_EOL;
		}
		$body.='</tr>'.PHP_EOL;
	}
		
	echo $body;

?>
	
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