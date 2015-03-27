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
			<td>$<?php if(isset($quartile1)):
				echo $quartile1;
			endif;
			?></td>
		</tr>
		<tr>
			<td>Quartile 2</td>
			<td>$<?php if(isset($quartile2)):
				echo $quartile2;
			endif;
			?></td>
					</tr>
		<tr>
			<td>Quartile 3</td>
			<td>$<?php if(isset($quartile3)):
				echo $quartile3;
			endif;
			?></td>
					</tr>
		<tr>
			<td>Quartile 4</td>
			<td>$<?php if(isset($quartile4)):
				echo $quartile4;
			endif;
			?></td>
					</tr>
	</table>
<?php 
	}
?>