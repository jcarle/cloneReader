<div class="paginatedList">
<?php 
$fields = $query->list_fields();
?>

	<form method="get" class="tblFilter form-inline navbar-form navbar-inner">
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
				
	<table class="table table-hover table-condensed">
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
					<a href="<?php echo base_url($controller.'/add'); ?>" class="btnAdd btn btn-small">
						<i class="icon-file-alt icon-large"></i>
						Agregar
					</a>
					<span><?php echo $query->foundRows; ?> rows</span>
					<div class="pagination pagination-small pagination-right">
						<ul>
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
	'first_tag_open'		=> '<li>',
	'first_tag_close'		=> '</li>',
	'last_tag_open'			=> '<li>',
	'last_tag_close'		=> '</li>',
	'first_url'				=> '', // Alternative URL for the First Page.
	'cur_tag_open'			=> '<li class="active"><a>',
	'cur_tag_close'			=> '</a></li>',
	'next_tag_open'			=> '<li>',
	'next_tag_close'		=> '</li>',
	'prev_tag_open'			=> '<li>',
	'prev_tag_close'		=> '</li>',
	'num_tag_open'			=> '<li>',
	'num_tag_close'			=> '</li>',
)); 
			
echo $this->pagination->create_links();
?>
						</ul>
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

