<div class="paginatedList">
<?php 
	echo anchor($controller.'/add', 'agregar', array('class'=>'btnAdd')); 
	$fields = $query->list_fields();
?>

	<form method="get" class="tblFilter form-inline">
		<fieldset>
			<label>Filtros</label>
			<div class="input-prepend">
				<span class="add-on">
					<i class="icon-remove" ></i>
				</span>
				<?php echo form_input('filter', $this->input->get('filter')); ?>
			</div>
			<input value="Buscar" type="submit" class="btn" />
		</fieldset>
	</form>
				
	<table class="table table-hover">
		<thead>
			<tr>
				<td>
					<input type="checkbox">
				</td>
<?php 
foreach ($fields as $field) {
	echo '		<td>'.$field.'</td>';
} 
?>				
			</tr>
		</thead>
		<tbody>
<?php 				
foreach ($query->result() as $row) {
	$id = reset($row);
	echo '
			<tr>
				<td>
					'.form_checkbox('chkDelete', $id).'
					'.anchor($controller.'/edit/'.$id, 'hiden', array('style'=>'display:none')).'
				</td>';	
		
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
		<tfoot>
			<tr>
				<td colspan="<?php echo count($fields) + 1; ?>">
					<a class="btnDelete btn btn-small" >
						<i class="icon-trash icon-large"></i>
						Delete
					</a>
					<span><?php echo $query->foundRows; ?> rows</span>
					<div class="divPaginacion">
<?php
$this->pagination->initialize(array(
	'first_link'			=> '1',
	'last_link'				=> ceil($query->foundRows /PAGE_SIZE),
	'uri_segment'			=> 3,
	'base_url'		 		=> current_url().'?filter='.$this->input->get('filter'),	
	'total_rows'			=> $query->foundRows,
	'per_page'				=> PAGE_SIZE, 
	'num_links' 			=> 2,
	'page_query_string'		=> true,
	'use_page_numbers'		=> true,
	'query_string_segment' 	=> 'page',
	'first_tag_close'		=> '',
	'last_tag_open'			=> '',
	'last_tag_close'		=> '',
	'first_url'				=> '', // Alternative URL for the First Page.
	'cur_tag_open'			=> '<a class="currentPage">',
	'cur_tag_close'			=> '</a>',
	'next_tag_open'			=> '',
	'next_tag_close'		=> '',
	'prev_tag_open'			=> '',
	'prev_tag_close'		=> '',
	'num_tag_open'			=> '',
)); 
			
echo $this->pagination->create_links();
?>
					</div>
				</td>
			</tr>
		</tfoot>
	</table>
</form>


<script>
$(document).ready(function() {
	$('.paginatedList').paginatedList();
});	
</script>

