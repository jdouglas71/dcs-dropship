<?php

//User functions
//require_once(ABSPATH . WPINC . '/registration.php');

/**
 * Inventory Page
 */ 
function dcs_dropship_inventory_page()
{
	return get_option( DCS_DROPSHIP_INVENTORY_DATA_URL );
}

/**
 * Logging to file.                                       
 */
function dcsLogToFile($msg)
{ 
    // open file
    $fd = fopen(LOGFILE, "a");
    // append date/time to message
    $str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg; 
    // write string
    fwrite($fd, $str . "\n");
    // close file
    fclose($fd);
}

function dcsVarDumpStr($var)
{
	ob_start();
	var_dump( $var );
	$out = ob_get_clean();
	return $out;
}


