<?php
function hide_mail($email) {
	$mail_segments = explode('@', $email);
	$mail_segments[0] = str_repeat('*', strlen($mail_segments[0]));

	return implode('@', $mail_segments);
}

function hide_phone($phone) {
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
 */
function getHtmlDropdownSort($sort, $current) {
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

	foreach ($sort as $key => $value) {
		$html .= ' <li role="presentation" '.($current == $key ? ' class="active" ' : '').'>
						<a role="menuitem" tabindex="-1" href="'. base_url( uri_string().'?sort='.$key).'">'. $value['label'].'</a>
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
		$html .= '	<a class="thumbnail imgCenter" data-skip-app-link="true" href="'.$picture['urlLarge'].'"> 
					<img src="'.$picture['urlThumbnail'].'" alt="'.sprintf($alt, ++$number).'" />
				</a>';
	}
	$html .= '</div>';
	
	return $html;
}
