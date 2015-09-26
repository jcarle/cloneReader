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

		$tag   = null;
		$tagId = $this->input->get('tagId');
		if ($tagId != null) {
			$tag = $this->Tags_Model->get($tagId);
		}
		$user   = null;
		$userId = $this->input->get('userId');
		if ($userId != null) {
			$user = $this->Users_Model->get($userId);
		}

		$feedSuggest = $this->input->get('feedSuggest') == 'on';
		$filters = array(
			'search'      => $this->input->get('search'),
			'statusId'    => $statusId,
			'countryId'   => $this->input->get('countryId'),
			'langId'      => $this->input->get('langId'),
			'tagId'       => $tagId,
			'userId'      => $userId,
			'feedSuggest' => $feedSuggest
		);

		$query = $this->Feeds_Model->selectToList($page, config_item('pageSize'), $filters, array(array('orderBy' => $this->input->get('orderBy'), 'orderDir' => $this->input->get('orderDir'))) );

		$this->load->view('pageHtml', array(
			'view'      => 'includes/crList',
			'meta'      => array('title' => lang('Edit feeds')),
			'list'      => array(
				'showId' => true,
				'urlList'  => strtolower(__CLASS__).'/listing',
				'urlEdit'  => strtolower(__CLASS__).'/edit/%s',
				'urlAdd'   => strtolower(__CLASS__).'/add',
				'columns'  => array(
					'feedName'          => lang('Name'),
					'feedDescription'   => lang('Description'),
					'statusName'        => lang('Status'),
					'countryName'       => lang('Country'),
					'langName'          => lang('Language'),
					'feedUrl'           => lang('Url'),
					'feedLink'          => lang('Link'),
					'feedLastEntryDate' => array('class' => 'datetime', 'value' => lang('Last entry')),
					'feedLastScan'      => array('class' => 'datetime', 'value' => lang('Last update')),
					'feedCountUsers'    => array('class' => 'numeric', 'value' => lang('Users')),
					'feedCountEntries'  => array('class' => 'numeric', 'value' => lang('Entries')),
				),
				'foundRows'  => $query['foundRows'],
				'data'       => $query['data'],
				'filters'    => array(
					'statusId' => array(
						'type'              => 'dropdown',
						'label'             => lang('Status'),
						'value'             => $statusId,
						'source'            => $this->Status_Model->selectToDropdown(),
						'appendNullOption'  => true
					),
					'countryId' => array(
						'type'              => 'dropdown',
						'label'             => lang('Country'),
						'value'             => $this->input->get('countryId'),
						'source'            => $this->Countries_Model->selectToDropdown(),
						'appendNullOption'  => true
					),
					'langId' => array(
						'type'               => 'dropdown',
						'label'              => lang('Language'),
						'value'              => $this->input->get('langId'),
						'source'             => $this->Languages_Model->selectToDropdown(),
						'appendNullOption'   => true
					),
					'tagId' => array(
						'type'          => 'typeahead',
						'label'         => 'Tags',
						'source'        => base_url('search/tags?onlyWithFeeds=true'),
						'value'         => array( 'id' => element('tagId', $tag), 'text' => element('tagName', $tag)),
						'multiple'      => false,
						'placeholder'   => 'tags'
					),
					'userId' => array(
						'type'          => 'typeahead',
						'label'         => lang('User'),
						'source'        => base_url('search/users/'),
						'value'         => array( 'id' => element('userId', $user), 'text' => element('userFirstName', $user).' '.element('userLastName', $user) ),
						'multiple'      => false,
						'placeholder'   => lang('User')
					),
					'feedSuggest' => array(
						'type'    => 'checkbox',
						'label'   => lang('Only feed suggest'),
						'checked' => $feedSuggest,
					),
				),
				'sort' => array(
					'feedId'            => '#',
					'feedName'          => lang('Name'),
					'feedLastEntryDate' => lang('Last entry'),
					'feedLastScan'      => lang('Last update'),
					'feedCountUsers'    => lang('Count users'),
					'feedCountEntries'  => lang('Count entries'),
				)
			)
		));
	}

	function edit($feedId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }

		$data = getCrFormData($this->Feeds_Model->get($feedId, true, true), $feedId);
		if ($data === null) { return error404(); }

		$form = $this->_getFormProperties($feedId);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$_POST['feedSuggest']        = $this->input->post('feedSuggest') == 'on';
				$_POST['fixLocale']          = $this->input->post('fixLocale') == 'on';
				$_POST['feedKeepOldEntries'] = $this->input->post('feedKeepOldEntries') == 'on';
				$this->Feeds_Model->save($this->input->post());
			}

			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}

		$form['fields']['countryId']['source'] = $this->Countries_Model->selectToDropdown();
		$form['fields']['langId']['source']    = $this->Languages_Model->selectToDropdown();
		$form['fields']['statusId']['source']  = $this->Status_Model->selectToDropdown();

		$this->load->view('pageHtml', array(
			'view'   => 'includes/crForm',
			'meta'   => array('title' => lang('Edit feeds') ),
			'form'   => populateCrForm($form, $data)
		));
	}

	function add(){
		$this->edit(0);
	}

	function delete() {
		if (! $this->safety->allowByControllerName(__CLASS__.'/edit') ) { return errorForbidden(); }

		return loadViewAjax($this->Feeds_Model->delete($this->input->post('feedId')));
	}

	function _getFormProperties($feedId) {
		$form = array(
			'frmName' => 'frmFeedEdit',
			'rules'   => array(),
			'fields'  => array(
				'feedId' => array(
					'type'  => 'hidden',
					'value' => $feedId
				),
				'feedName' => array(
					'type'  => 'text',
					'label' => lang('Name'),
				),
				'feedIcon' => array(
					'type'      => 'upload',
					'label'     => lang('Icon'),
					'isPicture' => true,
					'disabled'  => true,
				),
				'feedDescription' => array(
					'type'  => 'text',
					'label' => lang('Description'),
				),
				'feedUrl' => array(
					'type'  => 'text',
					'label' => lang('Url'),
				),
				'feedLink' => array(
					'type'  => 'text',
					'label' => lang('Link'),
				),
				'countryId' => array(
					'type'              => 'dropdown',
					'label'             => lang('Country'),
					'appendNullOption'  => true
				),
				'langId' => array(
					'type'              => 'dropdown',
					'label'             => lang('Language'),
					'appendNullOption'  => true
				),
				'feedLastEntryDate' => array(
					'type'  => 'datetime',
					'label' => lang('Last entry'),
				),
				'feedLastScan' => array(
					'type'  => 'datetime',
					'label' => lang('Last update'),
				),
				'statusId' => array(
					'type'     => 'dropdown',
					'label'    => lang('Status'),
					'disabled' => true
				),
				'aTagId' => array(
					'type'         => 'typeahead',
					'label'        => 'Tags',
					'source'       => base_url('search/tags/'),
					'multiple'     => true,
					'placeholder'  => 'tags',
					'disabled'     => true,
				),
				'feedSuggest' => array(
					'type'   => 'checkbox',
					'label'  => sprintf(lang('Show in "%s" tag?'), lang('@tag-browse')),
				),
				'fixLocale' => array(
					'type'  => 'checkbox',
					'label' => sprintf(lang('Fix language')),
				),
				'feedKeepOldEntries' => array(
					'type'  => 'checkbox',
					'label' => sprintf(lang('Keep old entries')),
				),
			),
		);

		$form['buttons'] = array();
		$form['buttons'][] = '<button type="button" class="btn btn-default" onclick="$.goToUrlList();"><i class="fa fa-arrow-left"></i> '.lang('Back').' </button> ';
		if ((int)$feedId > 0) {

			$form['fields']['feedCountUsers'] = array(
				'type'     => 'numeric',
				'label'    => lang('Count users'),
				'disabled' => true,
				'mDec'     => 0,
			);
			$form['fields']['feedCountEntries'] = array(
				'type'      => 'numeric',
				'label'     => lang('Count entries'),
				'disabled'  => true,
				'mDec'      => 0,
			);
			$form['fields']['feedCountStarred'] = array(
				'type'      => 'numeric',
				'label'     => lang('Count starred'),
				'value'     => $this->Feeds_Model->countEntriesStarredByFeedId($feedId),
				'disabled'  => true,
				'mDec'      => 0,
			);
			$form['fields']['linkViewEntries'] = array(
				'type'      => 'link',
				'label'     => lang('View entries'),
				'value'     => site_url('entries/listing?feedId='.$feedId),
			);

			$form['fields']['linkViewUsers'] = array(
				'type'   => 'link',
				'label'  => lang('View users'),
				'value'  => site_url('users/listing?feedId='.$feedId),
			);

			$form['buttons'][] = '<button type="button" class="btn btn-danger" ><i class="fa fa-trash-o"></i> '.lang('Delete').' </button>';
			$form['buttons'][] = '<button type="button" class="btn btn-info btnScan" onclick="$.Feeds.resetAndScanFeed('.$feedId.');"><i class="fa fa-refresh"></i> '.lang('Scan').' </button>';

			$form['buttons'][] = '<button type="button" class="btn btn-info btnDownloadIcon" onclick="$.Feeds.saveFeedIcon('.$feedId.');"><i class="fa fa-picture-o"></i> '.lang('Download icon').' </button>';
			$form['buttons'][] = '<button type="button" class="btn btn-warning btnDownloadIcon" onclick="$.Feeds.deleteOldEntriesByFeedId('.$feedId.');"><i class="fa fa-times"></i> '.lang('Remove old entries').' </button>';

			$form['urlDelete'] = base_url('feeds/delete/');
		}
		$form['buttons'][] = '<button type="submit" class="btn btn-primary" disabled="disabled"><i class="fa fa-save"></i> '.lang('Save').' </button> ';

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

	function resetAndScanFeed($feedId) {
		if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }

		$this->db->trans_start();

		$this->Feeds_Model->scanFeed($feedId, true);
		$this->Feeds_Model->updateFeedCounts($feedId);

		$this->db->trans_complete();

		return loadViewAjax(true, true);
	}

	function saveFeedIcon($feedId) {
		if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }

		return loadViewAjax(true, $this->Feeds_Model->saveFeedIcon($feedId, null, true));
	}

	function deleteOldEntriesByFeedId($feedId) {
		if (! $this->safety->allowByControllerName('feeds/edit') ) { return errorForbidden(); }

		return loadViewAjax(true, 'affected rows: '.$this->Feeds_Model->deleteOldEntriesByFeedId($feedId));
	}
}
