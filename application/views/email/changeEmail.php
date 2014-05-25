<?php
echo sprintf($this->lang->line('Hello %s, <p>To change your  email in %s, click <a href="%s">here</a></p>'), $user['userFirstName'], config_item('siteName'), $url);
