<?php
/*
Plugin Name: DCS Dropship Plugin
Plugin URI: http://www.douglasconsulting.net
Description: A Plugin to interface with the Dropship Distribution Service. 
Version: 0.5 Alpha
Author: Jason Douglas
Author URI: http://www.douglasconsulting.net
License: GPL
*/

//Template Config File
require_once(dirname(__FILE__)."/config.php");

//******************************************************************************************************************************//
/** ADMIN STUFF **/
/**
 * Add our admin menu to the dashboard.
 */
function dcs_dropship_admin_menu()
{
	add_options_page( 'DCS Dropship', 'DCS Dropship', 'administrator', 'dcs_dropship', 'dcs_dropship_admin_page');
}
add_action( 'admin_menu', 'dcs_dropship_admin_menu' );

/**
 * Show the admin page.
 */ 
function dcs_dropship_admin_page()
{
	include( 'dcs-dropship-admin.php' );
}

//******************************************************************************************************************************//
//** STYLESHEETS **/
/**
 * Add our stylesheets.
 */
function dcs_dropship_add_my_stylesheet()
{
	wp_register_style( 'dcs-dropship-style', plugins_url('dcs-dropship.css', __FILE__) );
	wp_enqueue_style( 'dcs-dropship-style' );
}
add_action( 'wp_enqueue_scripts', 'dcs_dropship_add_my_stylesheet' );

//*******************************************************************************************************************//
// Tasks

/**
 * Add Monthly to the list of schedules.
 */
function dcs_dropship_cron_definer($schedules)
{  
	$schedules['monthly'] = array(      
		'interval'=> 2592000,      
		'display'=>  __('Once Every 30 Days')  
		);  
	return $schedules;
}
add_filter('cron_schedules','dcs_dropship_cron_definer');   

/**
* Get the product database from dropship.
*/
function dcs_dropship_getProductDatabase()
{
	global $dropshipFTPServer;
	global $dropshipFTPDirectory;

	dcsLogToFile( "getProductDatabase starts." );
	$conn_id = ftp_connect( $dropshipFTPServer );
	$login_result = ftp_login( $conn_id, get_option(DCS_DROPSHIP_FTP_USER), get_option(DCS_DROPSHIP_FTP_PASSWORD) );
	ftp_chdir( $conn_id, $dropshipFTPDirectory );
	$contents = ftp_nlist( $conn_id, "Product_".date("Ymd")."*.tab" );

	if( $contents[0] )
	{
		if( ftp_get($conn_id, DCS_DROPSHIP_DIR."files/Product.tab", $contents[0], FTP_BINARY) )
		{
			dcsLogToFile( "Product FTP get successful." );
		}
		else
		{
			dcsLogToFile( "Product FTP get failed." );
		}
	}
	dcsLogToFile( "getProductDatabase ends." );

	dcs_dropship_loadProductsFromFile();
}
add_action( "dcs_dropship_get_products", "dcs_dropship_getProductDatabase" );

/**
* Get the Inventory database from dropship.
*/
function dcs_dropship_getInventoryDatabase()
{
	global $dropshipFTPServer;
	global $dropshipFTPDirectory;

	dcsLogToFile( "getInventoryDatabase starts." );
	$conn_id = ftp_connect( $dropshipFTPServer );
	$login_result = ftp_login( $conn_id, get_option(DCS_DROPSHIP_FTP_USER), get_option(DCS_DROPSHIP_FTP_PASSWORD) );
	ftp_chdir( $conn_id, $dropshipFTPDirectory );
	$contents = ftp_nlist( $conn_id, "Inventory_".date("Ymd")."*.tab" );

	if( $contents[0] != "" )
	{
		if( ftp_get($conn_id, DCS_DROPSHIP_DIR."files/Inventory.tab", $contents[0], FTP_BINARY) )
		{
			dcsLogToFile( "Inventory FTP get successful." );
		}
		else
		{
			dcsLogToFile( "Inventory FTP get successful." );
		}
	}
	dcsLogToFile( "getProductInventory ends." );

	dcs_dropship_loadInventoryFromFile();
}
add_action( "dcs_dropship_get_inventory", "dcs_dropship_getInventoryDatabase" );

