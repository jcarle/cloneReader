<div class="row">
	<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
		<ul class="nav nav-pills nav-stacked userProfile" >
			<li>
				<a title="<?php echo lang('Edit profile'); ?>" href="#editProfile" data-toggle="tab" ><?php echo lang('Edit profile'); ?></a>
			</li>
			<li>
				<a title="<?php echo lang('Change email'); ?>" href="#changeEmail" data-toggle="tab"><?php echo lang('Change email'); ?></a>
			</li>
			<li>
				<a title="<?php echo lang('Change password'); ?>" href="#changePassword" data-toggle="tab"><?php echo lang('Change password'); ?></a>
			</li>
			<li>
				<a title="<?php echo lang('Remove account'); ?>" href="#removeAccount" data-toggle="tab"><?php echo lang('Remove account'); ?></a>
			</li>
			<li>
				<a title="<?php echo lang('Download OPML'); ?>" href="#downloadOPML" data-toggle="tab"><?php echo lang('Download OPML'); ?></a>
			</li>
		</ul>
	</div>
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<div class="tab-content">
			<div class="tab-pane" id="editProfile" 		data-controller="<?php echo base_url('profile/editProfile'); ?>"> </div>
			<div class="tab-pane" id="changeEmail" 		data-controller="<?php echo base_url('profile/changeEmail'); ?>"> </div>
			<div class="tab-pane" id="changePassword" 	data-controller="<?php echo base_url('profile/changePassword'); ?>"> </div>
			<div class="tab-pane" id="removeAccount"	data-controller="<?php echo base_url('profile/removeAccount'); ?>"> </div>
			<div class="tab-pane" id="downloadOPML"		data-controller="<?php echo base_url('profile/downloadOPML'); ?>"> </div>
		</div>
	</div>
</div>

<?php
$this->my_js->add( ' $.Profile.init(); ');
