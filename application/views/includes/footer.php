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

if (!isset($langs)) {
	$langs = array();
}
$langs      = getLangToJs($langs);
$this->myjs->add(langJs($langs));
$this->myjs->add(  '  $(\'.'.getPageName().'\').data(\'meta\', '.json_encode($meta).'); ');

if (ENVIRONMENT == 'production' && config_item('google-analytics-Account') != '') {
	$this->myjs->add( "
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	ga('create', '".config_item('google-analytics-Account')."', 'auto');
	ga('send', 'pageview');
	" );
}

echo $this->myjs->getHtml();
?>		
</body>
</html>
