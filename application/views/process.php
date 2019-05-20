<?php
$items = array(
	array(
		'title' => 'Search',
		'buttons' => array(
			array(
				'text'   => 'Users',
				'url'    => 'process/saveEntitiesSearch/'.config_item('entityTypeUser'),
				'icon'   => 'fa fa-user text-muted',
				'childs' => array(
					array(
						'text'   => 'Only rows updated',
						'url'    => 'process/saveEntitiesSearch/'.config_item('entityTypeUser').'/true',
					),
				),
			),
			array(
				'text'   => 'Feeds',
				'url'    => 'process/saveEntitiesSearch/'.config_item('entityTypeFeed'),
				'icon'   => 'fa fa-rss text-success',
				'childs' => array(
					array(
						'text'   => 'Only rows updated',
						'url'    => 'process/saveEntitiesSearch/'.config_item('entityTypeFeed').'/true',
					),
				),
			),
			array(
				'text'   => 'Entries',
				'url'    => 'process/saveEntitiesSearch/'.config_item('entityTypeEntry'),
				'icon'   => 'fa fa-bookmark text-danger',
				'childs' => array(
					array(
						'text'   => 'Only rows updated',
						'url'    => 'process/saveEntitiesSearch/'.config_item('entityTypeEntry').'/true',
					),
				),
			),
			array(
				'text'   => 'Tags',
				'url'    => 'process/saveEntitiesSearch/'.config_item('entityTypeTag'),
				'icon'   => 'fa fa-tags text-info',
				'childs' => array(
					array(
						'text'   => 'Only rows updated',
						'url'    => 'process/saveEntitiesSearch/'.config_item('entityTypeTag').'/true',
					),
				),
			),
			array(
				'text'    => 'All entities',
				'url'     => 'process/saveEntitiesSearch',
				'childs' => array(
					array(
						'text'   => 'Only rows updated',
						'url'    => 'process/saveEntitiesSearch/null/true',
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
				'icon' => 'fa fa-rss text-success',
			),
			array(
				'text' => 'Rescan 404',
				'url'  => 'process/rescanAll404Feeds',
				'icon' => 'fa fa-rss text-success',
			),
			array(
				'text' => 'Process feeds tags',
				'url'  => 'process/processFeedsTags',
				'icon' => 'fa fa-rss text-success',
			),

		)
	),
	array(
		'title' => 'Entries',
		'buttons' => array(
			array(
				'text' => 'Delete old entries',
				'url'  => 'process/deleteOldEntries',
				'icon' => 'fa fa-bookmark text-danger',
			),
		),
	),
	array(
		'title'   => 'Diff logs',
		'buttons' => array(
			array(
				'text' => 'Diff logs',
				'url'  => 'process/processDiffEntityLog',
				'icon' => 'fa-file-text-o text-info',
			),
		),
	),
);


$aLi = array();
foreach ($items as $item) {
	$aButtons = array();
	foreach ($item['buttons'] as $process) {
		$className = 'btn-default';
		$icon      = 'fa-cog';
		if (isset($process['icon'])) {
			$icon = $process['icon'];
		}
		if (!isset($process['childs'])) {
			$aButtons[] = '
				<a title="'. lang($process['text']).'" href="javascript:$.process.submit(\''. base_url($process['url']).'\');" class="btn '.$className.'" >
					<i class="fa '.$icon.'"></i> '. lang($process['text']).'
				</a>';
		}
		else {
			$aChilds = array();
			foreach ($process['childs'] as $child) {
				$aChilds[] = ' <li> <a title="'. lang($child['text']).'" href="javascript:$.process.submit(\''. base_url($child['url']).'\');"  > '. lang($child['text']).' </a> </li>';
			}

			$aButtons[] = '
				<div class="btn-group">
					<a title="'. $process['text'].'" href="javascript:$.process.submit(\''. base_url($process['url']).'\');" class="btn '.$className.'" >
						<i class="fa '.$icon.'"></i> '. lang($process['text']).'
					</a>
					<button type="button" class="btn '.$className.' dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
					</button>
					<ul class="dropdown-menu" role="menu">
						'.implode('', $aChilds).'
					</ul>
				</div> ';
		}
	}
	$aLi[] = '<li class="list-group-item clearfix">
				<h4 class="list-group-item-heading">'. lang($item['title']).' </h4>
				'.implode('', $aButtons).'
			</li>';
}
?>


<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<ul class="list-group">
<?php echo implode(' ', $aLi); ?>
		</ul>
	</div>
</div>
