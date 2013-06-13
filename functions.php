<?php

//User functions
//require_once(ABSPATH . WPINC . '/registration.php');

/**
 * Inventory Page
 */ 
function dcs_dropship_inventory_page()
{
	//$retval = dcs_dropship_remote_get( DCS_DROPSHIP_INVENTORY_DATA_URL );

	$retval = generateTableFromTabData( file_get_contents(DCS_DROPSHIP_DIR."files/Inventory.tab") );

	return $retval;
}

/**
 *  Order Invoice Page
 */ 
function dcs_dropship_order_invoice_page()
{
	//$retval = dcs_dropship_remote_get( DCS_DROPSHIP_ORDER_INVOICE_DATA_URL );

	$retval = generateTableFromTabData( file_get_contents(DCS_DROPSHIP_DIR."files/Order_Invoice.tab") );

	return $retval;
}

/**
 * Product Page.
 */ 
function dcs_dropship_product_page()
{
	$retval = dcs_dropship_loadProducts();

	$showKeys = array( "sku", "wholesale_cost" );

	$retval = dcs_dropship_generatePrettyProductTable();

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
	//$retval = dcs_dropship_remote_get( DCS_DROPSHIP_TRACKING_DATA_URL );

	$retval = generateTableFromTabData( file_get_contents(DCS_DROPSHIP_DIR."files/Order_Tracking.tab") );

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
 * Parse a tab delimited file and generate a table from it.
 */
function generateTableFromTabData($data)
{
	$retval = "";
	$data = str_replace( array( "\r\n" , "\t" ) , array( "[NEW*LINE]" , "[tAbul*Ator]" ) , $data ); 
	$numLines = 0;
	
	$retval .= "<table border=\"1\">"; 
	foreach( explode( "[NEW*LINE]" , $a ) as $lines ) 
	{ 
		if( $numLines > 0 )
		{
			$retval .=  "<tr>"; 
		}
		else
		{
			$retval .=  "<th>";
		}
	
		foreach( explode( "[tAbul*Ator]" , $lines ) AS $li ) 
		{ 
			$retval .=  "<td>"; 
			$retval .=  $li ; 
			$retval .=  "</td>"; 
		} 
	
		if( $numLines > 0 )
		{
			$retval .=  "</tr>"; 
		}
		else 
		{
			$retval .=  "</th>";
		}
	
		$numLines++;
	} 
	$retval .=  "</table>"; 

	return $retval;
}

/**
 * Logging to file.                                       
 */
if( !function_exists("dcsLogToFile") )
{
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
}

/**
 * Dump array/object into a string.
 */
if( !function_exists("dcsVarDumpStr") )
{
	function dcsVarDumpStr($var)
	{
		ob_start();
		var_dump( $var );
		$out = ob_get_clean();
		return $out;
	}
}
