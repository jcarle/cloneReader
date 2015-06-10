<?php
$CI = &get_instance();
?>
		</div>
	</div>
	<footer ></footer>
<?php	
$CI->load->model('Users_Model');
$userFilters = $CI->Users_Model->getUserFiltersByUserId( $this->session->userdata('userId') );

$crSettings = array(
	'siteName'            => config_item('siteName'),
	'pageSize'            => config_item('pageSize'),
	'pageHome'            => $this->router->default_controller,
	'langId'              => $this->session->userdata('langId'),
	'addTitleSiteName'    => config_item('addTitleSiteName'),
	'defaultCurrencyId'   => config_item('defaultCurrencyId'),
	'defaultCurrencyName' => config_item('defaultCurrencyName'),
	'environment'         => ENVIRONMENT,
	'datetime'            => $this->Commond_Model->getCurrentDateTime(),
	'tagAll'              => config_item('tagAll'),
	'tagStar'             => config_item('tagStar'),
	'tagHome'             => config_item('tagHome'),
	'tagBrowse'           => config_item('tagBrowse'),
	'feedTimeSave'        => config_item('feedTimeSave'),
	'feedTimeReload'      => config_item('feedTimeReload'),
	'entriesPageSize'     => config_item('entriesPageSize'),
	'feedMaxCount'        => config_item('feedMaxCount'),
	'userFilters'         => json_decode($userFilters),
);

echo '	
	<script type="text/javascript" >
		var crSettings  = '.json_encode($crSettings).';
		var base_url    = \''. base_url().'\';
	</script>';

$this->carabiner->display('js');		

echo $this->my_js->getHtml();
?>		
</body>
</html>
