<?php

/**
 * // TODO: documentar ! y mover de aca tambien!
 * 
 * */
function createSefUrl($base_url, $sefsOrder, $currentFilters, $entityTypeId, $entitySef) {

	$remove = false;
	if (isset($currentFilters[$entityTypeId])) {
		if ($currentFilters[$entityTypeId]['entitySef'] == $entitySef) {
			$remove = true;
		}
	}
	
	$currentFilters[$entityTypeId] = array('entityTypeId' => $entityTypeId, 'entitySef' => $entitySef);
	if ($remove == true) {
		unset($currentFilters[$entityTypeId]);
		if ($entityTypeId == config_item('entityTypeCountry')) {
			unset($currentFilters[config_item('entityTypeState')]);
			unset($currentFilters[config_item('entityTypeCity')]);
		}
		if ($entityTypeId == config_item('entityTypeState')) {
			unset($currentFilters[config_item('entityTypeCity')]);
		}
	}

	$sefs = array($base_url);
	foreach ($sefsOrder as $entityTypeId) {
		if (isset($currentFilters[$entityTypeId])) {
			$sefs[] = $currentFilters[$entityTypeId]['entitySef'];
		}
	}
	
	return base_url(implode('/', $sefs));
}
