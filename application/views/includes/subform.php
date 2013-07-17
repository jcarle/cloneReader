<a href="<?php echo base_url($controller.'0'); ?>" class="btn btn-small">
	<i class="icon-plus"></i>
</a>

<table class="table table-hover table-condensed">
	<thead>
		<tr>
<?php
$fields = $query->list_fields();
 
foreach ($fields as $field) {
	echo '	<td class="sortAsc">'.$field.'</td>';
} 
?>				
		</tr>
	</thead>
	<tbody>
<?php 				
if (count($query->result()) == 0) {
	echo '<tr><td colspan="'.(count($fields) + 1).'"> No hay resultados </td></tr>';
}
foreach ($query->result() as $row) {
	$id = reset($row);
?>	
		<tr href="<?php echo base_url($controller.$id)?>">
<?		
	foreach ($row as $field) {
		$class = '';
		if (is_numeric($field)) {
			$class = ' class="numeric" ';
		}
		$value = str_replace('€', '', str_replace('U$S', '', str_replace('ar$', '', $field))); // TODO: desharkodear!!
		if (is_numeric($value)) {
			$class = ' class="numeric" ';
		}
		
		echo '	<td '.$class.'>
					'.$field.'
				</td>';		
	}
	echo '
		</tr>
	';
}
?>		
	</tbody>
</table>
