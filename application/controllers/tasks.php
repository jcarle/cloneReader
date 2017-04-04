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
			'meta'   => array( 'title' => lang('Tasks') ),
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
}
