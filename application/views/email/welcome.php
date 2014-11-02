<?php
echo sprintf($this->lang->line('Hello %s'), $user['userFirstName']);
echo '<h2>'.sprintf($this->lang->line('Welcome to %s'), config_item('siteName')).'</h2>';

if ($url != null) {
	echo '<p>'.sprintf($this->lang->line('To confirm your email in %s, click <a title="here" href="%s">here</a>'), config_item('siteName'), $url).'</p>';
}
