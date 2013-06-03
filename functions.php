<?php

//User functions
//require_once(ABSPATH . WPINC . '/registration.php');

/**
 * Inventory Page
 */ 
function dcs_dropship_inventory_page()
{
	$retval = "";
	$response = wp_remote_post( get_option(DCS_DROPSHIP_INVENTORY_DATA_URL) );

	if( is_wp_error( $response ) ) 
	{
	   $error_message = $response->get_error_message();
	   $retval .= "Something went wrong: $error_message";
	} 
	else 
	{
	   $retval .= 'Response:<pre>';
	   $retval .= dcsVarDumpStr( $response );
	   $retval .= '</pre>';
	}

	return $retval;
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


