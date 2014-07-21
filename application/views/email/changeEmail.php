<?php
echo sprintf($this->lang->line('Hello %s'), $user['userFirstName']);
echo '<p>'.sprintf($this->lang->line('To change your  email in %s, click <a href="%s">here</a>'), config_item('siteName'), $url).'</p>';
