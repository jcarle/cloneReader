<a href="<?php echo base_url($controller.'-1'); ?>">agregar</a>
<table>
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
		echo '	<td>
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
