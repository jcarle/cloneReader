<div class="paginatedList">
	<form method="get" class="form-inline navbar-form navbar-inner">
		<fieldset>
			<label class="checkbox">Filtros</label>
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
				<th>
					<input type="checkbox">
				</th>
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
foreach ($data as $row) {
	$id = reset($row);
	echo '
			<tr>
				<td>
					'.form_checkbox('chkDelete', $id).'
					'.anchor($controller.'/edit/'.$id, 'hiden', array('style'=>'display:none')).'
				</td>';	
	foreach ($columns as $fieldName => $columnName) {
		$class 	= '';
		if (is_array($columnName)) {
			$class 		= ' class="'.element('class', $columnName).'" ';
		}
		
		echo '	<td '.$class.'>'.$row[$fieldName].'</td>';
	}
	echo '
			</tr>
	';
}
?>		
		</tbody>
		<tfoot>
			<tr>
				<td colspan="<?php echo count($columns) + 1; ?>">
					<a class="btnDelete btn btn-small btn-danger" >
						<i class="icon-trash icon-large"></i>
						Delete
					</a>
					<a href="<?php echo base_url($controller.'/add'); ?>" class="btnAdd btn btn-small btn-success">
						<i class="icon-file-alt icon-large"></i>
						Agregar
					</a>
					<span><?php echo $foundRows; ?> rows</span>
					<div class="pagination pagination-small pagination-right">
						<ul>
<?php
$this->pagination->initialize(array(
	'first_link'			=> '1',
	'last_link'				=> ceil($foundRows /PAGE_SIZE),
	'uri_segment'			=> 3,
	'base_url'		 		=> current_url().'?filter='.$this->input->get('filter'),	
	'total_rows'			=> $foundRows,
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

