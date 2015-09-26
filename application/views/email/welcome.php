<?php
echo sprintf(lang('Hello %s'), $user['userFirstName']);
echo '<h2>'.sprintf(lang('Welcome to %s'), config_item('siteName')).'</h2>';

if ($url != null) {
	echo '<p>'.sprintf(lang('To confirm your email in %s, click <a title="here" href="%s">here</a>'), config_item('siteName'), $url).'</p>';
}
