<?php 
class Entries extends CI_Controller {
	// TODO: implementar la seguridad!
	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Entries_Model', 'Feeds_Model'));
	}
	
	/*
	function __destruct() {
		// TODO: cerrar las conecciones
		$this->Commond_Model->closeDB();
		//$this->db->close();
	}*/
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$feed 	= null;
		$feedId = $this->input->get('feedId');
		if ($feedId != null) {
			$feed = $this->Feeds_Model->get($feedId);
		}
		
		
		$query = $this->Entries_Model->selectToList(config_item('pageSize'), ($page * config_item('pageSize')) - config_item('pageSize'), $this->input->get('filter'), $feedId, $this->input->get('orderBy'), $this->input->get('orderDir'));
		
		$this->load->view('pageHtml', array(
			'view'      => 'includes/crList', 
			'meta'      => array( 'title' => $this->lang->line('Edit entries')),
			'list'       => array(
				'urlList'       => strtolower(__CLASS__).'/listing',
				'urlEdit'       => strtolower(__CLASS__).'/edit/%s',
				'urlAdd'        => strtolower(__CLASS__).'/add',
				'columns'       => array('feedName' => $this->lang->line('Feed'), 'entryTitle' => $this->lang->line('Title'), 'entryUrl' => $this->lang->line('Url'), 'entryDate' => array('class' => 'datetime', 'value' => $this->lang->line('Date'))),
				'data'          => $query->result_array(),
				'foundRows'     => $query->foundRows,
				'showId'        => false,
				'filters'       => array(
					'feedId' => array(
						'type'      => 'typeahead',
						'label'     => $this->lang->line('Feed'),
						'source'    => base_url('feeds/search/'),
						'value'     => array( 'id' => element('feedId', $feed), 'text' => element('feedName', $feed)), 
					),
				),
				'sort' => array(
					'entryId'     => '#',
					'entryDate'   => $this->lang->line('Date'),
				)
			)
		));
	}
	
	function select($page = 1) { // busco nuevas entries
//sleep(5);	
		$userId = (int)$this->session->userdata('userId');
		if ($this->input->post('pushTmpUserEntries') == 'true') {
			$this->Entries_Model->pushTmpUserEntries($userId);
		}
	
		return loadViewAjax(true, $this->Entries_Model->select($userId, (array)json_decode($this->input->post('post'))));
	}

	function selectFilters() {
		return loadViewAjax(true, $this->Entries_Model->selectFilters($this->session->userdata('userId')));
	}
	
	function edit($entryId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($entryId);
		
		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Entries_Model->save($this->input->post());
			}

			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		} 

		$this->load->view('pageHtml', array(
			'view'  => 'includes/crForm', 
			'meta'  => array( 'title' => $this->lang->line('Edit entries')),
			'form'  => populateCrForm($form, $this->Entries_Model->get($entryId, true)),
		));
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		return loadViewAjax($this->Entries_Model->delete($this->input->post('entryId')));	
	}

	function _getFormProperties($entryId) {
		$form = array(
			'frmId'		=> 'frmEntryEdit',
			'rules'		=> array(),
			'fields'	=> array(
				'entryId' => array(
					'type'	=> 'hidden', 
					'value'	=> $entryId,
				),
				'feedId' => array(
					'type' 		=> 'typeahead',
					'label'		=> $this->lang->line('Feed'),
					'source' 	=> base_url('feeds/search/'),
				),
				'entryTitle' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Title'), 
				),				
				'entryUrl' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Url'), 
				),
				'entryContent' => array(
					'type' 		=> 'textarea',
					'label'		=> $this->lang->line('Content'), 
				),
				'entryDate' => array(
					'type' 		=> 'datetime',
					'label'		=> $this->lang->line('Date'), 
				),
			),
		);
		
		if ((int)$entryId > 0) {
			$form['urlDelete'] = base_url('entries/delete/');
		}		
		
		$form['rules'] += array(
			array(
				'field' => 'feedId',
				'label' => $form['fields']['feedId']['label'],
				'rules' => 'trim|required'
			),		 
			array(
				'field' => 'entryTitle',
				'label' => $form['fields']['entryTitle']['label'],
				'rules' => 'trim|required'
			),
			array(
				'field' => 'entryUrl',
				'label' => $form['fields']['entryUrl']['label'],
				'rules' => 'trim|required'
			),
		);
		
		$this->form_validation->set_rules($form['rules']);

		return $form;
	}

	function getAsyncNewsEntries($userId = null) {
		exec(PHP_PATH.'  '.BASEPATH.'../index.php entries/getNewsEntries/'.(int)$userId.' > /dev/null &');
		return;
		
		
		// TODO: revisar como pedir datos para los users logeados
		// este metodo tarda casi un segundo creo; otro crontab ?
		/*$this->load->spark('curl/1.2.1'); 
		$this->curl->create(base_url().'entries/getNewsEntries/'.(int)$userId);
		$this->curl->http_login($this->input->server('PHP_AUTH_USER'), $this->input->server('PHP_AUTH_PW'));
		//$this->curl->options(array(CURLOPT_FRESH_CONNECT => 10, CURLOPT_TIMEOUT => 1));
		$this->curl->execute();*/
	}	
	
	function getNewsEntries($userId = null) {
		// scanea todos los feeds!
		$this->Entries_Model->getNewsEntries($userId);
		
		return loadViewAjax(true, 'ok');
	}
	
	function saveData() {
		$userId		= (int)$this->session->userdata('userId');
		$entries 	= (array)json_decode($this->input->post('entries'), true);
		$tags 		= (array)json_decode($this->input->post('tags'), true);
		
		$this->Entries_Model->saveTmpUsersEntries((int)$userId, $entries);		
		$this->Entries_Model->saveUserTags((int)$userId, $tags);
		
		return loadViewAjax(true, 'ok');
	}

	function addFeed() {
		$feedUrl = $this->input->post('feedUrl');
		$this->load->spark('ci-simplepie/1.0.1/');
		$this->cisimplepie->set_feed_url($feedUrl);
		$this->cisimplepie->enable_cache(false);
		$this->cisimplepie->force_feed(true);
		$this->cisimplepie->init();
		$this->cisimplepie->handle_content_type();
		
		if ($this->cisimplepie->error() != '' ) {
			// Si hay un error, vuelvo a preguntarle a simplepie con force_feed = true; para que traiga el rss por defecto
			$this->cisimplepie->set_feed_url($feedUrl);
			$this->cisimplepie->enable_cache(false);
			$this->cisimplepie->force_feed(false);
			$this->cisimplepie->init();
			$this->cisimplepie->handle_content_type();
			if ($this->cisimplepie->error() != '' ) {
				return loadViewAjax(false, $this->cisimplepie->error());
			}
			$feedUrl = $this->cisimplepie->subscribe_url();
		}


		$userId = (int)$this->session->userdata('userId');
		$feedId = $this->Entries_Model->addFeed($userId, array('feedUrl' => $feedUrl, 'feedSuggest' => true, 'fixLocale' => false));
		
		$this->Feeds_Model->scanFeed($feedId);
		
		// primero guardo todas las novedades en el usuario, ya que puede cambiar el MAX(entryId) al guardar el nuevo, y perderse entries
		$this->Entries_Model->saveEntriesTagByUser($userId);
		// guardo las entries en el user
		$this->Entries_Model->saveUserEntries($userId, $feedId);		

		return loadViewAjax(true, array('feedId' => $feedId));
	}

	function addTag() {
		$tagId = $this->Entries_Model->addTag($this->input->post('tagName'), $this->session->userdata('userId'), $this->input->post('feedId'));

		return loadViewAjax(($tagId > 0), array('tagId' => $tagId));
	}

	function saveUserFeedTag() {
		$result = $this->Entries_Model->saveUserFeedTag((int)$this->session->userdata('userId'), $this->input->post('feedId'), $this->input->post('tagId'), ($this->input->post('append') == 'true'));

		return loadViewAjax(($result === true), ($result === true ? 'ok': $result));
	}
	
	function subscribeFeed() {
		$feedId = $this->input->post('feedId');
		$userId = (int)$this->session->userdata('userId');
		$result = $this->Entries_Model->subscribeFeed($feedId, $userId);

		// primero guardo todas las novedades en el usuario, ya que puede cambiar el MAX(entryId) al guardar el nuevo, y perderse entries
		$this->Entries_Model->saveEntriesTagByUser($userId);
		// guardo las entries en el user
		$this->Entries_Model->saveUserEntries($userId, $feedId);

		return loadViewAjax(true, 'ok');
	}
	
	function unsubscribeFeed() {
		$result = $this->Entries_Model->unsubscribeFeed($this->input->post('feedId'), (int)$this->session->userdata('userId'));

		return loadViewAjax(true, 'ok');
	}
	
	function markAllAsRead() {
		$result = $this->Entries_Model->markAllAsRead((int)$this->session->userdata('userId'), $this->input->post('type'), $this->input->post('id') );

		return loadViewAjax(true, 'ok');
	}	
	
	function updateUserFilters() {
		$this->Entries_Model->updateUserFilters((array)json_decode($this->input->post('post')), (int)$this->session->userdata('userId'));

		return loadViewAjax(true, 'ok');
	}	

	function buildCache($userId = null) {
		if ($userId == null) {
			$userId = (int)$this->session->userdata('userId');
		}
		
		set_time_limit(0);
		$hasNewEntries = $this->Entries_Model->saveEntriesTagByUser($userId);
		$this->getAsyncNewsEntries($userId);
		
		return loadViewAjax(true, array('hasNewEntries' => $hasNewEntries));
	}	
	
	function browseTags() {
		$query = $this->Entries_Model->browseTags($this->session->userdata('userId'));
		
		return loadViewAjax(true, $query);
	}
	
	function browseFeedsByTagId() {
		$query = $this->Entries_Model->browseFeedsByTagId($this->session->userdata('userId'), $this->input->get('tagId'));
		
		return loadViewAjax(true, $query);
	}	
	
	function processTagBrowse() {
		if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }
		
		$this->Entries_Model->processTagBrowse();
	}
	
	function deleteOldEntries() {
		if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }
			
		$this->Feeds_Model->deleteOldEntries();
	}
	
