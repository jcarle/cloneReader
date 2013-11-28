<div class="table-responsive">
	<table class="table table-hover">
		<thead>
			<tr class="label-primary">
<?php
foreach ($list['columns'] as $columnName) {
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
if (count($list['data']) == 0) {
	echo '<tr class="warning"><td colspan="'.(count($list['columns']) + 1).'"> No hay resultados </td></tr>';
}

foreach ($list['data'] as $row) {
	if (is_array($row)) {
		$id = reset($row);
		echo '<tr href="'.base_url($list['controller'].$id).'">';	
		foreach ($list['columns'] as $fieldName => $columnName) {
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
</div>
<a href="<?php echo base_url($list['controller'].'0'); ?>" class="btn btn-default btn-sm btnAdd">
	<i class="icon-plus"> </i> <?php echo $this->lang->line('Add'); ?>
</a>