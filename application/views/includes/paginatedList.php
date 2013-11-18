<?php
$CI	= &get_instance();
?>
<div class="paginatedList">
	<div class="panel panel-default" >
		<form method="get" class="panel-heading form-inline" role="search">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="icon-remove" ></i>
					</span>
					<?php echo form_input(array('name' => 'filter',  'value' => $this->input->get('filter'), 'class' => 'form-control', 'placeholder' => $CI->lang->line('search'))); ?>
					<span class="input-group-btn">
						<button type="submit" class="btn btn-default"><?php echo $CI->lang->line('Search'); ?></button>
					</span>
				</div>					
			</div>
		</form>
	</div>
				
	<table class="table table-hover table-condensed">
		<thead>
			<tr>
<?php
$urlDelete 	= element('urlDelete', $list);
$showId 	= element('showId', $list);
if ($urlDelete == true) {
	echo '<th class="checkbox">	<input type="checkbox"> </th>';	
}
if ($showId == true) {
	echo '<th class="numeric"> # </th>';	
}

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
	echo '<tr class="warning"><td colspan="'.(count($list['columns']) + 1).'"> '.$CI->lang->line('No results').' </td></tr>';
}
foreach ($list['data'] as $row) {
	$id = reset($row);
	echo '<tr data-controller="'.base_url($list['controller'].'/edit/'.$id).'">';
	if ($urlDelete == true) {	
		echo '<td class="checkbox">'.form_checkbox('chkDelete', $id).'</td>';
	}
	if ($showId == true) {
		echo '<td class="numeric">'.$id.'</td>';
	}
	
	foreach ($list['columns'] as $fieldName => $columnName) {
		$class 	= '';
		if (is_array($columnName)) {
			$class 		= ' class="'.element('class', $columnName).'" ';
		}
		
		echo '	<td '.$class.'>'.$row[$fieldName].'</td>';
	}
	echo '</tr>';
}
?>		
		</tbody>
	</table>

	<div class="panel panel-default footer">
		<div class="panel-footer">
<?php
if ($urlDelete == true) {
	echo '<a class="btnDelete btn btn-sm btn-danger" > <i class="icon-trash icon-large"></i> '.$CI->lang->line('Delete').' </a>';
}
?>
			<a href="<?php echo base_url($list['controller'].'/add'); ?>" class="btnAdd btn btn-sm btn-success">
				<i class="icon-file-alt icon-large"></i>
				<?php echo $CI->lang->line('Add'); ?>
			</a>
			<span><?php echo sprintf($CI->lang->line('%s rows'), number_format( $list['foundRows'], 0, $CI->lang->line('NUMBER_DEC_SEP'), $CI->lang->line('NUMBER_THOUSANDS_SEP'))); ?> </span>
			<ul class="pagination pagination-small pagination-right">
<?php
$this->pagination->initialize(array(
	'first_link'			=> '1',
	'last_link'				=> ceil($list['foundRows'] /PAGE_SIZE),
	'uri_segment'			=> 3,
	'base_url'		 		=> current_url().'?filter='.$this->input->get('filter'),	
	'total_rows'			=> $list['foundRows'],
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
	</div>
</div>

<script>
$(document).ready(function() {
	$('.paginatedList').paginatedList();
});	
</script>

