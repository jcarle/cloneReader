<?php
class Login extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->model('Users_Model');
	}

	function index() {
		if (! $this->safety->allowByControllerName('login') ) { return errorForbidden(); }

		$form = $this->_getFormLogin();

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($this->input->is_ajax_request()) {

				$onLoginUrl = $this->session->userdata('onLoginUrl');
				if ($onLoginUrl == null) {
					$onLoginUrl = base_url();
				}
				return loadViewAjax($code, $code == false ? null : array('goToUrl' => $onLoginUrl, 'skipAppLink' => true));
			}
		}

		return $this->load->view('pageHtml', array(
			'view'   => 'login',
			'title'  => $this->lang->line('Login'),
			'form'   => $form,
			'meta'   => array(
				'title' => $this->lang->line('Login'),
				'description' => $this->lang->line('Ingresar - Motormaniaco del Auto, Services, Modelos, Marcas')
			)
		));
	}

	function popupLogin() {
		if (! $this->safety->allowByControllerName('login') ) { return errorForbidden(); }

		$form            = $this->_getFormLogin();
		$form['frmName'] = 'frmPopupLogin';

		$result = array(
			'html'           => $this->load->view('login', array( 'form' => $form, 'isPopUp' => true), true).$this->my_js->getHtml(),
			'title'          => $this->lang->line('Login'),
			'showPopupLogin' => true,
		);

		return loadViewAjax(true, $result);
	}

	function _getFormLogin() {
		$form = array(
			'frmName'  => 'frmLogin',
			'action'   => base_url('login'),
			'buttons'  => array('<button type="submit" class="btn btn-primary"><i class="fa fa-sign-in"></i> '.$this->lang->line('Login').' </button>'),
			'fields'   => array(
				'email' => array(
					'type'  => 'text',
					'label' => $this->lang->line('Email'),
					'value' => set_value('email')
				),
				'password' => array(
					'type'  => 'password',
					'label' => $this->lang->line('Password'),
					'value' => set_value('password')
				),
				'link'	=> array(
					'type'  => 'link',
					'label' => $this->lang->line('Forgot password'),
					'value' => base_url('forgotPassword'),
				)
			)
		);

		$form['rules'] = array(
			array(
				'field' => 'email',
				'label' => $form['fields']['email']['label'],
				'rules' => 'trim|required|valid_email|callback__validate_login'
			),
			array(
				'field' => 'password',
				'label' => $form['fields']['password']['label'],
				'rules' => 'trim|required'
			)
		);

		$this->form_validation->set_rules($form['rules']);

		return $form;
	}

	function _validate_login() {
		return $this->safety->login($this->input->post('email'), $this->input->post('password'));
	}

	function facebook() {
		$this->_oauth2('facebook');
	}

	function google() {
		$this->_oauth2('google');
	}

	function _oauth2($providerName) {
		if (! $this->safety->allowByControllerName('login') ) { return errorForbidden(); }

		$this->load->spark('oauth2/0.4.0/');
		$this->config->load('oauth2');

		$config   = $this->config->item('oauth2');
		$config   = $config[$providerName];
		$provider = $this->oauth2->provider($providerName, array(
			'id'     => $config['id'],
			'secret' => $config['secret'],
			'scope'  => $config['scope'],
		));

		if ( ! $this->input->get('code')) {
			$url = $provider->authorize();
			redirect($url);
		}

		try  {
			$token = $provider->access($_GET['code']);
			$user  = $provider->get_user_info($token);
			$user  = $this->Users_Model->loginRemote($user['email'], $user['last_name'], $user['first_name'], $user['location'], $user['birthday'], $providerName, $user['uid'] );

			if ($user == null) {
				return errorForbidden();
			}

			$this->session->set_userdata(array(
				'userId'  => $user['userId'],
				'langId'  => $user['langId'],
				'groups'  => sourceToArray($this->Users_Model->getGroups($user['userId']), 'groupId'),
			));

			$this->Users_Model->updateUserLastAccess();

			// Si el usuario es nuevo y tiene email le enviamos el email de bienvenida
			if (element('isNewUser', $user) == true && $user['userEmail'] != null) {
				$this->load->model(array('Tasks_Model', 'Entries_Model'));
				$this->Tasks_Model->addTask('sendEmailWelcome', array('userId' => $user['userId']));
				$this->Entries_Model->addDefaultFeeds();
			}

			$onLoginUrl = $this->session->userdata('onLoginUrl');
			if ($onLoginUrl == null) {
				$onLoginUrl = '';
			}

			redirect($onLoginUrl);
		}
		catch (OAuth2_Exception $e) {
			redirect('login');
		}

		return errorForbidden();
	}
}
