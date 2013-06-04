<?php

//User functions
//require_once(ABSPATH . WPINC . '/registration.php');

/**
 * Inventory Page
 */ 
function dcs_dropship_inventory_page()
{
	$retval = dcs_dropship_remote_get( DCS_DROPSHIP_INVENTORY_DATA_URL );

	return $retval;
}

/**
 *  Order Invoice Page
 */ 
function dcs_dropship_order_invoice_page()
{
	$retval = dcs_dropship_remote_get( DCS_DROPSHIP_ORDER_INVOICE_DATA_URL );

	return $retval;
}

/**
 * Product Page
 */ 
function dcs_dropship_product_page()
{
	$retval = dcs_dropship_remote_get( DCS_DROPSHIP_PRODUCT_DATA_URL );

	return $retval;
}

/**
 * Order Status Page
 */ 
function dcs_dropship_order_status_page()
{
	$retval = dcs_dropship_remote_get( DCS_DROPSHIP_ORDER_STATUS_DATA_URL );

	return $retval;
}
/**
 * Tracking Page
 */ 
function dcs_dropship_tracking_page()
{
	$retval = dcs_dropship_remote_get( DCS_DROPSHIP_TRACKING_DATA_URL );

	return $retval;
}

/**
 * Pull from the remote url.
 */ 
function dcs_dropship_remote_get($option_name)
{
	$retval = "";
	$response = wp_remote_post( get_option($option_name) );

	//JGD: Not sure if we need this.
	//if (in_array('curl', get_loaded_extensions())) 
	//{
	//	$ch = curl_init();
	//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	//}

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


