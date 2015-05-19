<?php
$items = array(
	array(
		'title' => 'Search',
		'buttons' => array(
			array(
				'text'    => 'Users',
				'url'     => 'process/saveEntitiesSearch/'.config_item('entityTypeUser'),
				'childs' => array(
					array(
						'text'   => 'Only rows updated',
						'url'    => 'process/saveEntitiesSearch/'.config_item('entityTypeUser').'/true',
					),
				),
			),
			array(
				'text'    => 'Feeds',
				'url'     => 'process/saveEntitiesSearch/'.config_item('entityTypeFeed'),
				'childs' => array(
					array(
						'text'   => 'Only rows updated',
						'url'    => 'process/saveEntitiesSearch/'.config_item('entityTypeFeed').'/true',
					),
				),1
			),
			array(
				'text'    => 'Tags',
				'url'     => 'process/saveEntitiesSearch/'.config_item('entityTypeTag'),
				'childs' => array(
					array(
						'text'   => 'Only rows updated',
						'url'    => 'process/saveEntitiesSearch/'.config_item('entityTypeTag').'/true',
					),
				),
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
		),
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
				<h4 class="list-group-item-heading"><?php echo $this->lang->line($item['title']); ?> </h4>
<?php
	foreach ($item['buttons'] as $process) {
		if (!isset($process['childs'])) {
			echo '
				<a title="'. $this->lang->line($process['text']).'" href="javascript:$.process.submit(\''. base_url($process['url']).'\');" class="btn btn-primary" >
					<i class="fa fa-cog"></i> '. $this->lang->line($process['text']).'
				</a>';
		}
		else {
			echo '
				<div class="btn-group">
					<a title="'. $process['text'].'" href="javascript:$.process.submit(\''. base_url($process['url']).'\');" class="btn btn-primary" >
						<i class="fa fa-cog"></i> '. $this->lang->line($process['text']).'
					</a>
					<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
					</button>
					<ul class="dropdown-menu" role="menu"> ';
			foreach ($process['childs'] as $child) {
				echo ' <li> <a title="'. $this->lang->line($child['text']).'" href="javascript:$.process.submit(\''. base_url($child['url']).'\');"  > '. $this->lang->line($child['text']).' </a> </li>';
			}
			echo '
					</ul>
				</div> ';
		}
	}
?>
			</li>
<?php
}
?>
		</ul>
	</div>
</div>
