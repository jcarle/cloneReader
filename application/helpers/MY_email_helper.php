<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');



function getEmailTemplate($message, $url = null){
	$CI = &get_instance();
	
	$troubleClick = '';
	
	if ($url != null) {
		$troubleClick = '
			<div style=" background: #F5F5F5; border:1px solid #E5E5E5; border-radius: 5px; padding: 10px;">
				'.sprintf($CI->lang->line('Trouble clicking? Copy and paste this URL into your browser: <br/> %s'), $url).'
			</div>';
	}
	
	$result = '
		<div>
			<div style="">
				<div style="border-bottom: 1px solid #E5E5E5;  padding: 10px;">
					<img alt="'.config_item('siteName').'" src="'.base_url('assets/images/logo.png').'" width="151" height="39">
				</div>
				<div  style="padding: 10px;">
					'.$message.'
					'.$troubleClick.'
				</div>
				<div style="background: #F5F5F5; text-align: center; border-top: 1px solid #E5E5E5; padding: 10px;">'.$CI->lang->line('Thank you for using cReader').'</div>
			</div>
		</div>';
	
	return $result;
} 