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

		$filters = array(
			'search'    => $this->input->get('search'),
			'countryId' => $this->input->get('countryId')
		);

		$query = $this->Testing_Model->selectToList($page, config_item('pageSize'),  $filters);

		$this->load->view('pageHtml', array(
			'view'  => 'includes/crList',
			'meta'  => array( 'title' => lang('Edit testing') ),
			'list'  => array(
				'urlList'    => strtolower(__CLASS__).'/listing',
				'urlEdit'    => strtolower(__CLASS__).'/edit/%s',
				'urlAdd'     => strtolower(__CLASS__).'/add',
				'columns'    => array('testName' => 'Name', 'countryName' => lang('Country'), 'stateName' => 'State'),
				'data'       => $query['data'],
				'foundRows'  => $query['foundRows'],
				'filters'    => array(
					'countryId' => array(
						'type'             => 'dropdown',
						'label'            => lang('Country'),
						'value'            => $this->input->get('countryId'),
						'source'           => $this->Countries_Model->selectToDropdown(),
						'appendNullOption' => true
					)
				)
			)
		));
	}

	function edit($testId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }

		$data = getCrFormData($this->Testing_Model->get($testId, true), $testId);
		if ($data === null) { return error404(); }

		$form = array(
			'frmName'   => 'frmTestingEdit',
			'urlDelete' => base_url('testing/delete/'),
			'fields'  => array(
				'testId' => array(
					'type'  => 'hidden',
					'value' => (int)$testId,
				),
				'testName' => array(
					'type'   => 'text',
					'label'  => 'Name',
				),
				'countryId' => array(
					'type'    => 'dropdown',
					'label'   => lang('Country'),
					'source'  => $this->Countries_Model->selectToDropdown(),
				),
				'stateId' => array(
					'type'        => 'dropdown',
					'label'       => 'State',
					'controller'  => base_url('search/selectStatesByCountryId/'),
					'subscribe'   => array(
						array(
							'field'      => 'countryId',
							'event'      => 'change',
							'callback'   => 'loadDropdown',
							'arguments'  => array(
								'this.getFieldByName(\'countryId\').val()'
							),
							'runOnInit'  => true
						)
					)
				),
				'testRating' => array(
					'type'   => 'raty',
					'label'  => 'Rating',
				),
				'testDesc' => array(
					'type'  => 'textarea',
					'label' => 'Description',
				),
				'testDate' => array(
					'type'  => 'datetime',
					'label' => 'Fecha',
				),
				'testPicture' => array(
					'type'       => 'upload',
					'label'       => lang('Logo'),
					'urlSave'     => base_url('testing/savePicture/'.$testId),
					'urlDelete'   => base_url('testing/deletePicture/'.$testId),
					'isPicture'   => true,
				),
				'testDoc' => array(
					'type'       => 'upload',
					'label'      => lang('pdf'),
					'urlSave'    => base_url('testing/saveDoc/'.$testId),
					'urlDelete'  => base_url('testing/deleteDoc/'.$testId),
				),
				'testIco' => array(
					'type'       => 'upload',
					'label'      => lang('Icon'),
					'isPicture'  => true,
					'disabled'   => true,
				),
				'gallery'    => getCrFormFieldGallery(config_item('entityTypeTesting'), $testId, 'Pictures'),
				'testChilds' => array(
					'type'        => 'subform',
					'label'       => 'childs',
					'controller'  => base_url('testing/selectChildsByTestId/'.$testId),
				),
				'entityLog' => getCrFormFieldEntityLog(config_item('entityTypeTesting'), $testId),
			),
		);

		if ((int)$testId == 0) {
			unset($form['urlDelete']);
			unset($form['fields']['testPicture']);
			unset($form['fields']['testDoc']);
			unset($form['fields']['testIco']);
			unset($form['fields']['gallery']);
			unset($form['fields']['testChilds']);
			unset($form['fields']['entityLog']);
		}

		$form['rules'] = array(
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
			'view' => 'includes/crForm',
			'meta' => array( 'title' => lang('Edit testing') ),
			'form' => populateCrForm($form, $data),
		));
	}

	function add(){
		$this->edit(0);
	}

	function delete() {
		if (! $this->safety->allowByControllerName(__CLASS__.'/edit') ) { return errorForbidden(); }

		return loadViewAjax($this->Testing_Model->delete($this->input->post('testId')));
	}

	function selectChildsByTestId($testId) {
		if (! $this->safety->allowByControllerName('testing/edit') ) { return errorForbidden(); }

		$data = $this->Testing_Model->selectChildsByTestId($testId);
		$data[] = '<tr><td colspan="3">asdf asdf asdf</td></tr>';

		$list = array(
			'controller' => strtolower(__CLASS__).'/popupTestingChilds/'.$testId.'/',
			'data'       => $data,
			'columns'    => array(
				'testChildName' => 'Name',
				'countryName'   => lang('Country'),
				'testChildDate' => array('class' => 'datetime', 'value' => lang('Date')) ),
		);

		return loadViewAjax(true, array('list' => $list));
	}

	function popupTestingChilds($testId, $testChildId) {
		if (! $this->safety->allowByControllerName('testing/edit') ) { return errorForbidden(); }

		$data = getCrFormData($this->Testing_Model->getTestChild($testChildId), $testChildId);
		if ($data === null) { return error404(); }

		$form = array(
			'frmName' => 'frmTestChildEdit',
			'title'   => 'Edit test child',
			'fields'  => array(
				'testChildId' => array(
					'type'  => 'hidden',
					'value' => (int)$testChildId
				),
				'testId' => array(
					'type'  => 'hidden',
					'value' => (int)$testId
				),
				'testChildName' => array(
					'type'  => 'text',
					'label' => lang('Name'),
				),
				'countryId' => array(
					'type'   => 'dropdown',
					'label'  => lang('Country'),
					'source' => $this->Countries_Model->selectToDropdown()
				),
				'testChildDate' => array(
					'type'  => 'datetime',
					'label' => lang('Date'),
				),
			),
			'rules' => array(
				array(
					'field' => 'testChildName',
					'label' => lang('Name'),
					'rules' => 'required'
				),
				array(
					'field' => 'testChildDate',
					'label' => lang('Date'),
					'rules' => 'required'
				),
			)
		);

		$price      = array('name' => 'testChildPrice',    'label' => lang('Price'), );
		$exchange  = array('name' => 'testChildExchange',  'label' => lang('Exchange rate'), );

		$form['fields'] += getCrFormFieldMoney(
			$price,
			array('name' => 'currencyId',           'label' => lang('Currency'), ),
			$exchange,
			array('name' => 'testChildTotalPrice',  'label' => 'Total')
		);

		$form['rules'] = array_merge($form['rules'], getCrFormValidationFieldMoney($price, $exchange));

		if ((int)$testChildId > 0) {
			$form['urlDelete'] = base_url('testing/deleteTestChild/');

			$form['fields']['testChildsUsers'] = array(
				'type'        => 'subform',
				'label'       => 'Users',
				'controller'  => base_url('testing/selectUsersByTestChildId/'.$testChildId),
			);
		}

		$this->form_validation->set_rules($form['rules']);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Testing_Model->saveTestingChilds($this->input->post());
				$this->Commond_Model->saveEntityLog(config_item('entityTypeTesting'), $testId);
			}

			return loadViewAjax($code);
		}

		return $this->load->view('includes/crJsonForm', array( 'form' => populateCrForm($form, $data) ));
	}


	function selectUsersByTestChildId($testChildId) {
		if (! $this->safety->allowByControllerName('testing/edit') ) { return errorForbidden(); }

		$list = array(
			'controller' => strtolower(__CLASS__).'/popupTestChildUser/'.$testChildId.'/',
			'data'       => $this->Testing_Model->selectUsersByTestChildId($testChildId),
			'columns'    => array(
				'userFirstName' => 'Nombre',
				'userLastName'  => 'Apellido',
				'userEmail'     => 'Email',
			),
		);

		return loadViewAjax(true, array('list' => $list));
	}

	function popupTestChildUser($testChildId, $userId) {
		if (! $this->safety->allowByControllerName('testing/edit') ) { return errorForbidden(); }

		$data = getCrFormData($this->Testing_Model->getTestChild($testChildId), $testChildId);
		if ($data === null) { return error404(); }

		$form = array(
			'frmName' => 'frmTestChildUserEdit',
			'title'   => 'Edit user',
			'fields'  => array(
				'testChildId' => array(
					'type'  => 'hidden',
					'value' => (int)$testChildId
				),
				'currentUserId' => array(
					'type'  => 'hidden',
					'value' => (int)$userId
				),
				'userId' => array(
					'type'          => 'typeahead',
					'label'         => lang('User'),
					'source'        => base_url('search/users/'),
					'value'         => getEntityToTypeahead(config_item('entityTypeUser').'-'.$userId, 'entityName', false),
					'multiple'      => false,
					'placeholder'   => lang('User')
				)
			),
			'rules' => array(
				array(
					'field' => 'userId',
					'label' => lang('User'),
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

				$testChildId = $this->input->post('testChildId');
				$userId      = $this->input->post('userId');

				if ($this->Testing_Model->exitsTestChildUser($testChildId, $userId) == true) {
					return loadViewAjax(false, 'El usuario ya exite che');
				}

				$this->Testing_Model->deleteTestChildUser($testChildId, $this->input->post('currentUserId'));
				$this->Testing_Model->saveTestChildUser($testChildId, $userId);
				$this->Commond_Model->saveEntityLog(config_item('entityTypeTesting'), $data['testId']);
			}

			return loadViewAjax($code);
		}

		return $this->load->view('includes/crJsonForm', array( 'form' => populateCrForm($form, $data) ));
	}

	function savePicture($testId) {
		if (! $this->safety->allowByControllerName('testing/edit') ) { return errorForbidden(); }

		$this->deletePicture($testId);

		$result  = savePicture(getEntityConfig(config_item('entityTypeTesting'), 'testPicture'));

		if ($result['code'] != true) {
			return loadViewAjax(false, $result['result']);
		}

		$testPictureFileId = $result['fileId'];

		$this->Testing_Model->savePicture($testId, $testPictureFileId);

		$data = $this->Testing_Model->get($testId, true);

		return loadViewAjax(true, $data['testPicture']);
	}

	function deletePicture($testId) {
		if (! $this->safety->allowByControllerName(__CLASS__.'/edit') ) { return errorForbidden(); }

		$this->load->model('Files_Model');

		$data = $this->Testing_Model->get($testId);

		$this->Files_Model->deleteFile(getEntityConfig(config_item('entityTypeTesting'), 'testPicture'), $data['testPictureFileId']);

		return loadViewAjax(true);
	}

	function saveDoc($testId) {
		if (! $this->safety->allowByControllerName('testing/edit') ) { return errorForbidden(); }

		$this->deleteDoc($testId, true);

		$result = saveFile(getEntityConfig(config_item('entityTypeTesting'), 'testDoc'));

		if ($result['code'] != true) {
			return loadViewAjax(false, $result['result']);
		}

		$this->Testing_Model->saveDoc($testId, $result['fileId']);

		$data = $this->Testing_Model->get($testId, true);
		$this->Commond_Model->saveEntityLog(config_item('entityTypeTesting'), $testId);

		return loadViewAjax(true, $data['testDoc']);
	}

	function deleteDoc($testId, $skipSaveEntityLog = false) {
		if (! $this->safety->allowByControllerName(__CLASS__.'/edit') ) { return errorForbidden(); }

		$this->load->model('Files_Model');
		$data = $this->Testing_Model->get($testId);
		if (!empty($data)) {
			$this->Files_Model->deleteFile(getEntityConfig(config_item('entityTypeTesting'), 'testDoc'), $data['testDocFileId']);
		}
		if ($skipSaveEntityLog == false) {
			$this->Commond_Model->saveEntityLog(config_item('entityTypeTesting'), $testId);
		}

		return loadViewAjax(true);
	}
}
