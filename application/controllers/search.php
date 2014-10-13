<?php 
// TODO: implementar la seguridad!

class Search extends CI_Controller {

	function __construct() {
		parent::__construct();
	}
	
	function index() {
	}
	
	function users() {
		$this->load->model('Users_Model');
				
		return $this->load->view('json', array(
			'result' 	=> $this->Users_Model->search($this->input->get('query'), $this->input->get('groupId'))
		));
	}
	
	function friends() {
		if ($this->session->userdata('userId') == USER_ANONYMOUS) {
			return errorForbidden();
		}
		
		// FIXME: chapuza; hacer que los fields typeahead permitan agregar datos y validarlos
		// Si el item que ingreso el usuario es un mail valido, lo apendeo a los resultados del autocomplete para que pueda seleccionarlo!
		$this->load->helper('email');
		$this->load->model('Users_Model');
		
		$query 	= $this->input->get('query');
		$result = $this->Users_Model->searchFriends($query, $this->session->userdata('userId'));
		
		
		if (valid_email($query) == true) {
			$result[] = array('id' => $query, 'text' => $query);
		}
		
		return $this->load->view('json', array(
			'result' 	=> $result
		));
	}		
	
	function selectStatesByCountryId($countryId) {
		$this->load->model('States_Model');
	
		return $this->load->view('json', array(
			'result' => $this->States_Model->selectStatesByCountryId($countryId)
		));
	}
	
	function zones($reverse = true) {
		$searchKey = ' +searchZones ';

		return $this->load->view('json', array(
			'result' => $this->Commond_Model->searchEntities($this->input->get('query'), $reverse, $searchKey)
		));
	}

	function feeds() {
		$searchKey = ' +searchFeeds ';
		
		return $this->load->view('json', array(
			'result' => $this->Commond_Model->searchEntities($this->input->get('query'), false, $searchKey, false)
		));
	}
	
	function tags() {
		$searchKey = ' +searchTags ';
		
		if ($this->input->get('onlyWithFeeds') == 'true') {
			$searchKey .= ' +tagHasFeed ';
		}
		
		return $this->load->view('json', array(
			'result' => $this->Commond_Model->searchEntities($this->input->get('query'), false, $searchKey, false)
		));
	}
}

