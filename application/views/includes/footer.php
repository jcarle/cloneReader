	</div>
	<!-- <div id="footer"></div> -->

<script>
var base_url	= '<?php echo base_url(); ?>';
var datetime	= '<?php echo $this->Commond_Model->getCurrentDateTime(); ?>';
var langId		= '<?php echo $this->session->userdata('langId'); ?>';
</script>
	
<?php
if (!isset($langs)) {
	$langs = array();
}
$langs[] = 'Cancel';
$langs[] = 'Close';

echo langJs($langs);

$scripts = '';
if (isset($aServerData)) {
	$scripts .= 'var SERVER_DATA = '.json_encode($aServerData).'; ';
}

if (in_array($_SERVER['SERVER_NAME'], array('www.jcarle.com.ar', 'www.clonereader.com.ar'))) {
	$scripts .= "

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-41589815-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
";
}
if ($scripts != '') {
	echo '<script type="text/javascript">'.$scripts.'</script>';		
}
?>
</body>
</html>
