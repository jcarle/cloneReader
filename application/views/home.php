<?php
$crSettings = array(
	'tagAll'           => config_item('tagAll'),
	'tagStar'          => config_item('tagStar'),
	'tagHome'          => config_item('tagHome'),
	'tagBrowse'        => config_item('tagBrowse'),
	'feedTimeSave'     => config_item('feedTimeSave'),
	'feedTimeReload'   => config_item('feedTimeReload'),
	'entriesPageSize'  => config_item('entriesPageSize'),
	'feedMaxCount'     => config_item('feedMaxCount'),
);
?>
<script>
$(document).ready(function() {
	$.crSettings = $.extend($.crSettings, <?php echo json_encode($crSettings); ?>);
	cloneReader.init(<?php echo $userFilters; ?>);
});
</script>
