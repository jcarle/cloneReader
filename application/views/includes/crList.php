<?php
$CI			= &get_instance();
$filters 	= element('filters', $list);
$sort  		= element('sort', $list);
$readOnly 	= element('readOnly', $list, false);
?>
<div class="crList">
	<div class="panel panel-default" >
		<form method="get" class="panel-heading form-inline" action="<?php echo base_url($list['controller']); ?>" id="frmCrList" role="search">
			<div class="btn-group">
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
<?php
if ($filters != null) {
	$this->load->view('includes/crFilterList', array('form' => array('fields' => $filters, 'frmId' => 'crFrmFilterList') ));			
}

if ($sort != null) {
	$url = parse_url($_SERVER['REQUEST_URI']);
	parse_str(element('query', $url), $params);
	unset($params['orderBy']);
	unset($params['orderDir']);
	unset($params['page']);
	
	$aTmp 			= array_keys($sort);
	$defaultOrderBy = $aTmp[0];	
	
	$orderBy 	= $this->input->get('orderBy');
	if (array_key_exists((string)$orderBy, $sort) === false) {
		$orderBy 	= $defaultOrderBy;
	}
	$orderDir 	= $this->input->get('orderDir') == 'desc' ? 'desc' : 'asc';
?>
	<div class="btn-group">
		<input type="hidden" name="orderBy"  value="<?php echo $orderBy; ?>" />
		<input type="hidden" name="orderDir" value="<?php echo $orderDir; ?>" />
		<div class="dropdown">
			<button type="button" class="btn btn-default dropdown-toggle dropdown-toggle btnOrder <?php if ($orderBy != $defaultOrderBy || $orderDir != 'asc') { echo ' btn-info '; } ?>" type="button" data-toggle="dropdown">
				<i class="icon-sort-by-attributes" ></i>
			</button>	
			<ul class="dropdown-menu pull-right" role="menu">
<?php
foreach ($sort as $key => $value) {
	$params['orderBy'] 	= $key;
	$params['orderDir'] = ($orderDir == 'desc' ? 'asc' : 'desc');
	$icon				= '';
	
	if ($orderBy == $key) {
		$icon = '<i class="'.($orderDir == 'asc' ? 'icon-arrow-up' : 'icon-arrow-down').' icon-fixed-width" ></i>';
	}
	
	echo '<li><a href="'.current_url().'?'.http_build_query($params).'">'.$icon.' '.$value.'</a></li>';
}
?>
			</ul>
		</div>
	</div>
<?php	
}
?>			
		</form>
	</div>
				
	<div class="table-responsive">
		<table class="table <?php echo ($readOnly == false ? ' table-hover ' : ''); ?>">
			<thead>
				<tr class="label-primary">
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
	</div>
	
	<div class="panel panel-default footer">
		<div class="panel-footer row">
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
<?php
if ($urlDelete == true) {
	echo '<a class="btnDelete btn btn-sm btn-danger" > <i class="icon-trash icon-large"></i> '.$CI->lang->line('Delete').' </a>';
}
if ($readOnly !== true) {
	echo '<a href="'.base_url($list['controller'].'/add').'" class="btnAdd btn btn-sm btn-success"> <i class="icon-file-alt icon-large"></i> '.$CI->lang->line('Add').' </a> ';
}
?>				
				<span><?php echo sprintf($CI->lang->line('%s rows'), number_format( $list['foundRows'], 0, $CI->lang->line('NUMBER_DEC_SEP'), $CI->lang->line('NUMBER_THOUSANDS_SEP'))); ?> </span>
			</div>						
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
				<ul class="pagination">
<?php
$url = parse_url($_SERVER['REQUEST_URI']);
parse_str(element('query', $url), $params);
unset($params['page']);

$this->pagination->initialize(array(
	'first_link'			=> '1',
	'last_link'				=> ceil($list['foundRows'] /PAGE_SIZE),
	'uri_segment'			=> 3,
	'base_url'		 		=> current_url().'?'.http_build_query($params),
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
</div>

<script type="text/javascript">
$(document).ready(function() {
	$('.crList').crList(<?php echo json_encode($list); ?>);	
});
</script>
