		</div>
	</div>
	<!-- <div id="footer"></div> -->
<?php	
if (!isset($hasUploadFile)) {
	$hasUploadFile = false;
}
if ($hasUploadFile == false && isset($form)) {	
	$hasUploadFile = hasCrUploadFile($form);
}

if ($hasUploadFile == true) {
	$this->load->view('includes/uploadfile');
}
?>
</body>
</html>
