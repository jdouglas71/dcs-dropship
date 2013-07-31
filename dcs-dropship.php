<?php
/*
Plugin Name: DCS Dropship Plugin
Plugin URI: http://www.douglasconsulting.net
Description: A Plugin to interface with the Dropship Distribution Service. 
Version: 0.75 Beta
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
                                "dcs_dropship_search_products_nonce"=>wp_create_nonce("dcs_dropship_search_products"),
                                "dcs_dropship_clear_cart_nonce"=>wp_create_nonce("dcs_dropship_clear_cart"),
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
	global $dropshipFTPDirectory;

	//JGD: This is a good place to delete the log file, since this only happens once a month (in theory).
	unlink( DCS_DROPSHIP_LOGFILE );

	dcsLogToFile( "getProductDatabase starts." );
	$conn_id = ftp_connect( $dropshipFTPServer );
	$login_result = ftp_login( $conn_id, get_option(DCS_DROPSHIP_FTP_USER), get_option(DCS_DROPSHIP_FTP_PASSWORD) );
	dcsLogToFile( "Login results: " . $login_result );
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
	$pageNumber = 1;
	if( isset($_GET['pageNumber']) ) 
	{
		$pageNumber = $_GET['pageNumber'];
	}

	$retval = dcs_dropship_product_page($pageNumber);

	return $retval;
}
add_shortcode( 'dcs_dropship_product_page', 'dcs_dropship_product_page_shortcode' );

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

	//JGD TODO: THis isn't working and I don't know why. I'm punting for now and creating them manually.
	/**
	//Create the pages for the order approved and declined (if they don't already exist)
	$existingPage = get_page_by_title( "Order Approved", ARRAY_A, "page" );
	if( !$existingPage )
	{
        $page = array();
    
        $page["post_type"] = "page";
        $page["post_content"] = "[dcs_dropship_order_approved]";
        $page["post_parent"] = 0;
        $page["post_author"] = wp_get_current_user()->ID;
        $page["post_status"] = "publish";
        $page["post_title"] = "Order Approved";
        $page["comment_status"] = "closed";
        $page["ping_status"] = "closed";
        $pageid = wp_insert_post( $page );

        if( $pageid == 0 )
        {
            //Page not created.
			dcsLogToFile( "Approved Page not created!!!" );
        }
        else
        {
            //Add to excluded list
            $excluded_ids_str = get_option( "ep_exclude_pages" );
            $excluded_ids = explode( ",", $excluded_ids_str );
            array_push( $excluded_ids, $pageid );
            $excluded_ids = array_unique( $excluded_ids );
            $excluded_ids_str = implode( ",", $excluded_ids );
            delete_option( "ep_exclude_pages" );
            add_option( "ep_exclude_pages", $excluded_ids_str, __( "Comma separated list of post and page IDs to exclude when returning pages from the get_pages function.", "exclude-pages" ) );
			update_option( DCS_DROPSHIP_APPROVED_PAGE, get_permalink($pageid) );
        }
	}
	else
	{
		update_option( DCS_DROPSHIP_APPROVED_PAGE, get_permalink($existingPage) );
	}

	//Create the declined page if it doesn't exist.
	$existingPage = get_page_by_title( "Order Declined", ARRAY_A, "page" );
	if( !$existingPage )
	{
		$page = array();

		$page["post_type"] = "page";
		$page["post_content"] = "[dcs_dropship_order_declined]";
		$page["post_parent"] = 0;
		$page["post_author"] = wp_get_current_user()->ID;
		$page["post_status"] = "publish";
		$page["post_title"] = "Order Declined";
		$page["comment_status"] = "closed";
		$page["ping_status"] = "closed";
		$pageid = wp_insert_post( $page );

        if( $pageid == 0 )
        {
            //Page not created.
			dcsLogToFile( "Declined Page not created!!!" );
        }
        else
        {
            //Add to excluded list
            $excluded_ids_str = get_option( "ep_exclude_pages" );
            $excluded_ids = explode( ",", $excluded_ids_str );
            array_push( $excluded_ids, $pageid );
            $excluded_ids = array_unique( $excluded_ids );
            $excluded_ids_str = implode( ",", $excluded_ids );
            delete_option( "ep_exclude_pages" );
            add_option( "ep_exclude_pages", $excluded_ids_str, __( "Comma separated list of post and page IDs to exclude when returning pages from the get_pages function.", "exclude-pages" ) );
			update_option( DCS_DROPSHIP_DECLINED_PAGE, get_permalink($pageid) );
        }
	}
	else
	{
		update_option( DCS_DROPSHIP_DECLINED_PAGE, get_permalink($existingPage) );
	}

	//Create the Shopping Cart page if it doesn't exist.
	$existingPage = get_page_by_title( "Shopping Cart", ARRAY_A, "page" );
	if( !$existingPage )
	{
		$page = array();

		$page["post_type"] = "page";
		$page["post_content"] = "[dcs_dropship_shopping_cart]";
		$page["post_parent"] = 0;
		$page["post_author"] = wp_get_current_user()->ID;
		$page["post_status"] = "publish";
		$page["post_title"] = "Shopping Cart";
		$page["comment_status"] = "closed";
		$page["ping_status"] = "closed";
		$pageid = wp_insert_post( $page );

		if( $pageid != 0 )
		{
			update_option( DCS_DROPSHIP_SHOPPING_CART_PAGE, get_permalink($pageid) );
		}
		else
		{
			dcsLogToFile( "Shopping cart page not created!!!!" );
		}
	}
	else
	{
		update_option( DCS_DROPSHIP_SHOPPING_CART_PAGE, get_permalink($existingPage) );
	}

	dcsLogToFile( "Order Declined: " . get_option(DCS_DROPSHIP_DECLINED_PAGE) );
	dcsLogToFile( "Order Accepted: " . get_option(DCS_DROPSHIP_ACCEPTED_PAGE) );
	dcsLogToFile( "Shopping Cart: " . get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE) );
	*/
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

	delete_option( DCS_DROPSHIP_SHOPPING_CART_PAGE );
	delete_option( DCS_DROPSHIP_APPROVED_PAGE );
	delete_option( DCS_DROPSHIP_DECLINED_PAGE );
	delete_option( DCS_DROPSHIP_PRODUCT_PAGE );

	//Clear out tasks
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

