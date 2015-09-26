<?php
echo sprintf(lang('Hello %s'), $user['userFirstName']);
echo '<p>'.sprintf(lang('To change your  email in %s, click <a title="here" href="%s">here</a>'), config_item('siteName'), $url).'</p>';
