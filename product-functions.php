<?php

define( 'PRODUCT_TAB_FILE_NAME', DCS_DROPSHIP_DIR."files/product.tab" );
define( 'PRODUCT_NUM_LINES', 5 );
define( 'PRODUCT_NUM_COLS', 3 );
define( 'PRODUCT_NUM', 25 );

/**
 * Parse and Load the products.
 */
function dcs_dropship_loadProducts()
{
	global $dropshipProducts;
	$retval = "";

    $file_handle = fopen(PRODUCT_TAB_FILE_NAME, "r");
	$numLines = 0;
	$numCols = 0;
	$keys = array();
	$dropshipProducts = array();

	while( !feof($file_handle) && ($numLines < PRODUCT_NUM))
	{
		$line = fgets($file_handle);
		$line = str_replace( array("\t") , array("[tAbul*Ator]") , $line ); 
		$lineVals = array();

		//Parse the line.
		foreach( explode("[tAbul*Ator]", $line) as $li ) 
		{ 
			$retval .= "NumLines: " . $numLines . "<br />";
			if( $numLines == 0 )
			{
				$retval .= "Adding " . trim($li) . " as a key.<br />";
				$keys[] = trim($li);
			}
			else
			{
				$retval .= "Adding " . trim($li) . " as a value for key $numCols:".$keys[$numCols].".<br />";
				$lineVals[$keys[$numCols]] = trim($li);
			}
			$numCols++;
		} 

		$retval .= dcsVarDumpStr( $lineVals );

		$dropshipProducts[] = $lineVals;
		$numLines++;
		$numCols = 0;
	}

	$retval .= dcsVarDumpStr( $dropshipProducts ) . "<br />";

	return $retval;
}

/**
 * Display Products in a table. Mostly for debug purposes.
 */
function dcs_dropship_generateProductTable($showKeys=NULL)
{
	global $dropshipProducts;
	$retval = "<table border='1'>";
	$numLines = 1;

	//$retval .= dcsVarDumpStr( $dropshipProducts );

	//The header
	$retval .= "<tr>";
	foreach( $dropshipProducts[1] as $key=>$value )
	{
		if( $showKeys == NULL || in_array($key,$showKeys) )
		{
			$retval .= "<th>".$key."</th>";
		}
	}
	$retval .= "</tr>";

	foreach( $dropshipProducts as $product )
	{
		$retval .= "<tr>";
		foreach( $product as $key=>$value )
		{
			if( $showKeys == NULL || in_array($key,$showKeys) )
			{
				$retval .= "<td>".$value."</td>"; 
			}
		}
		$retval .= "</tr>";
		$numLines++;
	}

	$retval .= "</table>";

	return $retval;
}

/**
 * Pretty Product Table.
 */
function dcs_dropship_generatePrettyProductTable()
{
	global $dropshipProducts;
	$retval = "<table cellpadding='3'>";

	$numCols = 1;

	foreach( $dropshipProducts as $product )
	{
		if( $product['status'] == "in-stock" )
		{
			if( $numCols == 1 )
			{
				$retval .= "<tr>";
			}
			$retval .= dcs_dropship_generateProductCell( $product );
			if( $numCols == PRODUCT_NUM_COLS )
			{
				$retval .= "</tr>";
				$numCols = 0;
			}
			$numCols++;
		}
	}

	$retval .= "</table>";

	return $retval;
}

/**
 * Generate a table cell for the given product.                         
 */
function dcs_dropship_generateProductCell($product)
{
	$retval = "";

	$retval .= "<td class='dcs_dropship_product'>";
	$retval .= "<div class='dcs_dropship_product'>";
	$retval .= "<img class='dcs_dropship_product' src='".$product['product_image']."'><br />";
	$retval .= $product['product_title']."<br />";
	$retval .= "$".$product['wholesale_cost'];
	$retval .= "</div>";
	$retval .= "</td>";

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
?>
