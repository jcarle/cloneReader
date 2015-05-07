<?php
$crSettings = array(
	'tagAll'           => config_item('tagAll'),
	'tagStar'          => config_item('tagStar'),
	'tagHome'          => config_item('tagHome'),
	'tagBrowse'        => config_item('tagBrowse'),
	'feedTimeSave'     => config_item('feedTimeSave'),
	'feedTimeReload'   => config_item('feedTimeReload'),
	'entriesPageSize'  => config_item('entriesPageSize'),
	'feedMaxCount'     => config_item('feedMaxCount'),
);

$this->my_js->add( ' crSettings = $.extend(crSettings, '. json_encode($crSettings) .'); ');
$this->my_js->add( ' cloneReader.init('. $userFilters .'); ');