//******************************************************************************************************************************//
/** SHORTCODES **/
/**
 * Inventory page shortcode.
 */
function dcs_dropship_inventory_page_shortcode($atts, $content=null)
{
	$retval = dcs_dropship_inventory_page();

	return $retval;
}
add_shortcode( 'dcs_dropship_inventory_page', 'dcs_dropship_inventory_page_shortcode' );

/**
 * Order Invoice page shortcode.
 */
function dcs_dropship_order_invoice_page_shortcode($atts, $content=null)
{
	$retval = dcs_dropship_order_invoice_page();

	return $retval;
}
add_shortcode( 'dcs_dropship_order_invoice_page', 'dcs_dropship_order_invoice_page_shortcode' );

/**
 * Product page shortcode.
 */
function dcs_dropship_product_page_shortcode($atts, $content=null)
{
	//TEST CODE
	//dcs_dropship_loadProductsFromFile();
	//dcs_dropship_loadInventoryFromFile();
	//$retval = "Inventory Loaded.";
	//TEST CODE ENDS

	$retval = dcs_dropship_product_page();

	return $retval;
}
add_shortcode( 'dcs_dropship_product_page', 'dcs_dropship_product_page_shortcode' );

/**
 * Order Status page shortcode.
 */
function dcs_dropship_order_status_page_shortcode($atts, $content=null)
{
	$retval = dcs_dropship_order_status_page();

	return $retval;
}
add_shortcode( 'dcs_dropship_order_status_page', 'dcs_dropship_order_status_page_shortcode' );

/**
 * Tracking page shortcode.
 */
function dcs_dropship_tracking_page_shortcode($atts, $content=null)
{
	$retval = dcs_dropship_tracking_page();

	return $retval;
}
add_shortcode( 'dcs_dropship_tracking_page', 'dcs_dropship_tracking_page_shortcode' );

/**
 * Shopping cart shortcode.
 */ 
function dcs_dropship_shopping_cart_shortcode($atts, $content=null)
{
	$retval = dcs_dropship_shopping_cart();

	return $retval;
}
add_shortcode( 'dcs_dropship_shopping_cart', 'dcs_dropship_shopping_cart_shortcode' );

//******************************************************************************************************************************//
/** HOOKS **/
/**
 * Installer function
 */
function dcs_dropship_install()
{
	global $wpdb;

	//Install default Options
	if( !add_option(DCS_DROPSHIP_KEY, "test key") )
	{
		update_option(DCS_DROPSHIP_KEY, "test key");
	}

	if( !add_option(DCS_DROPSHIP_INVENTORY_DATA_URL, "test key") )
	{
		update_option(DCS_DROPSHIP_INVENTORY_DATA_URL, "test key");
	}
	if( !add_option(DCS_DROPSHIP_PRODUCT_DATA_URL, "test key") )
	{
		update_option(DCS_DROPSHIP_PRODUCT_DATA_URL, "test key");
	}
	if( !add_option(DCS_DROPSHIP_ORDERS_URL, "test key") )
	{
		update_option(DCS_DROPSHIP_ORDERS_URL, "test key");
	}

	if( !add_option(DCS_DROPSHIP_ORDER_STATUS_DATA_URL, "test key") )
	{
		update_option(DCS_DROPSHIP_ORDER_STATUS_DATA_URL, "test key");
	}

	if( !add_option(DCS_DROPSHIP_TRACKING_DATA_URL, "test key") )
	{
		update_option(DCS_DROPSHIP_TRACKING_DATA_URL, "test key");
	}

	if( !add_option(DCS_DROPSHIP_ORDER_INVOICE_DATA_URL, "test key") )
	{
		update_option(DCS_DROPSHIP_ORDER_INVOICE_DATA_URL, "test key");
	}

	if( !add_option(DCS_DROPSHIP_SHOPPING_CART_PAGE, "/dropship-test-2/shopping-cart/") )
	{
		update_option(DCS_DROPSHIP_SHOPPING_CART_PAGE, "/dropship-test-2/shopping-cart/");
	}

	if( !add_option(DCS_DROPSHIP_MARKUP, "50") )
	{
		update_option(DCS_DROPSHIP_MARKUP, "50");
	}

	//Schedule our get tasks.
	wp_schedule_event( DCS_DROPSHIP_PRODUCT_GET_TASK_TIME, "monthly", "dcs_dropship_get_products" );
	wp_schedule_event( DCS_DROPSHIP_PRODUCT_GET_TASK_TIME, "hourly", "dcs_dropship_get_inventory" );
}
register_activation_hook( __FILE__, 'dcs_dropship_install' );

