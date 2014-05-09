<?php 
	if (isset($msg)) {
		echo $msg;
	}
	else {
?>
	<table>
		<tr>
			<th>Quartile</th>
			<th>Average NM$</th>
		</tr>
		<tr>
			<td>Quartile 1</td>
			<td>$<?php echo $arr_avgs['quartile1'];?></td>
		</tr>
		<tr>
			<td>Quartile 2</td>
			<td>$<?php echo $arr_avg['quartile2'];?></td>
		</tr>
		<tr>
			<td>Quartile 3</td>
			<td>$<?php echo $arr_avg['quartile3'];?></td>
		</tr>
		<tr>
			<td>Quartile 4</td>
			<td>$<?php echo $arr_avg['quartile4'];?></td>
		</tr>
	</table>
<?php 
	}
?>