<?php
$this->my_js->add(' $(\'.cr-page-logs-detail .datetime \').each( function() { $.formatDate($(this)); } ); ');

$aLog        = array();
$entityName  = null;
$count       = count($logs);
$entityUrl   = getEntityUrl($entityTypeId, $entityId);
$entityName  = getEntityName($entityTypeId, $entityId);

foreach ($logs as $data) {
	$className = 'list-group-item';
	switch ($data['statusId']) {
		case config_item('statusApproved'):
			$className .= ' list-group-item-success'; break;
		case config_item('statusRejected'):
			$className .= ' list-group-item-danger'; break;
	}

	$aLog[] = '
		<li class="'.$className.'">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					<h4>'.$data['userFullName'].'</h4>
					<small class="datetime">'.$data['entityLogDate'].'</small>
					<a class="btn btn-default" data-toggle="collapse" data-target="#entityLogRaw-'.$data['entityLogId'].'" aria-expanded="false" aria-controls="collapseExample"> <i class="fa fa-file-text-o text-info"> </i> '.lang('View raw').' </a>
					<h5> <label class="label label-danger"> #'.$count.' </label> </h5>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
					<div class="well">
						<label> <i class="fa fa-files-o text-info"> </i> Diff </label>
						<code>'.htmlspecialchars(json_encode(json_decode($data['entityLogDiff']), JSON_PRETTY_PRINT)).'</code>
					</div>
					<div class="collapse well" id="entityLogRaw-'.$data['entityLogId'].'">
						<label> <i class="fa fa-file-text-o text-info"> </i> Raw </label>
						<code>'.htmlspecialchars(json_encode(json_decode($data['entityLogRaw']), JSON_PRETTY_PRINT)).'</code>
					</div>
				</div>
			</div>
		</li>
	';
	$count--;
}
?>
<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<?php echo langEntityTypeName($entityTypeId, true).': <span>'. $entityName.'</span>'; ?>
			</div>
			<ul class="list-group">
				<li class="list-group-item">
					<a class="crLink" target="_blank"  href="<?php echo $entityUrl; ?>" > <?php echo $entityUrl; ?> </a>
				</li>
				<?php echo implode(' ', $aLog); ?>
			</ul>
			<div class="panel-footer">
				<a class="btn btn-default  " href="javascript:$.goToUrlList();"> <i class="fa fa-arrow-left" > </i> <?php echo lang('Back'); ?> </a>
			</div>
		</div>
	</div>
</div>
