<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/* End of file constants.php */
/* Location: ./application/config/constants.php */

define('PAGE_SIZE',	15);
define('GROUP_ROOT', 2);
define('GROUP_CLIENT', 3);
define('MENU_ADMIN', 1);
define('MENU_PROFILE', 10);
define('MENU_PUBLIC', 8);

define('TAG_ALL', 1);
define('TAG_STAR', 2);

define('FEED_TIME_SCAN', 60); 	// 	Cada cuanto MINUTOS busca nuevos feeds
define('FEED_TIME_SAVE', 3); 	// 	Cada cuanto SEGUNDOS guardan los datos
define('FEED_TIME_RELOAD', 5);  //  Cada cuanto MINUTOS recarga el menu con feeds
define('ENTRIES_PAGE_SIZE', 30);

