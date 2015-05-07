<?php
/**
 * Guarda javascript inline para imprimirlos todos juntos al final de la page
 * Se pueden agregar scripts en vistas o controllers
 * 
 */

class My_js {
	public $aJs = array();
	
	function __construct() { }
	
	public function add($script) {
		$this->aJs[] = $script;
	}
	
	public function getHtml() {
		return '
			<script type="text/javascript" >
			$(document).ready( function() {
				'.implode(" \n", $this->aJs).'
			});
			</script>';		
	}	
}
