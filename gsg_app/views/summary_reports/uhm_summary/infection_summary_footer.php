<tr id="herd-avg">
	<td class="sum-label">Herd 12 Test Avg</td>
	<td><?php if(isset($avgs['avg_scc_cow_cnt'])) echo round($avgs['avg_scc_cow_cnt']); ?></td>
	<td><?php if(isset($avgs['wtd_avg_scc'])) echo round($avgs['wtd_avg_scc']); ?></td>
	<td><?php if(isset($avgs['bulk_tank_scc'])) echo round($avgs['bulk_tank_scc']); ?></td>
	<td><?php if(isset($avgs['high_scc_pct'])) echo $avgs['high_scc_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['new_infection_pct'])) echo $avgs['new_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['first_new_infection_cnt'])) echo round($avgs['first_new_infection_cnt']); ?></td>
	<td><?php if(isset($avgs['11s25_infection_pct'])) echo $avgs['11s25_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['11s26_infection_pct'])) echo $avgs['11s26_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['11s27_infection_pct'])) echo $avgs['11s27_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['11s28_infection_pct'])) echo $avgs['11s28_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['14s25_infection_pct'])) echo $avgs['14s25_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['14s26_infection_pct'])) echo $avgs['14s26_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['14s27_infection_pct'])) echo $avgs['14s27_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['14s28_infection_pct'])) echo $avgs['14s28_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['chronic_cases_cnt'])) echo $avgs['chronic_cases_cnt']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['dry_period_failure'])) echo $avgs['dry_period_failure']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($avgs['l1_avg_linear_score'])) echo $avgs['l1_avg_linear_score']; ?></td>
	<td><?php if(isset($avgs['l4_avg_linear_score'])) echo $avgs['l4_avg_linear_score']; ?></td>
	<td><?php if(isset($avgs['scc_tot_month_milk_loss_lbs'])) echo $avgs['scc_tot_month_milk_loss_lbs']; ?></td>
</tr>
<tr>
	<td class="sum-label">80th Percentile</td>
	<td><?php if(isset($bench['scc_cow_cnt_80th'])) echo $bench['scc_cow_cnt_80th']; ?></td>
	<td><?php if(isset($bench['wtd_avg_scc_80th'])) echo $bench['wtd_avg_scc_80th']; ?></td>
	<td><?php if(isset($bench['bulk_tank_scc_80th'])) echo $bench['bulk_tank_scc_80th']; ?></td>
	<td><?php if(isset($bench['high_cases_80th'])) echo $bench['high_cases_80th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['new_cases_80th'])) echo $bench['new_cases_80th']; ?></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s25_new_infection_80th'])) echo $bench['l1s25_new_infection_80th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s26_new_infection_80th'])) echo $bench['l1s26_new_infection_80th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s27_new_infection_80th'])) echo $bench['l1s27_new_infection_80th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s28_new_infection_80th'])) echo $bench['l1s28_new_infection_80th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s25_new_infection_80th'])) echo $bench['l4s25_new_infection_80th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s26_new_infection_80th'])) echo $bench['l4s26_new_infection_80th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s27_new_infection_80th'])) echo $bench['l4s27_new_infection_80th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s28_new_infection_80th'])) echo $bench['l4s28_new_infection_80th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['chronic_cases_80th'])) echo $bench['chronic_cases_80th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['dry_cure_failure_80th'])) echo $bench['dry_cure_failure_80th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1_linear_score_80th'])) echo $bench['l1_linear_score_80th']; ?></td>
	<td><?php if(isset($bench['l4_linear_score_80th'])) echo $bench['l4_linear_score_80th']; ?></td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class="sum-label">Average</td>
	<td><?php if(isset($bench['avg_scc_cow_cnt'])) echo $bench['avg_scc_cow_cnt']; ?></td>
	<td><?php if(isset($bench['wtd_avg_scc'])) echo $bench['wtd_avg_scc']; ?></td>
	<td><?php if(isset($bench['bulk_tank_scc'])) echo $bench['bulk_tank_scc']; ?></td>
	<td><?php if(isset($bench['avg_high_cases_pct'])) echo $bench['avg_high_cases_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['avg_new_cases_pct'])) echo $bench['avg_new_cases_pct']; ?></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s25_avg_new_infection_pct'])) echo $bench['l1s25_avg_new_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s26_avg_new_infection_pct'])) echo $bench['l1s26_avg_new_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s27_avg_new_infection_pct'])) echo $bench['l1s27_avg_new_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s28_avg_new_infection_pct'])) echo $bench['l1s28_avg_new_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s25_avg_new_infection_pct'])) echo $bench['l4s25_avg_new_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s26_avg_new_infection_pct'])) echo $bench['l4s26_avg_new_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s27_avg_new_infection_pct'])) echo $bench['l4s27_avg_new_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s28_avg_new_infection_pct'])) echo $bench['l4s28_avg_new_infection_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['avg_chronic_cases_pct'])) echo $bench['avg_chronic_cases_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['avg_dry_cure_failure_pct'])) echo $bench['avg_dry_cure_failure_pct']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1_avg_linear_score'])) echo $bench['l1_avg_linear_score']; ?></td>
	<td><?php if(isset($bench['l4_avg_linear_score'])) echo $bench['l4_avg_linear_score']; ?></td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class="sum-label">20th Percentile</td>
	<td><?php if(isset($bench['scc_cow_cnt_20th'])) echo $bench['scc_cow_cnt_20th']; ?></td>
	<td><?php if(isset($bench['wtd_avg_scc_20th'])) echo $bench['wtd_avg_scc_20th']; ?></td>
	<td><?php if(isset($bench['bulk_tank_scc_20th'])) echo $bench['bulk_tank_scc_20th']; ?></td>
	<td><?php if(isset($bench['high_cases_20th'])) echo $bench['high_cases_20th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['new_cases_20th'])) echo $bench['new_cases_20th']; ?></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s25_new_infection_20th'])) echo $bench['l1s25_new_infection_20th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s26_new_infection_20th'])) echo $bench['l1s26_new_infection_20th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s27_new_infection_20th'])) echo $bench['l1s27_new_infection_20th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1s28_new_infection_20th'])) echo $bench['l1s28_new_infection_20th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s25_new_infection_20th'])) echo $bench['l4s25_new_infection_20th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s26_new_infection_20th'])) echo $bench['l4s26_new_infection_20th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s27_new_infection_20th'])) echo $bench['l4s27_new_infection_20th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l4s28_new_infection_20th'])) echo $bench['l4s28_new_infection_20th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['chronic_cases_20th'])) echo $bench['chronic_cases_20th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['dry_cure_failure_20th'])) echo $bench['dry_cure_failure_20th']; ?></td>
	<td>&nbsp;</td>
	<td><?php if(isset($bench['l1_linear_score_20th'])) echo $bench['l1_linear_score_20th']; ?></td>
	<td><?php if(isset($bench['l4_linear_score_20th'])) echo $bench['l4_linear_score_20th']; ?></td>
	<td>&nbsp;</td>
</tr>
</table>