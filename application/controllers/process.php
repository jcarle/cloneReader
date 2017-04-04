<?php
class Process extends CI_Controller {

	function __construct() {
		parent::__construct();

		if ($this->input->is_cli_request()) {
			$this->session->set_userdata('userId', config_item('userCRBoot'));
		}
		else {
			if (!$this->safety->allowByControllerName(__CLASS__)) {
				throw new Exception(' Not Found');
			}
		}

		$this->output->enable_profiler(false);
		$this->db->save_queries = false;

		set_time_limit(0);
		ini_set('memory_limit', '512M');
	}

	function index() {
		if (! $this->safety->allowByControllerName(__CLASS__) ) { return errorForbidden(); }

		$this->load->view('pageHtml', array(
			'view'       => 'process',
			'meta'       => array( 'title' => lang('Process') ),
		));
	}


	function scanAllFeeds($userId = null) {
		$this->load->model(array('Feeds_Model'));
		$this->Feeds_Model->scanAllFeeds($userId);

		return loadViewAjax(true, array('msg' => lang('Data updated successfully')));
	}

	function rescanAll404Feeds() {
		$this->load->model(array('Feeds_Model'));
		$this->Feeds_Model->scanAllFeeds(null, null, true);

		return loadViewAjax(true, array('msg' => lang('Data updated successfully')));
	}

	function scanFeed($feedId) {
		$this->load->model(array('Feeds_Model'));
		$this->db->trans_start();

		if ($this->input->is_cli_request()) {
			echo date("Y-m-d H:i:s").' scan feed '.$feedId."\n";
		}

		$this->Feeds_Model->scanFeed($feedId);
		$this->Feeds_Model->updateFeedCounts($feedId);

		$this->db->trans_complete();
	}

	function deleteOldEntries($feedId = null) {
		$this->load->model('Feeds_Model');
		$this->Feeds_Model->deleteOldEntries($feedId);

		return loadViewAjax(true, array('msg' => lang('Data updated successfully')));
	}

	function processFeedsTags() {
		$this->load->model(array('Feeds_Model'));
		$this->Feeds_Model->processFeedsTags();

		return loadViewAjax(true, array('msg' => lang('Data updated successfully')));
	}

	function saveEntitiesSearch($entityTypeId = null, $onlyUpdates = false) {
		$onlyUpdates        = ($onlyUpdates == 'true');
		$deleteEntitySearch = ($onlyUpdates != true);
		if ($entityTypeId == 'null') {
			$entityTypeId = null;
		}

		if ($entityTypeId == null || $entityTypeId == config_item('entityTypeUser')) {
			$this->load->model('Users_Model');
			$this->Users_Model->saveUsersSearch($deleteEntitySearch, $onlyUpdates);
		}
		if ($entityTypeId == null || $entityTypeId == config_item('entityTypeTag')) {
			$this->load->model('Tags_Model');
			$this->Tags_Model->saveTagsSearch($deleteEntitySearch, $onlyUpdates);
		}
		if ($entityTypeId == null || $entityTypeId == config_item('entityTypeFeed')) {
			$this->load->model('Feeds_Model');
			$this->Feeds_Model->saveFeedsSearch($deleteEntitySearch, $onlyUpdates);
		}
		if ($entityTypeId == null || $entityTypeId == config_item('entityTypeEntry')) {
			$this->load->model('Entries_Model');
			$this->Entries_Model->saveEntriesSearch($deleteEntitySearch, $onlyUpdates);
		}

		return loadViewAjax(true, array('msg' => lang('Data updated successfully')));
	}

	function optimizeTableEntitiesSearch() {
		$this->load->dbutil();
		$this->dbutil->optimize_table('entities_search');
	}
	function doProcessDiffEntityLog() {
		exec(PHP_PATH.'  '.BASEPATH.'../index.php process/processDiffEntityLog > /dev/null &');

		return loadViewAjax(true, array('msg' => lang('Data updated successfully'), 'icon' => 'success'));
	}

	function processDiffEntityLog() {
		$fileName = BASEPATH.'../application/cache/.processDiffEntityLog';
		if (is_file($fileName)) {
			// Puede suceder que en caso de un error desconocido, nunca se llege a completar el proceso y borrar el archivo
			// Si el archivo .processDiffEntityLog es muy antiguo, lo elimino para poder continuar con el proceso
			$maxLock = 1800; // Treinta minutos
			if (time() - filemtime($fileName) >= $maxLock) {
			    @unlink($fileName);
			}
			return;
		}
		file_put_contents($fileName, 'processing');

//sleep(5);
		$this->Commond_Model->processDiffEntityLog();
		@unlink($fileName);
	}

	/*
	 * Metodo que se llama desde un cronjobs para iniciar  el envio de las tasks_email
	 */
	function sendEmails(){
		set_time_limit(0);

		$this->load->model('Tasks_Model');

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

		// TODO: mejorar esta parte; es para vaciar la variable $view que llega a las vistas
		$this->load->view('json', array('view' => null), true);

		return loadViewAjax(true, array('msg' => lang('Task completed successfully'), 'icon' => 'success'));
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
