<?php
/*
Plugin Name: DCS Dropship Plugin
Plugin URI: http://www.douglasconsulting.net
Description: A Plugin to interface with the Dropship Distribution Service. 
Version: 0.95 Beta
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

//*******************************************************************************************************************//
// Scripts and Styles 
/**
 * Nonce's for our AJAX calls.
 */
function dcs_dropship_load_scripts()
{
	//Stylesheets
	wp_register_style( 'dcs-dropship-style', plugins_url('dcs-dropship.css', __FILE__) );
	wp_enqueue_style( 'dcs-dropship-style' );

	//Scripts
	wp_enqueue_script('jquery');
	wp_enqueue_script('dcs_dropship_script', (WP_PLUGIN_URL.'/dcs-dropship/dcs-dropship.js'), array('jquery'), false, true);

    //Register nonce values we can use to verify our ajax calls from the editor.
    wp_localize_script( "dcs_dropship_script", "dcs_dropship_script_vars",
                        array(
								"ajaxurl" => admin_url('admin-ajax.php'),
                                "dcs_dropship_place_order_nonce"=>wp_create_nonce("dcs_dropship_place_order"),
                                "dcs_dropship_search_products_nonce"=>wp_create_nonce("dcs_dropship_search_products"),
                                "dcs_dropship_clear_cart_nonce"=>wp_create_nonce("dcs_dropship_clear_cart"),
                                "dcs_dropship_remove_item_nonce"=>wp_create_nonce("dcs_dropship_remove_item"),
                                "dcs_dropship_add_to_cart_nonce"=>wp_create_nonce("dcs_dropship_add_to_cart")
                            )
                      );
}
add_action('wp_enqueue_scripts', 'dcs_dropship_load_scripts');
add_action('admin_enqueue_scripts', 'dcs_dropship_load_scripts');

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
	global $dropshipFTPOutDirectory;

	//JGD: This is a good place to delete the log file, since this only happens once a month (in theory).
	unlink( DCS_DROPSHIP_LOGFILE );

	dcsLogToFile( "getProductDatabase starts." );
	$conn_id = ftp_connect( $dropshipFTPServer );
	$login_result = ftp_login( $conn_id, get_option(DCS_DROPSHIP_FTP_USER), get_option(DCS_DROPSHIP_FTP_PASSWORD) );
	dcsLogToFile( "Login results: " . $login_result );
	ftp_chdir( $conn_id, $dropshipFTPOutDirectory );
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
	else
	{
		dcsLogToFile( "Unabled to get contents of ftp directory." );
	}
	dcsLogToFile( "getProductDatabase ends." );

	for($i=0; $i<20; $i++)
	{
		dcs_dropship_loadProductsFromFile($i*PRODUCT_NUM);
	}
}
add_action( "dcs_dropship_get_products", "dcs_dropship_getProductDatabase" );
add_action( "wp_ajax_dcs_dropship_get_products", "dcs_dropship_getProductDatabase" );

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
	dcsLogToFile( "getInventory ends." );

	//Updates database
	dcs_dropship_loadInventoryFromFile();
}
add_action( "dcs_dropship_get_inventory", "dcs_dropship_getInventoryDatabase" );
add_action( "wp_ajax_dcs_dropship_get_inventory", "dcs_dropship_getInventoryDatabase" );

/**
* Get the order Invoices from dropship.
*/
function dcs_dropship_getOrderInvoices()
{
	global $dropshipFTPServer;
	global $dropshipFTPOutDirectory;

	dcsLogToFile( "getOrderInvoices starts." );
	$conn_id = ftp_connect( $dropshipFTPServer );
	$login_result = ftp_login( $conn_id, get_option(DCS_DROPSHIP_FTP_USER), get_option(DCS_DROPSHIP_FTP_PASSWORD) );
	dcsLogToFile( "Login results: " . $login_result );
	ftp_chdir( $conn_id, $dropshipFTPOutDirectory );
	$contents = ftp_nlist( $conn_id, "Order_Invoice_*.tab" );


	foreach( $contents as $file )
	{
		if( ftp_get($conn_id, DCS_DROPSHIP_DIR."files/".$file, $file, FTP_BINARY) )
		{
			dcsLogToFile( "Got invoice: " . $file );
			//JGD TODO Delete from server here.

			//JGD TODO Parse file and put in database.
			dcs_dropship_loadInvoiceFromFile( $file );
		}
		else
		{
			dcsLogToFile( "Get for: " . $file . " failed." );
		}
	}

	dcsLogToFile( "getOrderInvoices ends." );
}
add_action( "dcs_dropship_get_invoices", "dcs_dropship_getOrderInvoices" );
add_action( "wp_ajax_dcs_dropship_get_invoices", "dcs_dropship_getOrderInvoices" );

//******************************************************************************************************************************//
/** SHORTCODES **/
/**
 * Product page shortcode.
 */
function dcs_dropship_product_page_shortcode($atts, $content=null)
{
	$pageNumber = 1;
	if( isset($_GET['pageNumber']) ) 
	{
		$pageNumber = $_GET['pageNumber'];
	}

	$category = "all";
	if( isset($_GET['category']) )
	{
		$category = $_GET['category'];
	}

	$searchTerms = "";
	if( isset($_GET['searchTerms']) )
	{
		$searchTerms = $_GET['searchTerms'];
	}

	$retval = dcs_dropship_product_page($pageNumber, $category, $searchTerms);

	return $retval;
}
add_shortcode( 'dcs_dropship_product_page', 'dcs_dropship_product_page_shortcode' );

