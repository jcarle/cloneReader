<?php
$items = array(
	array(
		'title' => 'Search',
		'buttons' => array(
			array(
				'text'    => 'Feeds',
				'url'     => 'process/saveFeedsSearch',
			),
			array(
				'text'    => 'Tags',
				'url'     => 'process/saveTagsSearch',
			),			
		),
	),
	array(
		'title' => 'Feeds',
		'buttons' => array(	
			array(
				'text' => 'Scan',
				'url'  => 'process/scanAllFeeds',
			),
			array(
				'text' => 'Rescan 404',
				'url'  => 'process/rescanAll404Feeds',
			),
			array(
				'text' => 'Process feeds tags',
				'url'  => 'process/processFeedsTags',
			),
			
		)
	),
	array(
		'title' => 'Entries',
		'buttons' => array(	
			array(
				'text' => 'Delete old entries',
				'url'  => 'process/deleteOldEntries',
			),
		)
	)
);
?>
<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<ul class="list-group">
<?php
foreach ($items as $item) {
?>
			<li class="list-group-item clearfix">
				<h4 class="list-group-item-heading"><?php echo $item['title']; ?> </h4>
<?php
	foreach ($item['buttons'] as $process) {
?>		
				<a href="javascript:$.process.submit('<?php echo base_url($process['url']); ?>');" class="btn btn-primary" >
					<i class="fa fa-cog"></i>
					<?php echo $process['text']; ?>
				</a>

<?php
	}
?>
			</li>
<?php
}
?>
		</ul>
	</div>
</div>
