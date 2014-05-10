<p>
	<?php echo sprintf($this->lang->line('Someone recently requested that the password be reset for %s'), $user['userFirstName'].' '.$user['userLastName']); ?>
</p>
<p>
	<?php echo sprintf($this->lang->line('To reset your password please click <a href="%s">here</a>'), $url); ?>
</p>
<p>
	<?php echo $this->lang->line('If this is a mistake just ignore this email - your password will not be changed');?>
</p>
