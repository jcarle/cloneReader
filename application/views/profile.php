<div class="row">
	<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
		
		<ul class="nav nav-pills nav-stacked userProfile" >
			<li>
				<a href="#editProfile" data-toggle="tab" >Edit profile</a>
			</li>
			<li>
				<a href="#changeEmail" data-toggle="tab">Change email</a>
			</li>
			<li>
				<a href="#changePassword" data-toggle="tab">Change password</a>
			</li>
			<li>
				<a href="#removeAccount" data-toggle="tab">Remove account</a>
			</li>
		</ul>

	</div>
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		
		<div class="tab-content">
			<div class="tab-pane" id="editProfile" 		data-controller="<?php echo base_url('profile/frmEditProfile'); ?>"> </div>
			<div class="tab-pane" id="changeEmail" 		data-controller="<?php echo base_url('profile/frmChangeEmail'); ?>"> </div>
			<div class="tab-pane" id="changePassword" 	data-controller="<?php echo base_url('profile/frmChangePassword'); ?>"> </div>
			<div class="tab-pane" id="removeAccount"	data-controller="<?php echo base_url('profile/frmRemoveAccount'); ?>"> </div>
		</div>

	</div>
</div>