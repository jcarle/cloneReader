<div id="cloneReader" >
</div>

<script>
var TAG_ALL			 	= <?php echo TAG_ALL; ?>;
var TAG_STAR			= <?php echo TAG_STAR; ?>;
var FEED_TIME_SAVE		= <?php echo FEED_TIME_SAVE; ?>;
var FEED_TIME_RELOAD 	= <?php echo FEED_TIME_RELOAD; ?>;
var ENTRIES_PAGE_SIZE	= <?php echo ENTRIES_PAGE_SIZE;?>;

$(document).ready(function() {
	cloneReader.init(<?php echo $userFilters; ?>);
});
</script>
