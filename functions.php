<?php

//Gotta do this here to get around session start header errors on greengeeks server.
session_start();

//JGD NOTE: This prevents the auto insertion of paragraphs and line breaks.
remove_filter( 'the_content', 'wpautop' );

/**
 * Product Page.
 */ 
function dcs_dropship_product_page($pageNumber, $category, $searchTerms)
{
	$retval = "<table>";
	$retval .= "<tr><td width='20%' style='vertical-align:top;'>";
	$retval .= dcs_dropship_generateProductCategoryTable();
	$retval .= "</td>";

	$retval .= "<td width='80%'>";
	$retval .= dcs_dropship_generatePrettyProductTable($pageNumber, $category, $searchTerms);
	$retval .= "</td></tr>";

	$retval .= "</table>";

	return $retval;
}

/**
 * Product Info Page.
 */
function dcs_dropship_product_info_page($sku)
{
	global $wpdb;

	$retval = "No Product Selected.";

	if( isset($sku) )
	{
		//Find the product.
		$sql = "SELECT * FROM dcs_dropship_products where sku='".$sku."';";
		$product = $wpdb->get_row($sql,ARRAY_A);

		//dcsLogToFile( "product: " . dcsVarDumpStr($product) );

		if( $product != null )
		{
			//Generate the Page.
			$retval = dcs_dropship_generateProductPage($product);
		}
	}

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
 * Approved Order.
 */
function dcs_dropship_approved_order_page()
{
	global $dropshipFTPServer;
	global $dropshipFTPInDirectory;
	$retval = "Order Approved.";

	$fields = array( "po_number",
					 "line_item_sku",
					 "line_item_quantity",
					 "line_item_cost",
					 "line_item_title",
					 "ship_first_name",
					 "ship_last_name",
					 "ship_company",
					 "ship_address_1",
					 "ship_address_2",
					 "ship_city",
					 "ship_state",
					 "ship_zip_code",
					 "ship_country",
					 "ship_phone",
					 "ship_email",
					 "ship_method",
					 "ship_carrier",
					 "user_defined_name_1",
					 "user_defined_value_1",
					 "retailer_create_date" );	

	if( !session_id() ) session_start();

	$purchaseOrderFile = "";
	foreach( $fields as $field )
	{
		$purchaseOrderFile .= $field . "\t";
	}
	$purchaseOrderFile = substr( $purchaseOrderFile, 0, strlen($purchaseOrderFile)-1 );
	$purchaseOrderFile .= PHP_EOL;

	//Get the shipping info
	if( isset($_SESSION['dcs_dropship_shipping_info']) && !empty($_SESSION['dcs_dropship_shipping_info']) )
	{
		$shippingInfo = $_SESSION['dcs_dropship_shipping_info'];
		$timeStr = time();
		dcsLogToFile( "Shipping Info: " . dcsVarDumpStr($shippingInfo) );
	
		if( isset($_SESSION['dcs_dropship_shopping_cart']) && !empty($_SESSION['dcs_dropship_shopping_cart']) )
		{
			$shoppingCart = $_SESSION['dcs_dropship_shopping_cart'];
			foreach( $shoppingCart as $item )
			{
				$purchaseOrderFile .= $timeStr."\t".$item['sku']."\t".$item['quantity']."\t".$item['price']."\t".$item['product_name']."\t";
				$purchaseOrderFile .= $shippingInfo['first_name']."\t".$shippingInfo['last_name']."\t".$shippingInfo['company']."\t";
				$purchaseOrderFile .= $shippingInfo['address']."\t\t".$shippingInfo['city']."\t";
				$purchaseOrderFile .= $shippingInfo['state']."\t".$shippingInfo['zip']."\t".$shippingInfo['country']."\t";
				$purchaseOrderFile .= $shippingInfo['phone']."\t".$shippingInfo['email']."\tGround\tUPS\t\t\t";
				$purchaseOrderFile .= date("Y-m-d H:i:s",$timeStr).PHP_EOL;
			}
		}

		//Write the file locally
		$localDir = plugin_dir_path( __FILE__ ) . "/files/";
		$filename = "Purchase_Order_".$timeStr.".tab"; 
		$fd = fopen( $localDir.$filename, "w" );
		fwrite( $fd, $purchaseOrderFile );
		//Close the file to force a flush and reopen in read-only mode.
		fclose( $fd );
		$fd = fopen( $localDir.$filename, "r" );

		//Send the file to the FTP Site.
		$conn_id = ftp_connect( $dropshipFTPServer );
		$login_result = ftp_login( $conn_id, get_option(DCS_DROPSHIP_FTP_USER), get_option(DCS_DROPSHIP_FTP_PASSWORD) );
		dcsLogToFile( "Login results: " . $login_result );
		ftp_chdir( $conn_id, $dropshipFTPInDirectory );
		if( !ftp_fput( $conn_id, $filename, $fd, FTP_BINARY ) )
		{
			$retval = "Error uploading file.";
		}

		//Close up shop
		ftp_close( $conn_id );
		fclose( $fd );

		//Clear the shopping cart
		$_SESSION['dcs_dropship_shopping_cart'] = null;
		session_write_close();
	}

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

	$retval = "No Items in Cart.<br />";

	if( isset($_SESSION['dcs_dropship_shopping_cart']) && !empty($_SESSION['dcs_dropship_shopping_cart']) )
	{
		$retval = "<table>";
		$retval .= "<tr><th>Product Name</th><th>SKU</th><th>Quantity</th><th>Item Price</th><th>Total</th></tr>";
		foreach( $_SESSION['dcs_dropship_shopping_cart'] as $key=>$item )
		{
			$retval .= "<tr>";
			$retval .= "<td>".$item['product_name']."</td>";
			$retval .= "<td>".$item['sku']."</td>";
			$retval .= "<td>".$item['quantity']."</td>";
			$retval .= "<td>$".sprintf('%01.2f',$item['price'])."</td>";
			//$retval .= "<td>".$item['shipping_cost']."</td>";
			$retval .= "<td>$".sprintf('%01.2f',($item['price']*$item['quantity']))."</td>";
			$retval .= "<td><input type='button' id='dcs_dropship_remove_item' value='Remove' class='dcs_dropship_button dcs_dropship_remove_item' index='".$key."'></input></td>";
			$retval .= "</tr>";
		}
		$retval .= "</table><br />";

		$retval .= "<form id='dcs_dropship_shipping_form'>";
		$retval .= "<h2>Shipping Info</h2>";
		$retval .= "<table>";
		$retval .= "<tr><td width='10%' style='text-align:right;' ><label for='shipping_first_name'>First Name</label></td><td><input type='text' name='shipping_first_name' class='dcs_dropship_input'></td></tr>";
		$retval .= "<tr><td width='10%' style='text-align:right;' ><label for='shipping_last_name'>Last Name</label></td><td><input type='text' name='shipping_last_name' class='dcs_dropship_input'></td></tr>";
		$retval .= "<tr><td width='10%' style='text-align:right;' ><label for='shipping_company'>Company</label></td><td><input type='text' name='shipping_company' class='dcs_dropship_input'></td></tr>";
		$retval .= "<tr><td width='10%' style='text-align:right;' ><label for='shipping_address'>Address</label></td><td><input type='text' name='shipping_address' class='dcs_dropship_input'></td></tr>";
		$retval .= "<tr><td width='10%' style='text-align:right;' ><label for='shipping_city'>City</label></td><td><input type='text' name='shipping_city' class='dcs_dropship_input'></td></tr>";
		$retval .= "<tr><td width='10%' style='text-align:right;' ><label for='shipping_state'>State</label></td><td><input type='text' name='shipping_state' class='dcs_dropship_input'></td></tr>";
		$retval .= "<tr><td width='10%' style='text-align:right;' ><label for='shipping_zip'>Zip</label></td><td><input type='text' name='shipping_zip' class='dcs_dropship_input'></td></tr>";
		$retval .= "<tr><td width='10%' style='text-align:right;' ><label for='shipping_country'>Country</label></td><td><input type='text' name='shipping_country' class='dcs_dropship_input'></td></tr>";
		$retval .= "<tr><td width='10%' style='text-align:right;' ><label for='shipping_phone'>Phone</label></td><td><input type='text' name='shipping_phone' class='dcs_dropship_input'></td></tr>";
		$retval .= "<tr><td width='10%' style='text-align:right;' ><label for='shipping_email'>Email</label></td><td><input type='text' name='shipping_email' class='dcs_dropship_input'></td></tr>";
		$retval .= "</table>";
		$retval .= "</form>";

		$retval .= "<div style='text-align:right;'>";
		$retval .= "<input type='button' id='dcs_dropship_clear_cart' value='Clear Cart' class='dcs_dropship_button'></input>";
		$retval .= "<input type='button' id='dcs_dropship_place_order' value='Place Order' class='dcs_dropship_button'></input>";
		$retval .= "</div>";

		//Create the hidden form for submitting to the payment gateway
		$retval .= "<FORM style='display:none;' id='dcs_dropship_payment_form' METHOD=POST ACTION='https://www.eProcessingNetwork.com/cgi-bin/wo/order.pl'>";
		$retval .= "<INPUT TYPE=HIDDEN NAME='ePNAccount' VALUE='06131240'>"; //Real number
		//$retval .= "<INPUT TYPE=HIDDEN NAME='ePNAccount' VALUE='05971'>"; //Testing only

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
		$retval .= "<input type='hidden' name='ShipFirstName' value=''>";
		$retval .= "<input type='hidden' name='ShipLastName' value=''>";
		$retval .= "<input type='hidden' name='ShipCompany' value=''>";
		$retval .= "<input type='hidden' name='ShipAddress' value=''>";
		$retval .= "<input type='hidden' name='ShipCity' value=''>";
		$retval .= "<input type='hidden' name='ShipState' value=''>";
		$retval .= "<input type='hidden' name='ShipZip' value=''>";
		$retval .= "<input type='hidden' name='ShipCountry' value=''>";
		$retval .= "<input type='hidden' name='ShipPhone' value=''>";
		$retval .= "<input type='hidden' name='ShipEmail' value=''>";
		//Shipping fees (JGD TODO)
		$retval .= "<INPUT TYPE='HIDDEN' NAME='ServiceDesc' VALUE='Shipping and Handling'>";
        $retval .= "<INPUT TYPE='HIDDEN' NAME='ServiceFee' VALUE='".get_option(DCS_DROPSHIP_SHIPPING_PERCENTAGE)."'>"; 
		$retval .= "<INPUT TYPE='HIDDEN' NAME='ServicePercent' VALUE='".get_option(DCS_DROPSHIP_SHIPPING_MINIMUM)."'>"; 

		$retval .= "</form>";
	}

	$retval .= "<a href='".get_option(DCS_DROPSHIP_PRODUCT_PAGE)."'>Return to shopping.</a>";

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
    session_write_close();

	echo get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE);
	die();
}
add_action( 'wp_ajax_dcs_dropship_add_to_cart', 'dcs_dropship_addToCart' );
add_action( 'wp_ajax_nopriv_dcs_dropship_add_to_cart', 'dcs_dropship_addToCart' );

