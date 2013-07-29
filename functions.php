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

	return $retval;
}

/**
 * Category Page
 */
function dcs_dropship_category_page()
{
	$retval = dcs_dropship_generateProductCategoryTable();

	return $retval;
}

/**
 * Brand Page
 */
function dcs_dropship_brand_page()
{
	$retval = dcs_dropship_generateProductBrandTable();
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
 * Approved Order.
 */
function dcs_dropship_approved_order_page()
{
	//JGD TODO: Here's where to create the order file and place it on the dropship server.
	return "Order Approved.";
}

/**
 * Declined Order.
 */
function dcs_dropship_declined_order_page()
{
	return "Order Declined.";
}

/**
 * Shopping Cart.
 */
function dcs_dropship_shopping_cart()
{
	if( !session_id() ) session_start();

	$retval = "No Items in Cart.";

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
			$retval .= "<td>$".$item['price']."</td>";
			$retval .= "<td>".$item['shipping_cost']."</td>";
			$retval .= "<td>$".sprintf('%01.2f',($item['price']*$item['quantity']))."</td>";
			$retval .= "</tr>";
		}
		$retval .= "</table><br />";

		$retval .= "<input type='button' id='dcs_dropship_clear_cart' value='Clear Cart' class='dcs_dropship_button'></input>";
		$retval .= "<input type='button' id='dcs_dropship_place_order' value='Place Order' class='dcs_dropship_button'></input>";

		//Create the hidden form for submitting to the payment gateway
		$retval .= "<FORM style='display:none;' id='dcs_dropship_payment_form' METHOD=POST ACTION='https://www.eProcessingNetwork.com/cgi-bin/wo/order.pl'>";
		//$retval .= "<INPUT TYPE=HIDDEN NAME='ePNAccount' VALUE='06131240'>"; //Real number
		$retval .= "<INPUT TYPE=HIDDEN NAME='ePNAccount' VALUE='05971'>"; //Testing only

		//Items.
		foreach( $_SESSION['dcs_dropship_shopping_cart'] as $item )
		{
			$retval .= "<br />";
			$retval .= "<INPUT SIZE=5 NAME='ItemQty' MAXLENGTH=3 VALUE='".$item['quantity']."'>";
			$retval .= "<INPUT TYPE=HIDDEN NAME='ItemDesc' VALUE='".$item['product_name']."' >";
			$retval .= "<INPUT TYPE=HIDDEN NAME='ItemCost' VALUE='".$item['price']."'>";
			$retval .= "<br />";
		}
		//Logo URL
		$retval .= "<INPUT TYPE=HIDDEN NAME='LogoURL' VALUE='".get_option(DCS_DROPSHIP_LOGO_URL)."'>";
		//Order processing urls.
		$retval .= "<INPUT TYPE=HIDDEN NAME='ReturnApprovedURL' VALUE='".get_option(DCS_DROPSHIP_APPROVED_PAGE)."'>";
		$retval .= "<INPUT TYPE=HIDDEN NAME='ReturnDeclinedURL' VALUE='".get_option(DCS_DROPSHIP_DECLINED_PAGE)."'>";
		//Sales Tax
		$retval .= "<INPUT NAME='ApplyTax' TYPE=CHECKBOX>";
		$retval .= "<INPUT TYPE=HIDDEN VALUE='.075' NAME='TaxRate'>";
		//Billing info
		$retval .= "<input type='text' name='BillFirstName' value=''>";
		$retval .= "<input type='text' name='BillLastName' value=''>";
		$retval .= "<input type='text' name='BillCompany' value=''>";
		$retval .= "<input type='text' name='BillAddress' value=''>";
		$retval .= "<input type='text' name='BillCity' value=''>";
		$retval .= "<input type='text' name='BillState' value=''>";
		$retval .= "<input type='text' name='BillZip' value=''>";
		$retval .= "<input type='text' name='BillCountry' value=''>";
		$retval .= "<input type='text' name='BillPhone' value=''>";
		$retval .= "<input type='text' name='BillEmail' value=''>";
		//Shipping info
		$retval .= "<input type='text' name='ShipFirstName' value=''>";
		$retval .= "<input type='text' name='ShipLastName' value=''>";
		$retval .= "<input type='text' name='ShipCompany' value=''>";
		$retval .= "<input type='text' name='ShipAddress' value=''>";
		$retval .= "<input type='text' name='ShipCity' value=''>";
		$retval .= "<input type='text' name='ShipState' value=''>";
		$retval .= "<input type='text' name='ShipZip' value=''>";
		$retval .= "<input type='text' name='ShipCountry' value=''>";
		$retval .= "<input type='text' name='ShipPhone' value=''>";
		$retval .= "<input type='text' name='ShipEmail' value=''>";
		$retval .= "<input type='hidden' name='ShippingPrompt' value='Yes'>";    
		//Shipping fees (JGD TODO)
		$retval .= "<INPUT TYPE='HIDDEN' NAME='ServiceDesc' VALUE='Shipping and Handling'>";
        $retval .= "<INPUT TYPE='HIDDEN' NAME='ServiceFee' VALUE='".get_option(DCS_DROPSHIP_SHIPPING_PERCENTAGE)."'>"; 
		$retval .= "<INPUT TYPE='HIDDEN' NAME='ServicePercent' VALUE='".get_option(DCS_DROPSHIP_SHIPPING_MINIMUM)."'>"; 

		$retval .= "</form>";
	}

	return $retval;
}

/**
 * Add to Cart.
 */
function dcs_dropship_addToCart()
{
	check_ajax_referer( "dcs_dropship_add_to_cart", "dcs_dropship_add_to_cart_nonce" );

	$price = sscanf($_POST['price'], "$%f");

	$dataValues = array( "sku" => $_POST['sku'],
						 "quantity" => $_POST['quantity'],
						 "price" => $price[0],
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

	echo get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE);
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

	echo get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE);
	die();
}
add_action( 'wp_ajax_dcs_dropship_clear_cart', 'dcs_dropship_clearCart' );
add_action( 'wp_ajax_nopriv_dcs_dropship_clear_cart', 'dcs_dropship_clearCart' );

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
