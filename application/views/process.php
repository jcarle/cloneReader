<?php
$items = array(
	/* array(
		'title' => 'Search',
		'buttons' => array(
			array(
				'text'    => 'Zones',
				'url'     => 'process/saveZonesSearch',
			),
		),
	),*/
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
		)
	),
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
