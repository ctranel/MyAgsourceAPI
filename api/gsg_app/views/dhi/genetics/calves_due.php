<?php 
	if (isset($msg)) {
		echo $msg;
	}
	else {
?>
	<table>
		<tr>
			<th>Month</th>
			<th>Count</th>
			<th>Avg $NM</th>
			<th>Month</th>
			<th>Count</th>
			<th>Avg $NM</th>
			<th>Month</th>
			<th>Count</th>
			<th>Avg $NM</th>						
		</tr>

		<?php for ($i=0;$i<3;$i++) { ?>
		
		<tr>
			<td><?php echo $arr_avg[0+$i]['month_name']?></td>
			<td><?php echo $arr_avg[0+$i]['calf_due_cnt']?></td>
			<td><?php echo $arr_avg[0+$i]['avg_net_merit']?></td>
			<td><?php echo $arr_avg[3+$i]['month_name']?></td>
			<td><?php echo $arr_avg[3+$i]['calf_due_cnt']?></td>
			<td><?php echo $arr_avg[3+$i]['avg_net_merit']?></td>
			<td><?php echo $arr_avg[6+$i]['month_name']?></td>
			<td><?php echo $arr_avg[6+$i]['calf_due_cnt']?></td>
			<td><?php echo $arr_avg[6+$i]['avg_net_merit']?></td>
		</tr>

		<?php } ?>
		
	</table>
<?php 
	}
?>