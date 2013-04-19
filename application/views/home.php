<div id="cloneReader" >
</div>

<script>
var TAG_ALL 	= <?php echo TAG_ALL; ?>;
var TAG_STAR	= <?php echo TAG_STAR; ?>;

$(document).ready(function() {
	cloneReader.init(<?php echo $userFilters; ?>);
});
</script>
