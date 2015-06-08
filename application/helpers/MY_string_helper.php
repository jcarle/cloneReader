<?php
function pr($value) {
	print_r($value);
}

function vd($value) {
	var_dump($value);
}

function formatCurrency($value, $currencyName = null) {
	$CI = &get_instance();
	
	if ($currencyName == null) {
		$currencyName = config_item('defaultCurrencyName');
	}
	 
	return $currencyName.' '.number_format($value, 2, $CI->lang->line('NUMBER_DEC_SEP'), $CI->lang->line('NUMBER_THOUSANDS_SEP'));
}

function hide_mail($email) {
	if (empty($email)) {
		return '';
	}
	$mail_segments = explode('@', $email);
	$mail_segments[0] = str_repeat('*', strlen($mail_segments[0]));

	return implode('@', $mail_segments);
}

function hide_phone($phone) {
	if (empty($phone)) {
		return '';
	}
	return substr($phone, 0, -4) . '****';
}

function truncate($string, $limit, $break=" ", $pad="...") {
	if(strlen($string) <= $limit){
		return $string;
	}
	
	if(false !== ($breakpoint = strpos($string, $break, $limit))) {
		if($breakpoint < strlen($string) - 1){
			$string = substr($string, 0, $breakpoint) . $pad;
		}
	}
	return $string;
}

function rip_tags($string) { 
    // ----- remove HTML TAGs ----- 
	$string = preg_replace ('/<[^>]*>/', ' ', $string); 

    // ----- remove control characters ----- 
	$string = str_replace("\r", '', $string);    // --- replace with empty space
	$string = str_replace("\n", ' ', $string);   // --- replace with space
	$string = str_replace("\t", ' ', $string);   // --- replace with space

	// ----- remove multiple spaces ----- 
	$string = trim(preg_replace('/ {2,}/', ' ', $string));
    
	return $string; 
}


/**
 * Elimina las palabras reservadas y las palabras muy cortas del string de búsqueda; 
 * 	También llama a la function searchReplace
 * 
 * @param  (string) $search       a buscar
 * @param  (array)  $aSearchKey   array con las $searchKey validas (ej: statusApproved, searchUsers)
 * @param  (bool)   $addPlus      agrega a cada palabra el operador '+' para que la palabra sea obligatoria
 * @param  (bool)   $addWildcard  agrega un asterisco a la ultima palabra, para busquedas parciales
 * @return (array)  devuelve      un array con las palabras a buscar
 * */
function cleanSearchString($search, $aSearchKey, $addPlus = true, $addWildcard = false) {
	$CI     = & get_instance();
	$result = array();
	$search = str_replace('  ', ' ', str_replace( config_item('searchKeys'), '', $search));

	if (substr($search, 0, 1) == '"'  && substr($search, strlen($search)-1, 1) == '"') {
		$result   = array(searchReplace($CI->db->escape_like_str($search)));
	}
	else {
		$aSearch   = explode(' ', $CI->db->escape_like_str($search));
		for($i=0; $i<count($aSearch); $i++) {
			if (strlen($aSearch[$i]) >= 3 )  { // TODO: harckodeta
				$result[] = searchReplace($aSearch[$i]);
			}
		}
	}

	if (empty($result)) {
		return array();
	}
	
	if ($addWildcard == true) {
		$result[count($result)-1] = $result[count($result)-1].'*';
	}
	
	for ($i=0; $i<count($aSearchKey);$i++) {
		$aSearchKey[$i] = '+'.$aSearchKey[$i];
	}
	for ($i=0; $i<count($result);$i++) {
		$result[$i] = ($addPlus == true ? '+' : '').$result[$i];
	}
	
	$result = array_merge($aSearchKey, $result);
	
	return $result;
}

/**
 * 	Reemplaza los caracteres especiales por caracteres buscables
 *  Ver la funcion searchReplace de mysql que hace el mismo REPLACE
 * 
 */
function searchReplace($string) {
	$string = html_entity_decode($string);
	return str_replace(array('+', '-', '&'), array('plus', 'minus', 'ampersand'), $string);
}

