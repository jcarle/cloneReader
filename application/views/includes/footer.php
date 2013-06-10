	</div>
	<!-- <div id="footer"></div> -->
	
<?php
$scripts = '';
if (isset($aServerData)) {
	$scripts .= 'var SERVER_DATA = '.json_encode($aServerData).'; ';
}

if ($_SERVER['SERVER_NAME'] == 'www.jcarle.com.ar') {
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