//******************************************************************************************************************************//
/** FUNCTIONS **/
/**
 * Uninstall Function.
 */
function dcs_dropship_uninstall()
{
	global $wpdb;

	//Clear out options
	$timestamp = wp_next_scheduled( "dcs_dropship_get_products" );
	wp_unschedule_event( $timestamp, "dcs_dropship_get_products" );

	$timestamp = wp_next_scheduled( "dcs_dropship_get_inventory" );
	wp_unschedule_event( $timestamp, "dcs_dropship_get_inventory" );
}
register_deactivation_hook( __FILE__, 'dcs_dropship_uninstall' );

/**
 * Called on init of WordPress.
 */
function dcs_dropship_init()
{
}
add_action('init', 'dcs_dropship_init');

/**
 * Set up header for AJAX calls.
 */
function dcs_dropship_js_header()
{
	wp_print_scripts( array('sack') );
	?>
	<script type="text/javascript">

		//Add to shopping cart.
		function dcs_dropship_add_to_cart(marker)
		{
			try
			{
                var mysack = new sack("<?php echo DCS_DROPSHIP_CALLBACK_DIR; ?>dcs-dropship-ajax.php");
                mysack.execute = 1;
                mysack.method = 'POST';
        
                //Set the variables
                mysack.setVar("action", "AddToCart");
				var sku = document.getElementById("sku"+marker).innerHTML;
				var quantity = document.getElementById("quantity"+marker).options[document.getElementById("quantity"+marker).selectedIndex].value;
				var price = document.getElementById("price"+marker).innerHTML;
				var productName = document.getElementById("product_name"+marker).innerHTML;
				var shippingCost = document.getElementById("shipping_cost"+marker).value;

				//alert( "marker: " + marker + "\nSKU: " + sku + "\nquantity: " + quantity  + "\nprice: " + price + "\nshipping cost: " + shippingCost );

				mysack.setVar("sku", sku);
				mysack.setVar("quantity", quantity);
				mysack.setVar("price", price);
				mysack.setVar("product_name", productName);
				mysack.setVar("shipping_cost", shippingCost);
        
                mysack.onError = function() { alert('An Error occurred. Please reload the page and try again.'); };
                mysack.runAJAX();
			}
			catch(err)
			{
                var txt = "There was an error on this page.\n\n";
                txt += "Error description: " + err.message + "\n\n";
                txt += "Click OK to continue.\n\n";
                alert(txt);
			}

			return true;
		}

		//Clear the cart
		function dcs_dropship_clear_cart()
		{
			try
			{
				var mysack = new sack("<?php echo DCS_DROPSHIP_CALLBACK_DIR; ?>dcs-dropship-ajax.php");
				mysack.execute = 1;
				mysack.method = 'POST';

				//Set the variables
				mysack.setVar("action", "ClearCart");
				mysack.onError = function() { alert('An Error occurred. Please reload the page and try again.'); };
				mysack.runAJAX();
			}
			catch(err)
			{
				var txt = "There was an error on this page.\n\n";
				txt += "Error description: " + err.message + "\n\n";
				txt += "Click OK to continue.\n\n";
				alert(txt);
			}

			return true;
		}
	 
	</script>
	<?php
}
add_action('wp_head', 'dcs_dropship_js_header' );

