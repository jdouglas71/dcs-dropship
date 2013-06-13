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

//******************************************************************************************************************************//
/** HOOKS **/
/**
 * Installer function
 */
function dcs_dropship_install()
{
	global $wpdb;

	//Install default Options
	if( !add_option(DCS_DROPSHIP_VERSION, $dcs_dropship_version) )
	{
		update_option(DCS_DROPSHIP_VERSION, $dcs_dropship_version);
	}

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
	delete_option( DCS_DROPSHIP_VERSION );
}
register_deactivation_hook( __FILE__, 'dcs_dropship_uninstall' );


/**
 * Called on init of WordPress.
 */
function dcs_dropship_init()
{
	global $dcs_dropship_version;

	if( !is_admin() )
	{
		$dcs_dropship_version = get_option( DCS_DROPSHIP_VERSION );
	}
}
add_action('init', 'dcs_dropship_init');


/**
 * Set up header for AJAX calls.
 */
function dcs_dropship_js_header()
{
	wp_print_scripts( array('sack') );
	?>
	<script type='text/javascript'>

        //Add user
        function dcs_dropship_add_user()
        {
            try
            {
                document.getElementById('hs_add_account').disabled = true;
                var submit_message = document.getElementById('dcs_dropship_submit_message');
                submit_message.className = "dcs_dropship_message";
                submit_message.innerHTML = "Submitting form. Please wait...";
    
                var mysack = new sack("<?php echo DCS_DROPSHIP_CALLBACK_DIR; ?>dcs-dropship-ajax.php");
                mysack.execute = 1;
                mysack.method = 'POST';
        
                //Set the variables
                mysack.setVar("action", "AddUser");
                mysack.setVar("username", document.getElementById("hs_username").value);
                mysack.setVar("password", document.getElementById("hs_password").value);
                mysack.setVar("confirm_password", document.getElementById("hs_confirm_password").value);
        
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

?>