/**
 * Remove Item from Cart.
 */
function dcs_dropship_removeItemFromCart()
{
	check_ajax_referer( "dcs_dropship_remove_item", "dcs_dropship_remove_item_nonce" );

	if(!session_id()) session_start();

	//dcsLogToFile( "Removing item: " . $_POST['index'] );

	unset($_SESSION['dcs_dropship_shopping_cart'][$_POST['index']]);

	//dcsLogToFile( dcsVarDumpStr( $_SESSION ) );
    session_write_close();

	echo get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE);
	die();
}
add_action( 'wp_ajax_dcs_dropship_remove_item', 'dcs_dropship_removeItemFromCart' );
add_action( 'wp_ajax_nopriv_dcs_dropship_remove_item', 'dcs_dropship_removeItemFromCart' );

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
	session_write_close();

	echo get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE);
	die();
}
add_action( 'wp_ajax_dcs_dropship_clear_cart', 'dcs_dropship_clearCart' );
add_action( 'wp_ajax_nopriv_dcs_dropship_clear_cart', 'dcs_dropship_clearCart' );

/**
 * Search Products.
 */
function dcs_dropship_searchProducts()
{
	check_ajax_referer( "dcs_dropship_search_products", "dcs_dropship_search_products_nonce" );

	echo get_option(DCS_DROPSHIP_PRODUCT_PAGE)."?searchTerms=".$_POST['searchTerms'];
	die();
}
add_action( 'wp_ajax_dcs_dropship_search_products', 'dcs_dropship_searchProducts' );
add_action( 'wp_ajax_nopriv_dcs_dropship_search_products', 'dcs_dropship_searchProducts' );

/**
 * Place order.
 */
function dcs_dropship_placeOrder()
{
	check_ajax_referer( "dcs_dropship_place_order", "dcs_dropship_place_order_nonce" );

	$shippingInfo = array();
	$shippingInfo['first_name'] = $_POST['first_name'];
	$shippingInfo['last_name'] = $_POST['last_name'];
	$shippingInfo['company'] = $_POST['company'];
	$shippingInfo['address'] = $_POST['address'];
	$shippingInfo['city'] = $_POST['city'];
	$shippingInfo['state'] = $_POST['state'];
	$shippingInfo['zip'] = $_POST['zip'];
	$shippingInfo['country'] = $_POST['country'];
	$shippingInfo['phone'] = $_POST['phone'];
	$shippingInfo['email'] = $_POST['email'];

	dcsLogToFile( "placeorder: " . dcsVarDumpStr($shippingInfo) );

	if( !session_id() ) session_start();
	$_SESSION['dcs_dropship_shipping_info'] = $shippingInfo;
    session_write_close();

	die();
}
add_action( 'wp_ajax_dcs_dropship_place_order', 'dcs_dropship_placeOrder' );
add_action( 'wp_ajax_nopriv_dcs_dropship_place_order', 'dcs_dropship_placeOrder' );

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