/**
 *
 * Devuelve un html con un dropdown que se utiliza para ordenar listados 
 * 
 * @param $sort      array con los items del dropdown, debe tener el formato :array('rating' => array( 'key' => 'label' ))
 * @param $current   item seleccionado
 * @param $seoTag    indica si se hagrega un tag html dentro del link para mejorar el seo
 * @param $className el class que va a tener el dropdown
 */
function getHtmlDropdownSort($sort, $current, $seoTag = null, $className = 'btn btn-default') {
	$CI = & get_instance();
	
	if (!isset($sort[$current])) {
		$aTmp    = array_keys($sort);
		$current = $aTmp[0];	
	}
	
	$html = '
		<div class="dropdown">
			<a href="javascript:void(0);" class="'.$className.' dropdown-toggle" type="button" data-toggle="dropdown">
				'. sprintf($CI->lang->line('Sort by %s'), strtolower($sort[$current]['label'])).'
				<span class="caret"></span>
			</a>
			<ul class="dropdown-menu" role="menu" > ';

	$seoStart = null;
	$seoEnd   = null;
	if ($seoTag != null) {
		$seoStart = '<'.$seoTag.'>';
		$seoEnd   = '</'.$seoTag.'>';		
	}

	foreach ($sort as $key => $value) {
		$html .= ' <li role="presentation" '.($current == $key ? ' class="active" ' : '').'>
						<a title="'. $value['label'].'" data-sort="'.$key.'" role="menuitem" tabindex="-1" href="'. base_url( uri_string().'?sort='.$key).'">'.$seoStart. $value['label'].$seoEnd.'</a>
					</li>';
	}
				
	$html .= ' </ul> </div>';
		
	return $html;
}

/**
 * TODO: documentar!
 */
function getHtmlGallery($pictures, $alt = null) {
	$CI = & get_instance();
	if (empty($pictures)) {
		return '';
	}
	if ($alt == null) {
		$alt = $CI->lang->line('Picture %s');
	}
	
	$html = '';
	$html .= ' <div data-toggle="modal-gallery" data-target="#modal-gallery" class="gallery">';
	foreach ($pictures as $number => $picture) {
		$html .= '	<a class="thumbnail imgCenter" title="'.sprintf($alt, ++$number).'" data-skip-app-link="true" href="'.$picture['urlLarge'].'"> 
					<img src="'.$picture['urlThumbnail'].'" alt="'.sprintf($alt, ++$number).'" />
				</a>';
	}
	$html .= '</div>';
	
	return $html;
}

function getHtmlPagination($foundRows, $pageSize, $params) {
	$CI = & get_instance();
	$CI->load->library('pagination');
	
	$CI->pagination->initialize(array(
		'first_link'            => '1',
		'last_link'             => ceil($foundRows / $pageSize),
		'uri_segment'           => 3,
		'base_url'              => current_url().'?'.http_build_query($params, '', '&amp;'),
		'total_rows'            => $foundRows,
		'per_page'              => $pageSize, 
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

	$links = $CI->pagination->create_links();
	if (!empty($links)) {
		return ' <ul class="pagination"> ' . $links .' </ul> ';
	}
	return '';
}	


function getHtmlFormSearch($isHeader = true) {
	$CI        = & get_instance();
	$frmName   = 'frmSearch';

	$html = '
		<form class="'.($isHeader == true ? ' navbar-form navbar-left' : '').' '.$frmName.'" role="search" action="'.base_url('').'">
			<a href="'.base_url('search').'" class="btn btn-default '.($isHeader == true ? ' visible-sm visible-md ' : ' hide ').'" title="'.$CI->lang->line('search').'"><i class="fa fa-search"></i> </a>
			<div class="form-group '.($isHeader == true ? ' hidden-md hidden-sm ' : '').'" >
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-times" ></i>
					</span>
					<input type="text" class="form-control" name="q" placeholder="'. $CI->lang->line('search').' ..."  value="'.$CI->input->get('q').'" />
					<span class="input-group-btn">
						<button  class="btn btn-default"> '. $CI->lang->line('Search').'</button>
					</span>
				</div>
			</div>
		</form>	';
	
	return $html;
}

function getHtmlAdsense($slotName) {
	$html = '<div class="adsbygoogle" data-slot-id="'.config_item('google-dfp-slotId').'" data-slot-name="'.$slotName.'"> </div>';

	return $html;
}
