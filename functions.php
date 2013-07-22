<?php

//JGD NOTE: This prevents the auto insertion of paragraphs and line breaks.
remove_filter( 'the_content', 'wpautop' );

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
	$retval = dcs_dropship_generatePrettyProductTable( );
	//$retval = "<h3>Categories</h3>".dcs_dropship_generateProductCategoryTable();
	//$retval .= "<br/><h3>Brands</h3>".dcs_dropship_generateProductBrandTable();

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
 * Shopping Cart.
 */
function dcs_dropship_shopping_cart()
{
	$retval = "No Items in Cart.";

	if( !session_id() )
	{
		session_start();
	}

	if( isset($_SESSION['dcs_dropship_shopping_cart']) && !empty($_SESSION['dcs_dropship_shopping_cart']) )
	{
		$retval = "<table>";
		$retval .= "<tr><th>Product Name</th><th>SKU</th><th>Quantity</th><th>Item Price</th><th>Shipping</th><th>Total</th></tr>";
		foreach( $_SESSION['dcs_dropship_shopping_cart'] as $item )
		{
			$retval .= "<tr>";
			$retval .= "<td>".$item['product_name']."</td>";
			$retval .= "<td>".$item['sku']."</td>";
			$retval .= "<td>".$item['quantity']."</td>";
			$retval .= "<td>".$item['price']."</td>";
			$retval .= "<td>".$item['shipping']."</td>";
			$retval .= "<td>$".sprintf('%01.2f',($item['price']*$item['quantity']) )."</td>";
			$retval .= "</tr>";
		}
		$retval .= "</table><br />";

		$retval .= "<input type='button' id='dcs_dropship_clear_cart' value='Clear Cart' class='dcs_dropship_button'></input>";
		$retval .= "<input type='button' id='dcs_dropship_place_order' value='Place Order' class='dcs_dropship_button'></input>";
	}

	return $retval;
}

/**
 * Add to Cart.
 */
function dcs_dropship_addToCart()
{
	check_ajax_referer( "dcs_dropship_add_to_cart", "dcs_dropship_add_to_cart_nonce" );

	$dataValues = array( "sku" => $_POST['sku'],
						 "quantity" => $_POST['quantity'],
						 "price" => $_POST['price'],
						 "product_name" => $_POST['product_name'],
						 "shipping_cost" => $_POST['shipping_cost']
					   );

	dcsLogToFile( dcsVarDumpStr($dataValues) );

	if(!session_id()) session_start();

	if( !isset($_SESSION['dcs_dropship_shopping_cart']) )
	{
		$_SESSION['dcs_dropship_shopping_cart'] = array();
	}
	$_SESSION['dcs_dropship_shopping_cart'][] = $dataValues;

	echo site_url(get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE));
	die();
}
add_action( 'wp_ajax_dcs_dropship_add_to_cart', 'dcs_dropship_addToCart' );
add_action( 'wp_ajax_nopriv_dcs_dropship_add_to_cart', 'dcs_dropship_addToCart' );

/**
 * Clear Cart.
 */
function dcs_dropship_clearCart()
{
	check_ajax_referer( "dcs_dropship_clear_cart", "dcs_dropship_clear_cart_nonce" );

	if(!session_id()) session_start();

	if( isset($_SESSION['dcs_dropship_shopping_cart']) )
	{
		$_SESSION['dcs_dropship_shopping_cart'] = array();
	}

	echo site_url(get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE));
	die();
}
add_action( 'wp_ajax_dcs_dropship_clear_cart', 'dcs_dropship_clearCart' );
add_action( 'wp_ajax_nopriv_dcs_dropship_clear_cart', 'dcs_dropship_clearCart' );

/**
 * Place Order.
 */
function dcs_dropship_placeOrder()
{
	check_ajax_referer( "dcs_dropship_place_order", "dcs_dropship_place_order_nonce" );

	if(!session_id()) session_start();

	$shoppingCart = $_SESSION['dcs_dropship_shopping_cart'];

	//JGD TODO:
	//Calculate Shipping.
	//Payment gateway.
	//Put order on dropship server.

	echo "Order Placed.";
	die();
}
add_action( 'wp_ajax_dcs_dropship_place_order', 'dcs_dropship_placeOrder' );
add_action( 'wp_ajax_nopriv_dcs_dropship_place_order', 'dcs_dropship_placeOrder' );

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
		$fd = fopen(DCS_DROPSHIP_LOGFILE, "a");
		// append date/time to message
		$str = "[" . date("Y/m/d h:i:s", mktime()) . "] ". PHP_EOL . $msg; 
		// write string
		fwrite($fd, $str . PHP_EOL);
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
