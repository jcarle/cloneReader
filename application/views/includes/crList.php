<?php
/**
 * El listado tiene que tener este formato:
 *
 * $list = array(
 *      'urlList'       => 'services/listing',      // Url del listado
 *      'urlEdit'       => 'services/edit/%s',      // Url para editar el item ( se reemplaza el %s por el id),
 *      'urlAdd'        => 'services/add',          // Url para agregar un item
 *      'urlDelete'     => '',                      // Url para eliminar elementos desde el listado, se envia un json con el array de los ids seleccionados
 *      'showCheckbox'  => false,                   // muestra un checkbox en cada row
 *      'columns'       => array(                   // array con las columnas, con el formato: $key => $value. También puede ser un array con las properties: {'value': nombre de la columna, 'className': incluye un class para los datetime y los numeric,  'isHtml': permite codigo html en la columna }
 *          'entityName' => $this->lang->line('Name'),
 *          'entityDate' => array('class' => 'date', 'value' => $this->lang->line('Date'), 'isHtml' => true ),
 *      ),
 *      'data'          => (array) $data,          // los datos a mostrar en el listado;
 *            Cada row puede ser un array macheando el mismo key que en la property columns; o un string html del <tr/>
 *            Ej:
 *              $data = array();
 *              foreach ($query['data'] as $row) {
 *                  $data[] = array(
 *                      'icon' => '<img width="16" height="16" src="assets/images/default_feed.png" />',
 *                      'name' => $row['name'],
 *                  );
 *              }
 *              $data[] = '<tr class="success"><td colspan="4"> bla bla</td></tr>';
 *      'foundRows'     => $foundRows, // cantidad de registros, se usa en la paginación
 *      'showId'        => true,       // Indica si muestra el id en el listado
 *      'filters'       => array()     // Filtros para el listado, es un array con los fields similar a un crForm
 *      'sort'          => array(),    // Un array con los items por los que se puede ordenar el listado
 * );
 *
 * classNames:
 * 		date: 		formatea una fecha
 * 		datetime:	formatea una fecha y hora
 * 		numeric:	aliea el texto a la izquierza // TODO: hacer que formatee pasando un par de parametros mas
 * 		dotdotdot:	trunca el texto a y muestra '...' si corresponde
 *
 */


$CI               = &get_instance();
$filters          = element('filters', $list);
$sort             = element('sort', $list);
$list['urlEdit']  = element('urlEdit', $list, null);
$htmlSort         = '';