/**
 * Product Info page shortcode.
 */
function dcs_dropship_product_info_page_shortcode($atts, $content=null)
{
	$retval = dcs_dropship_product_info_page($_GET['sku']);

	return $retval;
}
add_shortcode( 'dcs_dropship_product_info_page', 'dcs_dropship_product_info_page_shortcode' );

/**
 * Product Category page shortcode.
 */
function dcs_dropship_category_page_shortcode($atts, $content=null)
{
	$retval = dcs_dropship_category_page();

	return $retval;
}
add_shortcode( 'dcs_dropship_category_page', 'dcs_dropship_category_page_shortcode' );

/**
 * Product Brand page shortcode.
 */
function dcs_dropship_brand_page_shortcode($atts, $content=null)
{
	$retval = dcs_dropship_brand_page();

	return $retval;
}
add_shortcode( 'dcs_dropship_brand_page', 'dcs_dropship_brand_page_shortcode' );

/**
 * Shopping cart shortcode.
 */ 
function dcs_dropship_shopping_cart_shortcode($atts, $content=null)
{
	$retval = dcs_dropship_shopping_cart();

	return $retval;
}
add_shortcode( 'dcs_dropship_shopping_cart', 'dcs_dropship_shopping_cart_shortcode' );

/**
 * Approved Order shortcode.
 */ 
function dcs_dropship_approved_order_shortcode($atts, $content=null)
{
	$retval = dcs_dropship_approved_order_page();

	return $retval;
}
add_shortcode( 'dcs_dropship_approved_order', 'dcs_dropship_approved_order_shortcode' );

/**
 * Declined Order shortcode.
 */ 
function dcs_dropship_declined_order_shortcode($atts, $content=null)
{
	$retval = dcs_dropship_declined_order_page();

	return $retval;
}
add_shortcode( 'dcs_dropship_declined_order', 'dcs_dropship_declined_order_shortcode' );

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

	if( !add_option(DCS_DROPSHIP_SHOPPING_CART_PAGE, site_url("/shopping-cart/")) )
	{
		update_option(DCS_DROPSHIP_SHOPPING_CART_PAGE, site_url("/dropship-test-2/shopping-cart/") );
	}

	if( !add_option(DCS_DROPSHIP_MARKUP, "50") )
	{
		update_option(DCS_DROPSHIP_MARKUP, "50");
	}

	if( !add_option(DCS_DROPSHIP_APPROVED_PAGE, site_url("/order-approved/")) )
	{
		update_option(DCS_DROPSHIP_APPROVED_PAGE, site_url("/order-approved/"));
	}

	if( !add_option(DCS_DROPSHIP_DECLINED_PAGE, site_url("/order-declined/")) )
	{
		update_option(DCS_DROPSHIP_DECLINED_PAGE, site_url("/order-declined/"));
	}

	if( !add_option(DCS_DROPSHIP_PRODUCT_PAGE, site_url("/products/")) )
	{
		update_option(DCS_DROPSHIP_PRODUCT_PAGE, site_url("/products/"));
	}

	dcs_dropship_createInvoiceDatabase();

	//Schedule our get tasks.
	if( strstr(site_url(), "darktower") != FALSE )
	{
		wp_schedule_event( DCS_DROPSHIP_PRODUCT_GET_TASK_TIME, "monthly", "dcs_dropship_get_products" );
		wp_schedule_event( DCS_DROPSHIP_PRODUCT_GET_TASK_TIME, "daily", "dcs_dropship_get_invoices" );
		wp_schedule_event( DCS_DROPSHIP_PRODUCT_GET_TASK_TIME, "daily", "dcs_dropship_get_inventory" );
	}
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

	delete_option( DCS_DROPSHIP_SHOPPING_CART_PAGE );
	delete_option( DCS_DROPSHIP_APPROVED_PAGE );
	delete_option( DCS_DROPSHIP_DECLINED_PAGE );
	delete_option( DCS_DROPSHIP_PRODUCT_PAGE );

	$wpdb->query( "DROP TABLE dcs_dropship_invoices;" );

	//Clear out tasks
	if( strstr(site_url(), "darktower") != FALSE )
	{
		$timestamp = wp_next_scheduled( "dcs_dropship_get_products" );
		wp_unschedule_event( $timestamp, "dcs_dropship_get_products" );
	
		$timestamp = wp_next_scheduled( "dcs_dropship_get_inventory" );
		wp_unschedule_event( $timestamp, "dcs_dropship_get_inventory" );
	
		$timestamp = wp_next_scheduled( "dcs_dropship_get_invoices" );
		wp_unschedule_event( $timestamp, "dcs_dropship_get_inventory" );
	}
}
register_deactivation_hook( __FILE__, 'dcs_dropship_uninstall' );

/**
 * Called on init of WordPress.
 */
function dcs_dropship_init()
{
}
add_action('init', 'dcs_dropship_init');

