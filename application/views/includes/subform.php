<a href="<?php echo base_url($controller.'0'); ?>" class="btn btn-small">
	<i class="icon-plus"></i>
</a>

<table class="table table-hover table-condensed">
	<thead>
		<tr>
<?php
foreach ($columns as $columnName) {
	$class 		= '';
	$columnName	= $columnName;
	if (is_array($columnName)) {
		$class 		= ' class="'.element('class', $columnName).'" ';
		$columnName	= element('value', $columnName);
	}
	echo '		<th '.$class.'>'.$columnName.'</th>';
} 
?>
		</tr>
	</thead>
	<tbody>
<?php 				
if (count($data) == 0) {
	echo '<tr class="warning"><td colspan="'.(count($columns) + 1).'"> No hay resultados </td></tr>';
}

foreach ($data as $row) {
	if (is_array($row)) {
		$id = reset($row);
		echo '<tr href="'.base_url($controller.$id).'">';	
		foreach ($columns as $fieldName => $columnName) {
			$class 	= '';
			if (is_array($columnName)) {
				$class 		= ' class="'.element('class', $columnName).'" ';
			}
			
			echo '	<td '.$class.'>'.$row[$fieldName].'</td>';
		}
		echo '</tr>';
	}
	else {
		echo $row;
	}
}
?>		
	</tbody>
</table>
