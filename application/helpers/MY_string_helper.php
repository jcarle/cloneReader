<?php
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

/**
 * TODO: documentar
 * 
 * @param $sort      array con los items del dropdown
 * @param $current   item seleccionado
 * @param $seoTag    indica si se hagrega un tag html dentro del link para mejorar el seo
 */
function getHtmlDropdownSort($sort, $current, $seoTag = null) {
	$CI = & get_instance();
	
	if (!isset($sort[$CI->input->get('sort')])) {
		$aTmp    = array_keys($sort);
		$current = $aTmp[0];	
	}
	
	$html = '
		<div class="dropdown">
			<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
				'. sprintf($CI->lang->line('Sort by %s'), strtolower($sort[$current]['label'])).'
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1"> ';

	$seoStart = null;
	$seoEnd   = null;
	if ($seoTag != null) {
		$seoStart = '<'.$seoTag.'>';
		$seoEnd   = '</'.$seoTag.'>';		
	}

	foreach ($sort as $key => $value) {
		$html .= ' <li role="presentation" '.($current == $key ? ' class="active" ' : '').'>
						<a title="'. $value['label'].'" role="menuitem" tabindex="-1" href="'. base_url( uri_string().'?sort='.$key).'">'.$seoStart. $value['label'].$seoEnd.'</a>
					</li>';
	}
				
	$html .= ' </ul> </div>';
		
	return $html;
}

/**
 * TODO: documentar!
 */
function getHtmlGallery($pictures, $alt = 'Picture %s') {
	if (empty($pictures)) {
		return '';
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
		'base_url'              => current_url().'?'.http_build_query($params),
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