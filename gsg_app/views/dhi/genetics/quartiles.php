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
			<td>$<?php echo $quartile1;?></td>
		</tr>
		<tr>
			<td>Quartile 2</td>
			<td>$<?php echo $quartile2;?></td>
		</tr>
		<tr>
			<td>Quartile 3</td>
			<td>$<?php echo $quartile3;?></td>
		</tr>
		<tr>
			<td>Quartile 4</td>
			<td>$<?php echo $quartile4;?></td>
		</tr>
	</table>
<?php 
	}
?>