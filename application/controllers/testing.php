<?php 
class Testing extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Testing_Model', 'Countries_Model', 'Users_Model'));
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$query = $this->Testing_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter'), $this->input->get('countryId'));
				
		$this->load->view('pageHtml', array(
			'view'			=> 'includes/crList', 
			'title'			=> 'Edit testing',
			'list'			=> array(
				'controller'	=> strtolower(__CLASS__),
				'columns'		=> array('testName' => 'Name', 'countryName' => 'Country', 'stateName' => 'State'),
				'data'			=> $query->result_array(),
				'foundRows'		=> $query->foundRows,
				'filters'		=> array(
					'countryId' => array(
						'type'				=> 'dropdown',
						'label'				=> 'Country', 
						'value'				=> $this->input->get('countryId'),
						'source'			=> $this->Countries_Model->selectToDropdown(),
						'appendNullOption' 	=> true
					)
				)
			)
		));
	}
	
	function edit($testId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = array(
			'frmId'		=> 'frmTestingEdit',
			'fields'	=> array(
				'testId' => array(
					'type' 		=> 'hidden',
					'value'		=> (int)$testId,
				),
				'testName' => array(
					'type'	=> 'text',
					'label'	=> 'Name', 
				),
				'countryId' => array(
					'type'		=> 'dropdown',
					'label'		=> 'Country', 
					'source'	=> $this->Countries_Model->selectToDropdown(),
				),
				'stateId' => array(
					'type'			=> 'dropdown',
					'label'			=> 'State', 
					'controller'	=> base_url('testing/selectStatesByCountryId/'),
					'subscribe'		=> array(
						array(					
							'field' 		=> 'countryId',
							'event'			=> 'change',   
							'callback'		=> 'loadDropdown',
							'arguments'		=> array(
								'this.getFieldByName(\'countryId\').val()'
							),
							'runOnInit'		=> true
						)
					)
				),
				'testRating' => array(
					'type'	=> 'raty',
					'label'	=> 'Rating', 
				),
				'testDesc' => array(
					'type'	=> 'textarea',
					'label'	=> 'Description', 
				),
				'testDate' => array(
					'type'	=> 'datetime',
					'label'	=> 'Fecha', 
				),				
			)
		);
		
		if ((int)$testId > 0) {
			$form['urlDelete'] = base_url('testing/delete/');
			
			$form['fields']['gallery'] = array(
				'type'			=> 'gallery',
				'label'			=> 'Pictures',
				'urlGet' 		=> base_url('files/testing/'.$testId),
				'urlSave' 		=> base_url('files/save'),
				'entityName'	=> 'testing',
				'entityId'		=> $testId
			);
			
			$form['fields']['testChilds'] = array(
				'type'			=> 'subform',
				'label'			=> 'childs', 
				'controller'	=> base_url('testing/selectChildsByTestId/'.$testId),
			);
		}
		
		$form['rules'] 	= array( 
			array(
				'field' => 'testName',
				'label' => $form['fields']['testName']['label'],
				'rules' => 'required'
			)
		);		

		$this->form_validation->set_rules($form['rules']);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Testing_Model->save($this->input->post());
			}
			
			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}
				
		$this->load->view('pageHtml', array(
			'view'		=> 'includes/crForm', 
			'title'		=> 'Edit testing',
			'form'		=> populateCrForm($form, $this->Testing_Model->get($testId)),
		));
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		if (! $this->safety->allowByControllerName('testing/edit') ) { return errorForbidden(); }
		
		return loadViewAjax($this->Testing_Model->delete($this->input->post('testId')));
	}	
	
	function selectStatesByCountryId($countryId) { // TODO: centralizar en otro lado!
		$this->load->model('States_Model');
	
		return $this->load->view('ajax', array(
			'result' => $this->States_Model->selectStatesByCountryId($countryId)
		));
	}

	function selectChildsByTestId($testId) {
		if (! $this->safety->allowByControllerName('testing/edit') ) { return errorForbidden(); }
		
		$data = $this->Testing_Model->selectChildsByTestId($testId);
		$data[] = '<tr><td>asdf asdf asdf</td></tr>';
		
		$list = array(
			'controller'	=> strtolower(__CLASS__).'/popupTestingChilds/'.$testId.'/',
			'columns'		=> array( 
				'testChildName' 	=> 'Name', 
				'countryName' 		=> 'Country', 
				'testChildDate' 	=> array('class' => 'datetime', 'value' => $this->lang->line('Date')) ),
			'data'			=> $data,
		);

		return loadViewAjax(true, array('list' => $list));
	}
	
	
	
	function popupTestingChilds($testId, $testChildId) {
		if (! $this->safety->allowByControllerName('testing/edit') ) { return errorForbidden(); }
		
		$form = array(
			'frmId'		=> 'frmTestChildEdit',
			'isSubForm' => true,
			'title'		=> 'Edit test child',
			'fields'	=> array(
				'testChildId' => array(
					'type' 	=> 'hidden',
					'value'	=> (int)$testChildId
				),
				'testId' => array(
					'type' 	=> 'hidden',
					'value'	=> (int)$testId
				),
				'testChildName' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Name'),
				),				
				'countryId' => array(
					'type'		=> 'dropdown',
					'label'		=> 'Country',
					'source'	=> $this->Countries_Model->selectToDropdown()
				),
				'testChildDate' => array(
					'type'	=> 'datetime',
					'label'	=> $this->lang->line('Date'), 
				),
			),
			'rules' 	=> array(
				array(
					'field' => 'testChildName',
					'label' => $this->lang->line('Name'),
					'rules' => 'required'				
				),			
				array(
					'field' => 'testChildDate',
					'label' => $this->lang->line('Date'),
					'rules' => 'required'
				),
			)
		);
		
		$price 		= array('name' => 'testChildPrice', 		'label' => $this->lang->line('Price'), 	);
		$exchange 	= array('name' => 'testChildExchange',	'label' => $this->lang->line('Exchange rate'), 	);
		
		$form['fields'] += getCrFormFieldMoney(
			$price,
			array('name' => 'currencyId', 				'label' => $this->lang->line('Currency'), ),
			$exchange,
			array('name' => 'testChildTotalPrice', 	'label' => 'Total')
		);
		
		$form['rules'] 		= array_merge($form['rules'], getCrFormValidationFieldMoney($price, $exchange));		
		
		if ((int)$testChildId > 0) {
			$form['urlDelete'] = base_url('testing/deleteTestChild/');
			
			$form['fields']['testChildsUsers'] = array(
				'type'			=> 'subform',
				'label'			=> 'Users', 
				'controller'	=> base_url('testing/selectUsersByTestChildId/'.$testChildId),
			);
		}
		

		$this->form_validation->set_rules($form['rules']);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Testing_Model->saveTestingChilds($this->input->post());
			}

			return loadViewAjax($code);
		}

		return $this->load->view('includes/crJsonForm', array( 'form' => populateCrForm($form, $this->Testing_Model->getTestChild($testChildId)) ));
	}


	function selectUsersByTestChildId($testChildId) {
		if (! $this->safety->allowByControllerName('testing/edit') ) { return errorForbidden(); }


		$list = array(
			'controller'	=> strtolower(__CLASS__).'/popupTestChildUser/'.$testChildId.'/',
			'columns'		=> array( 
				'userFirstName' 	=> 'Nombre', 
				'userLastName' 		=> 'Apellido', 
				'userEmail'			=> 'Email',
			),
			'data'			=> $this->Testing_Model->selectUsersByTestChildId($testChildId),
		);

		return loadViewAjax(true, array('list' => $list));
	}
	
	function popupTestChildUser($testChildId, $userId) {
		if (! $this->safety->allowByControllerName('testing/edit') ) { return errorForbidden(); }
		
		$user 	= null;
		if ((int)$userId != 0) {
			$user = $this->Users_Model->get($userId);
		}		
		
		$form = array(
			'frmId'		=> 'frmTestChildUserEdit',
			'isSubForm' => true,
			'title'		=> 'Edit user',
			'fields'	=> array(
				'testChildId' => array(
					'type' 	=> 'hidden',
					'value'	=> (int)$testChildId
				),
				'currentUserId' => array(
					'type' 	=> 'hidden',
					'value'	=> (int)$userId
				),
				'userId' => array(
					'type' 			=> 'typeahead',
					'label'			=> $this->lang->line('User'),
					'source' 		=> base_url('users/search/'),
					'value'			=> array( 'id' => element('userId', $user), 'text' => element('userFirstName', $user).' '.element('userLastName', $user) ), 
					'multiple'		=> false,
					'placeholder' 	=> $this->lang->line('User')
				)
			),
			'rules' 	=> array(
				array(
					'field' => 'userId',
					'label' => $this->lang->line('User'),
					'rules' => 'required'				
				),			
			)
		);
		
		
		if ((int)$testChildId > 0) {
			$form['urlDelete'] = base_url('testing/deleteTestChild/');
		}
		

		$this->form_validation->set_rules($form['rules']);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
			
				$testChildId 		= $this->input->post('testChildId');
				$userId 			= $this->input->post('userId');
			
				if ($this->Testing_Model->exitsTestChildUser($testChildId, $userId) == true) {
					return loadViewAjax(false, 'El usuario ya exite che');
				}

				$this->Testing_Model->deleteTestChildUser($testChildId, $this->input->post('currentUserId'));
				$this->Testing_Model->saveTestChildUser($testChildId, $userId);
			}

			return loadViewAjax($code);
		}
		
		return $this->load->view('includes/crJsonForm', array( 'form' => populateCrForm($form, $this->Testing_Model->getTestChild($testChildId)) ));
	}	
}
