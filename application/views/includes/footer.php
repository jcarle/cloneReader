		</div>
	</div>
	<footer ></footer>
<?php	
$crSettings = array(
	'siteName'            => config_item('siteName'),
	'pageSize'            => config_item('pageSize'),
	'pageHome'            => $this->router->default_controller,
	'langId'              => $this->session->userdata('langId'),
	'defaultCurrencyId'   => config_item('defaultCurrencyId'),
	'defaultCurrencyName' => config_item('defaultCurrencyName'),
);

echo '	
	<script type="text/javascript" >
		var crSettings  = '.json_encode($crSettings).';
		var base_url    = \''. base_url().'\';
		var datetime    = \''. $this->Commond_Model->getCurrentDateTime().'\';
	</script>';

$this->carabiner->display('js');		

echo $this->myjs->getHtml();
?>		
</body>
</html>
