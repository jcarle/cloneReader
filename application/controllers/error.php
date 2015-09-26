<?php
class Error extends CI_Controller {
	function __construct() {
		parent::__construct();
	}

	function index() {
		$this->error404();
	}

	function forbidden($forceJson = false) {
		if ($forceJson == true || ($this->input->is_ajax_request() && $this->input->get('pageJson') != true)) {
			return $this->load->view('json', array(
				'code'         => false,
				'result'       => lang('Not authorized for the action to take'),
				'status_code'  => 403
			));
		}

		$this->load->view('pageHtml', array(
			'view'        => 'error',
			'meta'        => array( 'title' => lang('Error 403') ),
			'message'     => lang('Not authorized for the action to take'),
			'status_code' => 403
		));
	}

	function error404($forceJson = false) {
		if ($forceJson == true || ($this->input->is_ajax_request() && $this->input->get('pageJson') != true)) {
			return $this->load->view('json', array(
				'code'          => false,
				'result'        => lang('The page you requested does not exist'),
				'status_code'   => 404
			));
		}

		$this->load->view('pageHtml', array(
			'view'          => 'error',
			'meta'          => array( 'title' => lang('Error 404') ),
			'message'       => lang('The page you requested does not exist'),
			'status_code'   => 404
		));
	}
}
