<?php
$result = array('pageName' => getPageName());

if (isset($notRefresh)) {
	$result['notRefresh'] = $notRefresh;
}
if (isset($showTitle)) {
	$result['showTitle'] = $showTitle;
}
if (!isset($meta)) {
	$meta = array();
}
$result['meta'] = getMetaByController($meta);

if (!isset($breadcrumb)) {
	$breadcrumb = array();
}
$result['breadcrumb'] = getBreadcrumb($breadcrumb, $result['meta'], isset($skipBreadcrumb) ? $skipBreadcrumb : false);

switch ($view) {
	case 'includes/crList':
		$result['js']   = 'crList';
		$result['list'] = $list;
		break;
	case 'includes/crForm':
		$form  = appendMessagesToCrForm($form);
		$result['js']   = 'crForm';
		$result['form'] = $form;
		break;
	default: 
		$result['html'] = $this->load->view($view, '', true);
		$result['html'].= $this->myjs->getHtml();  
}


return $this->load->view('json', array(
	'view'   => null,
	'code'   => true,
	'result' => $result
)); 
