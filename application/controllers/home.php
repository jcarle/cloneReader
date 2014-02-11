<?php 
class Home extends CI_Controller {
	public function index() {
		$this->load->model('Users_Model');

		$this->load->view('includes/template', 
			array(
				'view'			=> 'home', 
				'title'			=> 'News reader and feeds',
				'aJs'			=> array('cloneReader.js', 'jquery.visible.min.js' ),
				'hasForm'		=> true,
				'userFilters'	=> $this->Users_Model->getUserFiltersByUserId( $this->session->userdata('userId') ),
				'langs'			=> array(
					'loading ...',
					'Expand',
					'Add feed',
					'Install',
					'Mark all as read',
					'Mark "%s" as read?',
					'Feed settings',
					'Sort by newest',
					'Sort by oldest',
					'All items',
					'%s unread items',
					'List view',
					'Detail view',
					'Reload',
					'Prev',
					'Next',
					'Add new feed',
					'Add feed url',
					'Keep unread',
					'no more entries',
					'Unsubscribe "%s"?',
					'Add new tag',
					'enter tag name',
					'Unsubscribe',
					'New tag',
					'From %s',
					'From %s by %s',
					'Subscribe',
					'Sort',
					'Star',
					'Enter a url',
					'Enter a valid url',
				)
			)
		);
	}
}
