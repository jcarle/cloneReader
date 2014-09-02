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
