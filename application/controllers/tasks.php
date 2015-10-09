<?php
class Tasks extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Tasks_Model');
	}

	function index() {
	}

	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }

		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }

		$this->load->model('Tasks_Status_Model');

		$filters = array(
			'search'       => $this->input->get('search'),
			'taskRunning'  => ($this->input->get('taskRunning') === false ? null : $this->input->get('taskRunning')),
		);
		$orders  = array(
			array('orderBy' => $this->input->get('orderBy'), 'orderDir' => $this->input->get('orderDir') ),
		);

		$query = $this->Tasks_Model->selectToList($page, config_item('pageSize'), $filters, $orders );

		$this->load->view('pageHtml', array(
			'view'   => 'includes/crList',
			'meta'   => array( 'title' => lang('Edit tasks') ),
			'list'   => array(
				'urlList'       => strtolower(__CLASS__).'/listing',
				'readOnly'      => true,
				'columns'       => array(
					'taskMethod'        => lang('Method'),
					'taskParams'        => array('value' => lang('Params'), 'class' => 'dotdotdot'),
					'statusTaskName'    => lang('Running'),
					'langName'          => lang('Language'),
					'taskRetries'       => lang('Retries'),
					'taskSchedule'      => array('value' => lang('Schedule date'), 'class' => 'datetime'),
				),
				'data'        => $query['data'],
				'foundRows'   => $query['foundRows'],
				'showId'      => true,
				'filters'     => array(
					'taskRunning' => array(
						'type'              => 'dropdown',
						'label'             => lang('Status'),
						'value'             => $this->input->get('taskRunning'),
						'source'            => $this->Tasks_Status_Model->selectToDropdown(),
						'appendNullOption' => true,
					),
				),
				'sort' => array(
					'taskId'        => lang('#'),
					'taskMethod'    => lang('Method'),
					'taskSchedule'  => lang('Schedule date'),
				)
			)
		));
	}

	/*
	 * Metodo que se llama desde un cronjobs para iniciar  el envio de las tasks_email
	 */
	function sendEmails(){
		set_time_limit(0);
		if(!$this->input->is_cli_request()){return error404();}

		switch (ENVIRONMENT) {
			case 'development':
				$this->config->set_item('base_url', config_item('urlDev'));
				break;
			case 'testing':
				$this->config->set_item('base_url', config_item('urlQa'));
				break;
			case 'production':
				$this->config->set_item('base_url', config_item('urlProd'));
				break;
		}

		$filters = array(
			'taskRunning'  => false,
			'statusTaskId' => config_item('taskPending'),
			'validDate'    => true
		);

		$query   = $this->Tasks_Model->selectToList(1, 100, $filters, array() );
		$rsTasks = $query['data'];
		if(!empty($rsTasks)){
			$this->load->library('SendMails');

			foreach ($rsTasks as $task) {
				$task['taskRunning'] = config_item('taskRunning');
				$this->Tasks_Model->save($task);

				$success = $this->_sendEmail($task);

				if ($success == true) { //Cuando se completo el envio borro la tarea
					$this->Tasks_Model->delete($task['taskId']);
				}
				else { //Sino se envio el email, aumento el contador de reintentos
					if($task['taskRetries'] < config_item('taskRetry')){
						//Cantidad de Reintentos
						$task['taskRunning'] = config_item('taskPending');
						$task['taskRetries'] = $task['taskRetries'] + 1;
						$this->Tasks_Model->save($task);
					}
					else {
						//Cambio el estado a Cancelado
						$task['taskRunning'] = config_item('taskCancel');
						$this->Tasks_Model->save($task);
					}
				}
			}
		}
	}

	/*
	 *
	 * Metodo que ejecuta cada una de las tareas de envio de email, lo llama self::sendEmails
	 * El array que recibe debe tener el indice taskMethod obligatorio
	 * @param array task
	 * @return boolean
	 *
	 */
	function _sendEmail($task) {
		if(!$this->input->is_cli_request()){ return error404(); }

		$return = false;
		if(empty($task) || !is_array($task) || empty($task['taskMethod']) ){
			return $return;
		}

		// Seteo el idioma en que se va a enviar el email
		$this->lang->is_loaded = array();
		$this->session->set_userdata('langId', $task['langId']);
		initLang();

		$taskMethod = $task['taskMethod'];
		$taskParams = $task['taskParams'];

		if (method_exists($this->sendmails, $taskMethod)) {
			if(!empty($taskParams)){
				$return = $this->sendmails->$taskMethod((array)json_decode($taskParams));
			}else{
				$return = $this->sendmails->$taskMethod();
			}
		}

		unset($taskMethod);
		unset($taskParams);
		return $return;
	}
}
