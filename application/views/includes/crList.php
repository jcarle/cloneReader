<?php
/**
 * El listado tiene que tener este formato:
 * 
 * $list = array(
 * 		'urlList'		=> 'services/listing', 		// Url del listado
 * 		'urlEdit'		=> 'services/edit/%s', 		// Url para editar el item ( se reemplaza el %s por el id),
 * 		'urlAdd'		=> 'services/add', 			// Url para agregar un item
 * 		'urlDelete'		=> '',						// TODO: falta implementar
 * 		'showCheckbox'	=> false, 					// muestra un checkbox en cada row
 * 		'columns'			=> array(							// array con las columnas, con el formato: $key => $value; se pueden incluir un className para los datetime y los numeric  
 * 			'entityName' 		=> $this->lang->line('Name'),
 * 			'entityDate'		=> array('class' => 'date', 'value' => $this->lang->line('Date'),  
 * 		),
 * 		'data'			=> $data,						// los datos a mostrar en el listado; macheando el mismo key que en la property columns
 * 		'foundRows'		=> $foundRows, 					// cantidad de registros, se usa en la paginación
 * 		'showId'		=> true,						// Indica si muestra el id en el listado
 * 		'filters'		=> array()						// Filtros para el listado, es un array con los fields similar a un crForm
 * 		'sort' 			=> array(),						// Un array con los items por los que se puede ordenar el listado
 * 		'readOnly'		=> false,						// Indica si el listado es de solo lectura ( no son cliqueables los rows, y no muestra el btn add)
 * ); 
 * 
 * classNames:
 * 		date: 		formatea una fecha
 * 		datetime:	formatea una fecha y hora
 * 		numeric:	aliea el texto a la izquierza // TODO: hacer que formatee pasando un par de parametros mas
 * 		dotdotdot:	trunca el texto a y muestra '...' si corresponde 
 * 
 */
 
 
$CI			= &get_instance();
$filters 	= element('filters', $list);
$sort  		= element('sort', $list);
$readOnly 	= element('readOnly', $list, false);
?>
<div class="crList">
	<div class="panel panel-default" >
		<form method="get" class="panel-heading" action="<?php echo base_url($list['urlList']); ?>" id="frmCrList" role="search">
			<div class="btn-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-times" ></i>
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
				<i class="fa fa-sort-amount-asc" ></i>
			</button>	
			<ul class="dropdown-menu pull-right" role="menu">
<?php
foreach ($sort as $key => $value) {
	$params['orderBy'] 	= $key;
	$params['orderDir'] = ($orderDir == 'desc' ? 'asc' : 'desc');
	$icon				= '';
	
	if ($orderBy == $key) {
		$icon = '<i class="fa fa-fw '.($orderDir == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down').'" ></i>';
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
$showCheckbox   = element('showCheckbox', $list);
$showId         = element('showId', $list);
if ($showCheckbox == true) {
	echo '<th class="rowCheckbox"> <input type="checkbox"> </th>';	
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
	$id        = reset($row);
	$urlEdit   = null;
	
	if ($readOnly != true && isset($list['urlEdit'])) {
		$urlEdit   = base_url(sprintf($list['urlEdit'], $id));
	}
	
	echo '<tr data-url-edit="'.$urlEdit.'">';
	if ($showCheckbox == true) {	
		echo '<td class="rowCheckbox">'.form_checkbox('chkDelete', $id).'</td>';
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
if (!isset($list['buttons'])) {
	$list['buttons'] = array();
	if ($showCheckbox == true && isset($list['urlDelete'])) {
		$list['buttons'][] = '<a class="btnDelete btn btn-sm btn-danger" > <i class="fa fa-trash-o fa-lg"></i> '.$CI->lang->line('Delete').' </a>';
	}
	if ($readOnly !== true && isset($list['urlAdd']) ) {
		$list['buttons'][] = '<a href="'.base_url($list['urlAdd']).'" class="btnAdd btn btn-sm btn-success"> <i class="fa fa-file-o fa-fw"></i> '.$CI->lang->line('Add').' </a> ';
	}
}
if (!empty($list['buttons'])) {
	echo implode(' ', $list['buttons']);
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
	'first_link'            => '1',
	'last_link'             => ceil($list['foundRows'] / config_item('pageSize')),
	'uri_segment'           => 3,
	'base_url'              => current_url().'?'.http_build_query($params),
	'total_rows'            => $list['foundRows'],
	'per_page'              => config_item('pageSize'), 
	'num_links'             => 2,
	'page_query_string'     => true,
	'use_page_numbers'      => true,
	'query_string_segment'  => 'page',
	'first_tag_open'        => '<li>',
	'first_tag_close'       => '</li>',
	'last_tag_open'         => '<li>',
	'last_tag_close'        => '</li>',
	'first_url'             => '', // Alternative URL for the First Page.
	'cur_tag_open'          => '<li class="active"><a>',
	'cur_tag_close'         => '</a></li>',
	'next_tag_open'         => '<li>',
	'next_tag_close'        => '</li>',
	'prev_tag_open'         => '<li>',
	'prev_tag_close'        => '</li>',
	'num_tag_open'          => '<li>',
	'num_tag_close'         => '</li>',
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
