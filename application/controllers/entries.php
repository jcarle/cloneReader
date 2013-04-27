<?php 
class Entries extends CI_Controller {
	// TODO: implementar la seguridad!
	function __construct() {
		parent::__construct();	
		
		$this->load->model('Entries_Model');
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$this->load->view('includes/template', array(
			'controller'	=> strtolower(__CLASS__),
			'view'			=> 'includes/paginatedList', 
			'title'			=> 'Editar entries',
			'query'			=> $this->Entries_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter')),
			'pagination'	=> $this->pagination
		));
	}
	
	function select($page = 1) {
		// busco nuevas entries
// TODO: mejorar esta parte
//pr(base_url().'entries/getNewsEntries/'.(int)$this->session->userdata('userId'));
//		$this->load->spark('curl/1.2.1'); 
//		$this->curl->simple_get(base_url().'entries/getNewsEntries/'.(int)$this->session->userdata('userId'));
		exec('php '.FCPATH.'index.php entries/getNewsEntries/'.(int)$this->session->userdata('userId').' > /dev/null & ');

		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> $this->Entries_Model->select((array)json_decode($this->input->post('post'))),
		));
	}

	function selectFeeds() {
		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> $this->Entries_Model->selectFeeds(),
		));
	}
	
	function edit($entryId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$form = $this->_getFormProperties($entryId);

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		$code = $this->form_validation->run(); 
		
		if ($this->input->is_ajax_request()) { // save data			
			return $this->load->view('ajax', array(
				'code'		=> $this->Entries_Model->save($this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/formValidation', 
			'title'		=> 'Editar Entries',
			'form'		=> $form	  
		));		
	}

	function add(){
		$this->edit(-1);
	}
	
	function _getFormProperties($entryId) {
		$data = $this->Entries_Model->get($entryId);
		
		$form = array(
			'frmId'		=> 'frmEntryEdit',
			'messages' 	=> getRulesMessages(),
			'rules'		=> array(),
			'fields'	=> array(
				'entryId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('entryId', $data, -1)
				),
				'entryTitle' => array(
					'type'		=> 'text',
					'label'		=> 'Title', 
					'value'		=> element('entryTitle', $data)
				),				
				'entryUrl' => array(
					'type' 		=> 'text',
					'label'		=> 'Url', 
					'value'		=> element('entryUrl', $data)
				),
				'entryContent' => array(
					'type' 		=> 'textarea',
					'label'		=> 'Content', 
					'value'		=> element('entryContent', $data)
				),				
			), 		
		);
		
		$form['rules'] += array( 
			array(
				'field' => 'entryTitle',
				'label' => 'Title',
				'rules' => 'required'
			),
			array(
				'field' => 'entryUrl',
				'label' => 'Url',
				'rules' => 'required'
			),
		);

		return $form;		
	}
	
	function getNewsEntries($userId = null) {
		// scanea todos los feeds!
		$this->Entries_Model->getNewsEntries($userId);
		
		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> 'ok',
		));				
	}
	
	function saveData() {
		$entries 	= (array)json_decode($this->input->post('entries'), true);
		$tags 		= (array)json_decode($this->input->post('tags'), true);
		
		$this->Entries_Model->saveUserEntries((int)$this->session->userdata('userId'), $entries);		
		$this->Entries_Model->saveUserTags((int)$this->session->userdata('userId'), $tags);
		
		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> 'ok',
		));		
	}

	function addFeed() {
		$this->load->spark('ci-simplepie/1.0.1/');
		$this->cisimplepie->set_feed_url($this->input->post('feedUrl'));
		$this->cisimplepie->enable_cache(false);
		$this->cisimplepie->init();
		$this->cisimplepie->handle_content_type();
		if ($this->cisimplepie->error() != '' ) {
			return $this->load->view('ajax', array(
				'code'		=> false,
				'result' 	=> $this->cisimplepie->error(),
			));			
		}

		
		$result = $this->Entries_Model->addFeed($this->input->post('feedUrl'), $this->session->userdata('userId'));

		$this->Entries_Model->getNewsEntries((int)$this->session->userdata('userId'));

		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> $result,
		));
	}

	function addTag() {
		$result = $this->Entries_Model->addTag($this->input->post('tagName'), $this->session->userdata('userId'), $this->input->post('feedId'));

		return $this->load->view('ajax', array(
			'code'		=> (is_array($result)),
			'result' 	=> $result,
		));
	}

	function saveUserFeedTag() {
		$result = $this->Entries_Model->saveUserFeedTag((int)$this->session->userdata('userId'), $this->input->post('feedId'), $this->input->post('tagId'), ($this->input->post('append') == 'true'));

		return $this->load->view('ajax', array(
			'code'		=> ($result === true),
			'result' 	=> ($result === true ? 'ok': $result),
		));
	}
	
	function unsubscribeFeed() {
		$result = $this->Entries_Model->unsubscribeFeed($this->input->post('feedId'), (int)$this->session->userdata('userId'));

		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> 'ok',
		));
	}	


	function migrateFromGReader() {
		$userId 	= 1; // FIXME: harckodeta
		$fileName 	= '/home/jcarle/dev/cloneReader/application/cache/subscriptions.xml';

		$xml = simplexml_load_file($fileName);

		foreach ($xml->xpath('//body/outline') as $tag) {
			if (count($tag->children()) > 0) {
				$tagName = (string)$tag['title'];

				foreach ($tag->children() as $feed) {
					$feedName 	= (string)$feed->attributes()->title;
					$feedUrl 	= (string)$feed->attributes()->xmlUrl;
					$result 	=  $this->Entries_Model->addFeed($feedUrl, $userId);
					$this->Entries_Model->addTag($tagName, $userId, $result['feedId']);
				}
			}

			$feedName 	= (string)$tag->title;
			$feedUrl 	= (string)$tag->xmlUrl;

			$this->Entries_Model->addFeed($feedUrl, $userId);
		}

	}
	
	function migrateStarredFromGReader() {
		$userId = 1; // FIXME: harckodeta
		$fileName = '/home/jcarle/dev/cloneReader/application/cache/starred.json';

		$json = (array)json_decode(file_get_contents($fileName), true);
//vd((array)json_decode($json));
//vd($json);
//die;
//$count = 0;
		foreach ($json['items'] as $data) {
//if ($count > 10) { break; }
//$count++;
			$entryContent = '';
			if (element('summary', $data) != null) {
				$entryContent = $data['summary']['content'];
			}
			else if (element('content', $data) != null) {
				$entryContent = $data['content']['content'];
			}

			$entry = array(
				'entryTitle' 	=> element('title', $data, '(title unknown)'),
				'entryUrl'		=> (string)$data['alternate'][0]['href'],
				'entryAuthor'	=> element('author', $data, null),
				'entryDate'		=> date('Y-m-d H:i:s', $data['published']),
				'entryContent' 	=> (string)$entryContent,
			);
			
//pr($data);			

			$feed = array(
				'feedName'	=> element('title', $data['origin']),
				'feedUrl' 	=> substr($data['origin']['streamId'], 5),
				'feedLink'	=> $data['origin']['htmlUrl']
			);
			
			$result 			= $this->Entries_Model->addFeed($feed['feedUrl'], $userId);
			$entry['feedId'] 	= $result['feedId'];
//pr($entry);			
			$entry['entryId'] 	= $this->Entries_Model->saveEntry($entry);

//pr($feed);        
//pr($entry);
			
			$this->Entries_Model->saveUserEntries((int)$userId, array(array( 'userId' => $userId, 'entryId'	=> $entry['entryId'], 'starred'	=> true,  'entryRead' => true )));
		}

/*
		foreach ($xml->xpath('//body/outline') as $tag) {
			if (count($tag->children()) > 0) {
				$tagName = (string)$tag['title'];

				foreach ($tag->children() as $feed) {
					$feedName 	= (string)$feed->attributes()->title;
					$feedUrl 	= (string)$feed->attributes()->xmlUrl;
					$result 	=  $this->Entries_Model->addFeed($feedUrl, $userId);
					$this->Entries_Model->addTag($tagName, $userId, $result['feedId']);
				}
			}

			$feedName 	= (string)$tag->title;
			$feedUrl 	= (string)$tag->xmlUrl;

			$this->Entries_Model->addFeed($feedUrl, $userId);
		}*/

	}	
}