/*	function populateMillionsEntries() {
		$this->Entries_Model->populateMillionsEntries();
	}*/
	
	function shareByEmail($entryId) {
		if ($this->session->userdata('userId') == USER_ANONYMOUS) {
			return errorForbidden();
		}		

		$data = $this->Entries_Model->get($entryId, false);
		if (empty($data)) {
			return error404();
		}
		
		$form = array(
			'frmId'					=> 'frmShareByEmail',
			'buttons'				=> array('<button type="submit" class="btn btn-primary"><i class="fa fa-envelope "></i> '.$this->lang->line('Send').' </button>'),
			'icon'					=> 'fa fa-envelope fa-lg text-primary',
			'modalHideOnSubmit'		=> true,
			'title'					=> sprintf($this->lang->line('Send by email %s'), ' "'.$data['entryTitle'].'" '),
			'fields'				=> array(
				'entryId' => array(
					'type'	=> 'hidden',
					'value'	=> $entryId 
				),
				'userFriendEmail' => array(
					'type'		=> 'typeahead',
					'label'		=> $this->lang->line('For'),
					'source' 	=> base_url('users/searchFriends/'),
					'value'		=> array( 'id' => null, 'text' => null ),
				),
				'shareByEmailComment' => array(
					'type'	=> 'textarea',
					'label'	=> $this->lang->line('Comment'), 
				),
				'sendMeCopy' => array(
					'type'		=> 'checkbox',
					'label'		=> $this->lang->line('Send me a copy'),
					'checked'	=> true, 
				),				
			)
		);
		
		$form['rules'] = array(
			array(
				'field' => 'userFriendEmail',
				'label' => $form['fields']['userFriendEmail']['label'],
				'rules' => 'trim|required|valid_email'
			),
		);		
		
		$this->form_validation->set_rules($form['rules']);
		
		if ($this->input->post() != false) {
			return $this->_saveShareByEmail();
		}

		$this->load->view('includes/crJsonForm', array( 'form' => $form ));
	}
	
	function _saveShareByEmail() {
		if ($this->form_validation->run() == FALSE) {
			return loadViewAjax(false);
		}

		$this->load->model('Users_Model');
		
		
		$userId 				= $this->session->userdata('userId');
		$entryId				= $this->input->post('entryId');
		$userFriendEmail		= $this->input->post('userFriendEmail');
		$sendMeCopy 			= $this->input->post('sendMeCopy')  == 'on';
		$shareByEmailComment	= trim($this->input->post('shareByEmailComment'));
		$userFriendId	 		= $this->Users_Model->saveUserFriend($userId, $userFriendEmail, '');
		$shareByEmailId			= $this->Users_Model->saveSharedByEmail(array(
			'userId'				=> $userId,
			'entryId'				=> $entryId,
			'userFriendId'			=> $userFriendId,
			'shareByEmailComment'	=> $shareByEmailComment,
		));
		$entry 				= $this->Entries_Model->get($entryId, false);
		$user 				= $this->Users_Model->get($userId);
		$userFullName		= $user['userFirstName'].' '.$user['userLastName'];		

		
		$this->load->library('email');
		$this->load->helper('email');

		if ($entry['entryAuthor'] == '') {
			$entryOrigin = sprintf($this->lang->line('From %s'), '<a href="'.$entry['entryUrl'].'" >' . $entry['feedName'] . '</a>');
		}
		else {
			$entryOrigin = sprintf($this->lang->line('From %s by %s'), '<a href="'.$entry['entryUrl'].'" >' . $entry['feedName'] . '</a>', $entry['entryAuthor']);
		}

		$message = $this->load->view('pageEmail',
			array(
				'emailView' 			=> 'email/shareEntry.php',
				'shareByEmailComment' 	=> $shareByEmailComment,
				'userFullName'			=> $userFullName,
				'entry'					=> $entry,
				'entryOrigin'			=> $entryOrigin,
			),
			true);
		//echo $message; die;	

		$this->email->from(config_item('emailFrom'), config_item('siteName'));
		$this->email->to($userFriendEmail); 
		$this->email->reply_To($user['userEmail'], $userFullName);
		if ($sendMeCopy == true) {
			$this->email->cc($user['userEmail']); 
		}
		$this->email->subject(config_item('siteName').' - '.$entry['entryTitle']);
		$this->email->message($message);
		$this->email->send();

		return loadViewAjax(true, array('notification' => $this->lang->line('The email has been sent')));
	}	
}
