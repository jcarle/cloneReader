<div class="row">
	<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
		
		<ul class="nav nav-pills nav-stacked userProfile" >
			<li>
				<a href="#editProfile" data-toggle="tab" ><?php echo $this->lang->line('Edit profile'); ?></a>
			</li>
			<li>
				<a href="#changeEmail" data-toggle="tab"><?php echo $this->lang->line('Change email'); ?></a>
			</li>
			<li>
				<a href="#changePassword" data-toggle="tab"><?php echo $this->lang->line('Change password'); ?></a>
			</li>
			<li>
				<a href="#removeAccount" data-toggle="tab"><?php echo $this->lang->line('Remove account'); ?></a>
			</li>
			<li>
				<a href="#removeAccount" data-toggle="tab"><?php echo $this->lang->line('Download OPML'); ?></a>
			</li>			
		</ul>

	</div>
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		
		<div class="tab-content">
			<div class="tab-pane" id="editProfile" 		data-controller="<?php echo base_url('profile/editProfile'); ?>"> </div>
			<div class="tab-pane" id="changeEmail" 		data-controller="<?php echo base_url('profile/changeEmail'); ?>"> </div>
			<div class="tab-pane" id="changePassword" 	data-controller="<?php echo base_url('profile/changePassword'); ?>"> </div>
			<div class="tab-pane" id="removeAccount"	data-controller="<?php echo base_url('profile/removeAccount'); ?>"> </div>
		</div>

	</div>
</div>
