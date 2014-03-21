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




define('SITE_NAME', 'cloneReader BETA');
define('SITE_ID', 'cloneReader');

define('DEFAULT_CURRENCY_NAME', 'AR$');
define('DEFAULT_CURRENCY_ID', 1);

define('USER_ANONYMOUS', 1);
define('AUTOCOMPLETE_SIZE',	50);
define('PAGE_SIZE',	15);
define('GROUP_ROOT', 2);
define('GROUP_DEFAULT', 3);
define('MENU_ADMIN', 1);
define('MENU_PROFILE', 10);
define('MENU_PUBLIC', 8);

define('TAG_ALL', 1);
define('TAG_STAR', 2);
define('TAG_HOME', 3);
define('TAG_BROWSE', 4);

define('FEED_MAX_COUNT', 1000);

define('FEED_CLONEREADER', 1633); 	// 	id del feed propio, se muestra en el filtro 'home'
define('FEED_STATUS_PENDING', 0); 	
define('FEED_STATUS_APPROVED', 1);
define('FEED_STATUS_INVALID_FORMAT', 3);
define('FEED_STATUS_NOT_FOUND', 404);

define('FEED_MAX_RETRIES', 10); // maxima cantidad de reintentos si un feed tiene algun error 

define('FEED_TIME_SCAN', 180); 	// 	Cada cuanto MINUTOS busca nuevos feeds
define('FEED_TIME_SAVE', 10); 	// 	Cada cuanto SEGUNDOS guardan los datos
define('FEED_TIME_RELOAD', 999);  //  Cada cuanto MINUTOS recarga el menu con feeds
define('ENTRIES_PAGE_SIZE', 30);
define('URL_SECRET_TIME', 180);// Cuantos MINUTOS esta vivo el link para resetear password y cambiar email 


/* End of file constants.php */
/* Location: ./application/config/constants.php */