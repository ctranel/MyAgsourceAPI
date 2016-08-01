<tr>
	<td><?php if(isset($test_date)) echo $test_date; ?></td>
	<td><?php echo ($scc_cow_cnt > 0) ? $scc_cow_cnt : '0'; ?></td>
	<td><?php echo ($wtd_avg_scc > 0) ? $wtd_avg_scc : '0'; ?></td>
	<td><?php echo ($l0_bulk_tank_scc > 0) ? $l0_bulk_tank_scc : '0'; ?></td>
	<td><?php echo ($scc_high_cases_pct > 0) ? $scc_high_cases_pct : '0'; ?></td>
	<td><?php echo ($scc_high_cases_cnt > 0) ? $scc_high_cases_cnt : '0'; ?></td>
	<td class = "first-test"><?php echo ($new_infection_pct > 0) ? $new_infection_pct : '0'; ?></td>
	<td class = "first-test"><?php echo (isset($new_infection_cnt) > 0) ? $new_infection_cnt : '0';
		echo '/';
		echo (isset($prev_no_infection_cnt)) ? $prev_no_infection_cnt : '0'; ?></td>
	<td><?php echo ($first_new_infection_cnt > 0) ? $first_new_infection_cnt : '0'; ?></td>
	<td class = "first-test"><?php echo ($l1s25_new_infection_pct > 0) ? $l1s25_new_infection_pct : '0'; ?></td>
	<td class = "first-test"><?php echo ($l1s25_new_infection_cnt > 0) ? $l1s25_new_infection_cnt : '0';
		echo '/';
		echo ($l1s25_prev_no_infection_cnt > 0) ? $l1s25_prev_no_infection_cnt : '0'; ?>
	</td>
	<td class = "new-infection"><?php echo ($l1s26_new_infection_pct > 0) ? $l1s26_new_infection_pct : '0'; ?></td>
	<td class = "new-infection"><?php echo ($l1s26_new_infection_cnt > 0) ? $l1s26_new_infection_cnt : '0'; ?></td>
	<td class = "new-infection"><?php echo ($l1s27_new_infection_pct > 0) ? $l1s27_new_infection_pct : '0'; ?></td>
	<td class = "new-infection"><?php echo ($l1s27_new_infection_cnt > 0) ? $l1s27_new_infection_cnt : '0'; ?></td>
	<td class = "new-infection"><?php echo ($l1s28_new_infection_pct > 0) ? $l1s28_new_infection_pct : '0'; ?></td>
	<td class = "new-infection"><?php echo ($l1s28_new_infection_cnt > 0) ? $l1s28_new_infection_cnt : '0'; ?></td>
	<td class = "first-test"><?php echo ($l4s25_new_infection_pct > 0) ? $l4s25_new_infection_pct : '0'; ?></td>
	<td class = "first-test"><?php echo ($l4s25_new_infection_cnt > 0) ? $l4s25_new_infection_cnt : '0';
		echo '/';
		echo ($l4s25_prev_no_infection_cnt > 0) ? $l4s25_prev_no_infection_cnt : '0'; ?>
	</td>
	<td class = "new-infection"><?php echo ($l4s26_new_infection_pct > 0) ? $l4s26_new_infection_pct : '0'; ?></td>
	<td class = "new-infection"><?php echo ($l4s26_new_infection_cnt > 0) ? $l4s26_new_infection_cnt : '0'; ?></td>
	<td class = "new-infection"><?php echo ($l4s27_new_infection_pct > 0) ? $l4s27_new_infection_pct : '0'; ?></td>
	<td class = "new-infection"><?php echo ($l4s27_new_infection_cnt > 0) ? $l4s27_new_infection_cnt : '0'; ?></td>
	<td class = "new-infection"><?php echo ($l4s28_new_infection_pct > 0) ? $l4s28_new_infection_pct : '0'; ?></td>
	<td class = "new-infection"><?php echo ($l4s28_new_infection_cnt > 0) ? $l4s28_new_infection_cnt : '0'; ?></td>
	<td class = "chronic-infection"><?php echo ($chronic_cases_pct > 0) ? $chronic_cases_pct : '0'; ?></td>
	<td class = "chronic-infection"><?php echo ($chronic_cases_cnt > 0) ? $chronic_cases_cnt : '0'; ?></td>
	<td class = "chronic-infection"><?php echo ($l4_dry_cure_failure_pct > 0) ? $l4_dry_cure_failure_pct : '0'; ?></td>
	<td class = "chronic-infection"><?php echo ($l4_dry_cure_failure_cnt > 0) ? $l4_dry_cure_failure_cnt : '0';
		echo '/';
		echo ($l4_prev_infected_cnt > 0) ? $l4_prev_infected_cnt : '0'; ?>
	</td>
	<td><?php echo ($l1_avg_linear_score > 0) ? $l1_avg_linear_score : '0'; ?></td>
	<td><?php echo ($l4_avg_linear_score > 0) ? $l4_avg_linear_score : '0'; ?></td>
	<td><?php echo ($scc_tot_month_milk_loss_lbs > 0) ? $scc_tot_month_milk_loss_lbs : '0'; ?></td>
</tr>
