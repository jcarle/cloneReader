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
		
		$query	= $this->Feeds_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter'), $statusId, $this->input->get('countryId'), $this->input->get('langId'), $tagId, $userId, $feedSuggest, $this->input->get('orderBy'), $this->input->get('orderDir') );
		
		$this->load->view('pageHtml', array(
			'view'			=> 'includes/crList', 
			'title'			=> $this->lang->line('Edit feeds'),
			'list'			=> array(
				'showId' => true,
				'controller'	=> strtolower(__CLASS__),
				'columns'		=> array(
					'feedName' 			=> $this->lang->line('Name'),  
					'feedDescription' 	=> $this->lang->line('Description'),  
					'statusName' 		=> $this->lang->line('Status'),
					'countryName' 		=> $this->lang->line('Country'),
					'langName' 			=> $this->lang->line('Language'),
					'feedUrl' 			=> $this->lang->line('Url'), 
					'feedLink' 			=> $this->lang->line('Link'),
					'feedLastEntryDate'	=> array('class' => 'datetime', 'value' => $this->lang->line('Last entry')),
					'feedLastScan' 		=> array('class' => 'datetime', 'value' => $this->lang->line('Last update')),
					'feedCountUsers' 	=> array('class' => 'numeric', 'value' => $this->lang->line('Users')),
					'feedCountEntries' 	=> array('class' => 'numeric', 'value' => $this->lang->line('Entries')),
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
				),
				'sort' => array(
					'feedId'			=> '#',
					'feedName'			=> $this->lang->line('Name'),
					'feedLastEntryDate'	=> $this->lang->line('Last entry'),
					'feedLastScan' 		=> $this->lang->line('Last update'),
					'feedCountUsers' 	=> $this->lang->line('Count users'),
					'feedCountEntries' 	=> $this->lang->line('Count entries'),					
				)				
			)
		));
	}
	
	function edit($feedId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($feedId);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$_POST['feedSuggest']		= $this->input->post('feedSuggest') == 'on';
				$_POST['fixLocale'] 		= $this->input->post('fixLocale') == 'on';
				$this->Feeds_Model->save($this->input->post());
			}
			
			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}		 
				
		$this->load->view('pageHtml', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Edit feeds'),
			'form'		=> populateCrForm($form, $this->Feeds_Model->get($feedId, true, true)),
		));		
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }
				
		return loadViewAjax($this->Feeds_Model->delete($this->input->post('feedId')));
	}
	
	function _getFormProperties($feedId) {
		$form = array(
			'frmId'		=> 'frmFeedEdit',
			'rules'		=> array(),
			'fields'	=> array(
				'feedId' => array(
					'type'	=> 'hidden', 
					'value'	=> $feedId
				),
				'feedName' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Name'), 
				),
				'feedIcon' => array(
					'type'		=> 'logo',
					'label'		=> $this->lang->line('Icon'), 
				),
				'feedDescription' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Description'), 
				),
				'feedUrl' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Url'), 
				),
				'feedLink' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Link'), 
				),				
				'countryId' => array(
					'type'				=> 'dropdown',
					'label'				=> $this->lang->line('Country'),
					'source'			=> array_to_select($this->Countries_Model->select(), 'countryId', 'countryName'),
					'appendNullOption' 	=> true
				),
				'langId' => array(
					'type'				=> 'dropdown',
					'label'				=> $this->lang->line('Language'),
					'source'			=> array_to_select($this->Languages_Model->select(), 'langId', 'langName'),
					'appendNullOption' 	=> true
				),
				'feedLastEntryDate' => array(
					'type' 		=> 'datetime',
					'label'		=> $this->lang->line('Last entry'), 
				),
				'feedLastScan' => array(
					'type' 		=> 'datetime',
					'label'		=> $this->lang->line('Last update'), 
				),
				'statusId' => array(
					'type'				=> 'dropdown',
					'label'				=> $this->lang->line('Status'),
					'source'			=> array_to_select($this->Status_Model->select(), 'statusId', 'statusName'),
					'disabled'			=> true
				),
				'aTagId' => array(
					'type' 			=> 'typeahead',
					'label'			=> 'Tags',
					'source' 		=> base_url('tags/search/'),
					'multiple'		=> true,
					'placeholder' 	=> 'tags'
				),
				'feedSuggest' => array(
					'type' 			=> 'checkbox',
					'label'			=> sprintf($this->lang->line('Show in "%s" tag?'), $this->lang->line('@tag-browse')),
				),
				'fixLocale' => array(
					'type' 			=> 'checkbox',
					'label'			=> sprintf($this->lang->line('Fix language')),
				),
				
			),
		);

		$form['buttons'] = array();
		$form['buttons'][] = '<button type="button" class="btn btn-default" onclick="$.goToUrlList();"><i class="fa fa-arrow-left"></i> '.$this->lang->line('Back').' </button> ';
		if ((int)$feedId > 0) {
			
			$form['fields']['feedCountUsers'] = array(
				'type' 		=> 'numeric',
				'label'		=> $this->lang->line('Count users'), 
				'disabled'	=> true,
				'mDec'		=> 0,
			);			
			$form['fields']['feedCountEntries'] = array(
				'type' 		=> 'numeric',
				'label'		=> $this->lang->line('Count entries'), 
				'disabled'	=> true,
				'mDec'		=> 0,
			);
			$form['fields']['feedCountStarred'] = array(
				'type' 		=> 'numeric',
				'label'		=> $this->lang->line('Count starred'), 
				'value'		=> $this->Feeds_Model->countEntriesStarredByFeedId($feedId),
				'disabled'	=> true,
				'mDec'		=> 0,
			);
			$form['fields']['linkViewEntries'] = array(
				'type' 		=> 'link',
				'label'		=> $this->lang->line('View entries'), 
				'value'		=> site_url('entries/listing?feedId='.$feedId),
			);
			
			$form['fields']['linkViewUsers'] = array(
				'type' 		=> 'link',
				'label'		=> $this->lang->line('View users'), 
				'value'		=> site_url('users/listing?feedId='.$feedId),
			);			
			
			$form['buttons'][] = '<button type="button" class="btn btn-danger" ><i class="fa fa-trash-o"></i> '.$this->lang->line('Delete').' </button>';
			$form['buttons'][] = '<button type="button" class="btn btn-info btnScan" onclick="$.Feeds.resetAndScanFeed('.$feedId.');"><i class="icon-refresh"></i> '.$this->lang->line('Scan').' </button>';
			
			$form['buttons'][] = '<button type="button" class="btn btn-info btnDownloadIcon" onclick="$.Feeds.saveFeedIcon('.$feedId.');"><i class="icon-picture"></i> '.$this->lang->line('Download icon').' </button>';
			$form['buttons'][] = '<button type="button" class="btn btn-warning btnDownloadIcon" onclick="$.Feeds.deleteOldEntriesByFeedId('.$feedId.');"><i class="fa fa-times"></i> '.$this->lang->line('Remove old entries').' </button>';			
			
			$form['urlDelete'] = base_url('feeds/delete/');
		}
		$form['buttons'][] = '<button type="submit" class="btn btn-primary" disabled="disabled"><i class="icon-save"></i> '.$this->lang->line('Save').' </button> ';	
		
		$form['rules'] += array( 
			array(
				'field' => 'feedName',
				'label' => $form['fields']['feedName']['label'],
				'rules' => 'trim|required'
			),
			array(
				'field' => 'feedUrl',
				'label' => $form['fields']['feedUrl']['label'],
				'rules' => 'trim|required'
			),
		);
		
		$this->form_validation->set_rules($form['rules']);
		
		return $form;
	}

	function scanFeed($feedId) {
		// TODO: implementar seguridad! 
		//if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }
		
		$this->db->trans_start();		
				
		$this->Feeds_Model->scanFeed($feedId);
		$this->Feeds_Model->updateFeedCounts($feedId);
		
		$this->db->trans_complete();
	}
	
	function resetAndScanFeed($feedId) {
		if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }
		
		$this->db->trans_start();
		
		$this->Feeds_Model->resetFeed($feedId);
		$this->Feeds_Model->scanFeed($feedId);
		$this->Feeds_Model->updateFeedCounts($feedId);
		
		$this->db->trans_complete();
		
		return loadViewAjax(true, true);
	}
	
	function saveFeedIcon($feedId) {
		if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }
				
		return loadViewAjax(true, $this->Feeds_Model->saveFeedIcon($feedId, null, true));
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
	
	function deleteOldEntriesByFeedId($feedId) {
		if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }
		
		return loadViewAjax(true, 'affected rows: '.$this->Feeds_Model->deleteOldEntriesByFeedId($feedId));	
	}
}
