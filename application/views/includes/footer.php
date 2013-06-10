	</div>
	<!-- <div id="footer"></div> -->
	
<?php
if (isset($aServerData)) {
echo '	
<script>
var SERVER_DATA = '.json_encode($aServerData).';
</script>';
}
?>
</body>
</html>