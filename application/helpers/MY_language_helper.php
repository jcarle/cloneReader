<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Save all lines into a json data for use in javascript
 *
 * @param array $lines array('line1, 'line2', ..., 'lineN')
 * @author porquero
 * @link porquero.blogspot.com
 *
 * @return string
 */
function langJs($lines)
{
    $CI = & get_instance();
    $json = array();

    foreach ((array)$lines as $line) {
        $json[$line] = $CI->lang->line($line);
    }

    $json = json_encode($json);
    $script = '<script type="text/javascript">var _msg = ' . $json . ';</script>' . "\n";

    return $script;

}