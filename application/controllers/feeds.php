<?php 
class Feeds extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Feeds_Model', 'Status_Model', 'Languages_Model', 'Countries_Model', 'Tags_Model', 'Users_Model'));
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$statusId = $this->input->get('statusId');
		if ($statusId === false) {
			$statusId = '';
		}
		

		$tag 	= null;
		$tagId = $this->input->get('tagId');
		if ($tagId != null) {
			$tag = $this->Tags_Model->get($tagId);
		}
		$user 	= null;
		$userId = $this->input->get('userId');
		if ($userId != null) {
			$user = $this->Users_Model->get($userId);
		}		
		
		$feedSuggest = $this->input->get('feedSuggest') == 'on';		
		
		$query	= $this->Feeds_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter'), $statusId, $this->input->get('countryId'), $this->input->get('langId'), $tagId, $userId, $feedSuggest);
		
		$this->load->view('includes/template', array(
			'view'			=> 'includes/crList', 
			'title'			=> $this->lang->line('Edit feeds'),
			'list'			=> array(
				'controller'	=> strtolower(__CLASS__),
				'columns'		=> array(
					'statusId' 			=> array('class' => 'numeric', 'value' => $this->lang->line('Status')), 
					'feedName' 			=> $this->lang->line('Name'),  
					'feedDescription' 	=> $this->lang->line('Description'),  
					'countryName' 		=> $this->lang->line('Country'),
					'langName' 			=> $this->lang->line('Language'),
					'feedUrl' 			=> $this->lang->line('Url'), 
					'feedLink' 			=> $this->lang->line('Link'),
					'feedLastEntryDate'	=> array('class' => 'datetime', 'value' => $this->lang->line('Last entry')),
					'feedLastScan' 		=> array('class' => 'datetime', 'value' => $this->lang->line('Last update'))
				),
				'foundRows'		=> $query->foundRows,
				'data'			=> $query->result_array(),
				'filters'	=> array(
					'statusId' => array(
						'type'				=> 'dropdown',
						'label'				=> $this->lang->line('Status'),
						'value'				=> $statusId,
						'source'			=> array_to_select($this->Status_Model->select(), 'statusId', 'statusName'),
						'appendNullOption' 	=> true
					),
					'countryId' => array(
						'type'				=> 'dropdown',
						'label'				=> $this->lang->line('Country'),
						'value'				=> $this->input->get('countryId'),
						'source'			=> array_to_select($this->Countries_Model->select(), 'countryId', 'countryName'),
						'appendNullOption' 	=> true
					),
					'langId' => array(
						'type'				=> 'dropdown',
						'label'				=> $this->lang->line('Language'),
						'value'				=> $this->input->get('langId'),
						'source'			=> array_to_select($this->Languages_Model->select(), 'langId', 'langName'),
						'appendNullOption' 	=> true
					),
					'tagId' => array(
						'type' 			=> 'typeahead',
						'label'			=> 'Tags',
						'source' 		=> base_url('feeds/searchTags/'),
						'value'			=> array( 'id' => element('tagId', $tag), 'text' => element('tagName', $tag)), 
						'multiple'		=> false,
						'placeholder' 	=> 'tags'
					),
					'userId' => array(
						'type' 			=> 'typeahead',
						'label'			=> $this->lang->line('User'),
						'source' 		=> base_url('users/search/'),
						'value'			=> array( 'id' => element('userId', $user), 'text' => element('userFirstName', $user).' '.element('userLastName', $user) ), 
						'multiple'		=> false,
						'placeholder' 	=> $this->lang->line('User')
					),					
					'feedSuggest' => array(
						'type' 			=> 'checkbox',
						'label'			=> $this->lang->line('Only feed suggest'),
						'checked'		=> $feedSuggest,
					),
				)
			)
		));
	}
	
	function edit($feedId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($feedId);

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		$code = $this->form_validation->run(); 
		
		if ($this->input->is_ajax_request()) { // save data
			$feedId = $this->Feeds_Model->save($this->input->post());			
			return $this->load->view('ajax', array(
				'code'		=> ($feedId > 0), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Edit feeds'),
			'form'		=> $form,
			'aJs'		=> array('feeds.js')
		));		
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }
				
		return $this->load->view('ajax', array(
			'code'		=> $this->Feeds_Model->delete($this->input->post('feedId')), 
			'result' 	=> validation_errors() 
		));	
	}

	
	function _getFormProperties($feedId) {
		$tags = array();
		$query = $this->Tags_Model->selectByFeedId($feedId);
		foreach ($query as $data) {
			$tags[] = array('id' => $data['tagId'], 'text' => $data['tagName']);
		}		
				
		$data = $this->Feeds_Model->get($feedId);
		
		$form = array(
			'frmId'		=> 'frmFeedEdit',
			'messages' 	=> getCrFormRulesMessages(),
			'rules'		=> array(),
			'fields'	=> array(
				'feedId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('feedId', $data, 0)
				),
				'feedName' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Name'), 
					'value'		=> element('feedName', $data)
				),
				'feedIcon' => array(
					'type'		=> 'logo',
					'label'		=> 'Icon', //$this->lang->line('Icon'), 
					'value'		=> (element('feedIcon', $data) == null ? site_url().'assets/images/default_feed.png' : site_url().'assets/favicons/'.element('feedIcon', $data)), 
					
				),
				'feedDescription' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Description'), 
					'value'		=> element('feedDescription', $data)
				),
				'feedUrl' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Url'), 
					'value'		=> element('feedUrl', $data)
				),
				'feedLink' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Link'), 
					'value'		=> element('feedLink', $data)
				),				
				'countryId' => array(
					'type'				=> 'dropdown',
					'label'				=> $this->lang->line('Country'),
					'value'				=> element('countryId', $data),
					'source'			=> array_to_select($this->Countries_Model->select(), 'countryId', 'countryName'),
					'appendNullOption' 	=> true
				),
				'langId' => array(
					'type'				=> 'dropdown',
					'label'				=> $this->lang->line('Language'),
					'value'				=> element('langId', $data),
					'source'			=> array_to_select($this->Languages_Model->select(), 'langId', 'langName'),
					'appendNullOption' 	=> true
				),
				'feedLastEntryDate' => array(
					'type' 		=> 'datetime',
					'label'		=> $this->lang->line('Last entry'), 
					'value'		=> element('feedLastEntryDate', $data)
				),
				'feedLastScan' => array(
					'type' 		=> 'datetime',
					'label'		=> $this->lang->line('Last update'), 
					'value'		=> element('feedLastScan', $data)
				),					
				'statusId' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Status'), 
					'value'		=> element('statusId', $data),
					'disabled'	=> 'disabled'
				),
				'aTagId' => array(
					'type' 			=> 'typeahead',
					'label'			=> 'Tags',
					'source' 		=> base_url('tags/search/'),
					'value'			=> $tags,
					'multiple'		=> true,
					'placeholder' 	=> 'tags'
				),
				'feedSuggest' => array(
					'type' 			=> 'checkbox',
					'label'			=> sprintf($this->lang->line('Show in "%s" tag?'), $this->lang->line('@tag-browse')),
					'checked'		=> element('feedSuggest', $data),
				),
				'fixLocale' => array(
					'type' 			=> 'checkbox',
					'label'			=> sprintf($this->lang->line('Fix language')),
					'checked'		=> element('fixLocale', $data),
				),
				
			),
		);

		$form['buttons'] = array();
		$form['buttons'][] = '<button type="button" class="btn btn-default" onclick="$.goToUrl($.base64Decode($.url().param(\'urlList\')));"><i class="icon-arrow-left"></i> '.$this->lang->line('Back').' </button> ';
		if ((int)$feedId > 0) {
			$form['buttons'][] = '<button type="button" class="btn btn-danger" ><i class="icon-trash"></i> '.$this->lang->line('Delete').' </button>';
			$form['buttons'][] = '<button type="button" class="btn btn-info btnScan" onclick="$.Feeds.scanFeed('.$feedId.');"><i class="icon-refresh"></i> '.$this->lang->line('Scan').' </button>';
			
			$form['buttons'][] = '<button type="button" class="btn btn-info btnDownloadIcon" onclick="$.Feeds.saveFeedIcon('.$feedId.');"><i class="icon-picture"></i> '.$this->lang->line('Download icon').' </button>';
			
			$form['urlDelete'] = base_url('feeds/delete/');
		}
		$form['buttons'][] = '<button type="submit" class="btn btn-primary" disabled="disabled"><i class="icon-save"></i> '.$this->lang->line('Save').' </button> ';	
		
		$form['rules'] += array( 
			array(
				'field' => 'feedName',
				'label' => $form['fields']['feedName']['label'],
				'rules' => 'required'
			),
			array(
				'field' => 'feedUrl',
				'label' => $form['fields']['feedUrl']['label'],
				'rules' => 'required'
			),
		);
		return $form;
	}

	function scanFeed($feedId, $forceScan = false) {
		// TODO: implementar seguridad! 
		//if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }
				
		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> $this->Feeds_Model->scanFeed($feedId, $forceScan)
		));				
	}
	
	function saveFeedIcon($feedId) {
		if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }
				
		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> $this->Feeds_Model->saveFeedIcon($feedId)
		));				
	}	
	

	function search() { // TODO: implementar la seguridad!
		return $this->load->view('ajax', array(
			'result' 	=> $this->Feeds_Model->search($this->input->get('query'))
		));
	}
	
	function searchTags() { // TODO: implementar la seguridad!
		return $this->load->view('ajax', array(
			'result' 	=> $this->Feeds_Model->searchTags($this->input->get('query'))
		));
	}	
}
