<?php
class Help extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->model('Users_Model');
	}

	function index() {

	}


	function keyboardShortcut() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }

		$aKeys = array(
			'j'	=> 'Next item',
			'k'	=> 'Previous item',
			'u'	=> 'Maximize entries',
			's'	=> 'Mark item as star',
			'm'	=> 'Mark item as unread',
			'v' => 'View original',
			'r' => 'Reload',
			'a' => 'Add feed',
			'e' => 'Send entry by email',
			'1' => 'Detail view',
			'2' => 'List view'
		);

		$html = '<ul class="list-group">';
		foreach ($aKeys as $key => $value) {
			$html .= '<li class="list-group-item"> <span class="label label-success">'.$key.'</span> '.$this->lang->line($value) .' </li> ';
		}
		$html .= '	</ul>';

		$form = array(
			'frmName'  => 'frmKeyboardShortcut',
			'title'    => $this->lang->line('Keyboard shortcut'),
			'icon'     => 'fa fa-keyboard-o',
			'buttons'  => array(),
			'fields'   => array(
				'keyboardShortcut' => array(
					'type'  => 'html',
					'value' => $html
				),
			)
		);


		if ($this->input->is_ajax_request()) {
			return $this->load->view('includes/crJsonForm', array( 'form' => $form));
		}


		$this->load->view('pageHtml', array(
			'view'			=> 'includes/crForm',
			'form'			=> $form,
			'title'			=> $this->lang->line('Keyboard shortcut'),
			'code'			=> true
		));
	}
}