if ($sort != null) {
	$url = parse_url($_SERVER['REQUEST_URI']);
	parse_str(element('query', $url), $params);
	unset($params['orderBy']);
	unset($params['orderDir']);
	unset($params['page']);

	$aTmp           = array_keys($sort);
	$defaultOrderBy = $aTmp[0];
	$orderBy        = $this->input->get('orderBy');
	if (array_key_exists((string)$orderBy, $sort) === false) {
		$orderBy = $defaultOrderBy;
	}
	$orderDir = $this->input->get('orderDir') == 'desc' ? 'desc' : 'asc';

	$aLi = array();
	foreach ($sort as $key => $value) {
		$params['orderBy'] 	= $key;
		$params['orderDir'] = ($orderDir == 'desc' ? 'asc' : 'desc');
		$icon               = '';

		if ($orderBy == $key) {
			$icon = '<i class="fa fa-fw '.($orderDir == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down').'" ></i>';
		}

		$aLi[] = '<li><a href="'.current_url().'?'.http_build_query($params, '', '&amp;').'">'.$icon.' '.$value.'</a></li>';
	}

	$htmlSort = '
	<div class="btn-group">
		<input type="hidden" name="orderBy"  value="<?php echo $orderBy; ?>" />
		<input type="hidden" name="orderDir" value="<?php echo $orderDir; ?>" />
		<div class="dropdown">
			<button type="button" class="btn btn-default dropdown-toggle dropdown-toggle btnOrder '.(($orderBy != $defaultOrderBy || $orderDir != 'asc') ? ' btn-info ' : '').'" data-toggle="dropdown">
				<i class="fa fa-sort-amount-asc" ></i>
			</button>
			<ul class="dropdown-menu pull-right" role="menu">
			'. implode(' ', $aLi).'
			</ul>
		</div>
	</div>';
}



$showCheckbox   = element('showCheckbox', $list);
$showId         = element('showId', $list);
$aTh            = array();
if ($showCheckbox == true) {
	$aTh[] = '<th class="rowCheckbox"> <input type="checkbox"> </th>';
}
if ($showId == true) {
	$aTh[] = '<th class="numeric"> # </th>';
}

foreach ($list['columns'] as $columnName) {
	$class      = '';
	$columnName	= $columnName;
	if (is_array($columnName)) {
		$class      = ' class="'.element('class', $columnName).'" ';
		$columnName	= element('value', $columnName);
	}
	$aTh[] = ' <th '.$class.'>'.$columnName.'</th>';
}

$aTr = array();
if (count($list['data']) == 0) {
	$aTr[] = '<tr class="warning"><td colspan="'.(count($list['columns']) + ($showCheckbox == true ? 2 : 1)).'"> '.$CI->lang->line('No results').' </td></tr>';
}
foreach ($list['data'] as $row) {
	if (!is_array($row)) {
		$aTr[] = $row;
	}
	else {
		$id        = reset($row);
		$urlEdit   = '';
		$aTd       = array();

		if ($list['urlEdit'] != null) {
			$urlEdit  = ' data-url-edit="'.base_url(sprintf($list['urlEdit'], $id)).'" ';
		}

		if ($showCheckbox == true) {
			$aTd[] = '<td class="rowCheckbox">'.form_checkbox('chkDelete', $id).'</td>';
		}
		if ($showId == true) {
			$aTd[] = '<td class="numeric">'.$id.'</td>';
		}

		foreach ($list['columns'] as $fieldName => $columnName) {
			$class 	= '';
			if (is_array($columnName)) {
				$class = ' class="'.element('class', $columnName).'" ';
			}

			$aTd[] = ' <td '.$class.'>'. (element('isHtml', $list['columns'][$fieldName]) == true ? $row[$fieldName] : htmlentities($row[$fieldName])).'</td>';
		}
		$aTr[] = '<tr '.$urlEdit.'> '.implode(' ', $aTd).' </tr>';
	}
}
?>
<div class="crList">
	<div class="panel panel-default" >
		<form method="get" class="panel-heading" action="<?php echo base_url($list['urlList']); ?>" id="frmCrList" role="search">
			<div class="btn-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-times" ></i>
					</span>
					<?php echo form_input(array('name' => 'search',  'value' => $this->input->get('search'), 'class' => 'form-control', 'placeholder' => $CI->lang->line('search'))); ?>
					<span class="input-group-btn">
						<button type="submit" class="btn btn-default"><?php echo $CI->lang->line('Search'); ?></button>
					</span>
				</div>
			</div>
<?php
if ($filters != null) {
	$this->load->view('includes/crFilterList', array('form' => array('fields' => $filters, 'frmName' => 'crFrmFilterList') ));
}
echo $htmlSort;
?>
		</form>
	</div>

	<div class="table-responsive">
		<table class="table <?php echo ($list['urlEdit'] != false ? ' table-hover ' : ''); ?>">
			<thead>
				<tr class="label-primary">
<?php echo implode(' ', $aTh); ?>
				</tr>
			</thead>
			<tbody>
<?php echo implode(' ', $aTr); ?>
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
	if (isset($list['urlAdd']) ) {
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
<?php
$url = parse_url($_SERVER['REQUEST_URI']);
parse_str(element('query', $url), $params);
unset($params['page']);

echo getHtmlPagination($list['foundRows'], config_item('pageSize'), $params);
?>
			</div>
		</div>
	</div>
</div>

<?php
$this->my_js->add(' $(\'.crList\').crList('. json_encode($list).'); ');
