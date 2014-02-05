<div id="cloneReader" >
</div>

<script>
var TAG_HOME			= <?php echo TAG_HOME; ?>;
var TAG_ALL			 	= <?php echo TAG_ALL; ?>;
var TAG_STAR			= <?php echo TAG_STAR; ?>;
var TAG_BROWSE			= <?php echo TAG_BROWSE; ?>;
var FEED_TIME_SAVE		= <?php echo FEED_TIME_SAVE; ?>;
var FEED_TIME_RELOAD 	= <?php echo FEED_TIME_RELOAD; ?>;
var ENTRIES_PAGE_SIZE	= <?php echo ENTRIES_PAGE_SIZE;?>;
var FEED_MAX_COUNT		= <?php echo FEED_MAX_COUNT; ?>;

$(document).ready(function() {
	cloneReader.init(<?php echo $userFilters; ?>);
});
</script>
