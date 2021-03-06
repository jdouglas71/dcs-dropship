<?php

global $wpdb;

/** Directories */
define('DCS_DROPSHIP_DIR', dirname(__FILE__)."/");
define('DCS_DROPSHIP_RELATIVE_DIR', "/wp-content/plugins/dcs-dropship/");
define('DCS_DROPSHIP_CALLBACK_DIR', site_url(DCS_DROPSHIP_RELATIVE_DIR));
define('DCS_DROPSHIP_CSS', DCS_DROPSHIP_CALLBACK_DIR."dcs-dropship.css");

/** Logfile */
define('DCS_DROPSHIP_LOGFILE', DCS_DROPSHIP_DIR.'DCS_DROPSHIP.log');
/**Task start time */
define('DCS_DROPSHIP_PRODUCT_GET_TASK_TIME', 1371121200 ); //6/13/13 6AM EST

/** Properties */
define(	'DCS_DROPSHIP_KEY', 'dcs-dropship-key' );
define(	'DCS_DROPSHIP_FTP_USER', 'dcs-dropship-ftp-user' );
define(	'DCS_DROPSHIP_FTP_PASSWORD', 'dcs-dropship-ftp-password' );
define(	'DCS_DROPSHIP_SHOPPING_CART_PAGE', 'dcs-dropship-shopping-cart-page' );
define(	'DCS_DROPSHIP_MARKUP', 'dcs-dropship-markup' );
define( 'DCS_DROPSHIP_APPROVED_PAGE', 'dcs-dropship-order-approved' );
define( 'DCS_DROPSHIP_DECLINED_PAGE', 'dcs-dropship-order-declined' );
define( 'DCS_DROPSHIP_PLACE_ORDER_PAGE', 'dcs-dropship-place-order' );
define( 'DCS_DROPSHIP_PRODUCT_PAGE', 'dcs-dropship-product-page' );
define( 'DCS_DROPSHIP_PRODUCT_INFO_PAGE', 'dcs-dropship-product-info-page' );
define( 'DCS_DROPSHIP_LOGO_URL', 'dcs-dropship-logo-url' );
define( 'DCS_DROPSHIP_SHIPPING_PERCENTAGE', 'dcs-dropship-shipping-percentage' );
define( 'DCS_DROPSHIP_SHIPPING_MINIMUM', 'dcs-dropship-shipping-minimum' );

/** Globals */
global $dropshipProducts;
global $dropshipInventory;
global $dropshipCategories;
global $dropshipBrands;

global $dropshipFTPServer;
$dropshipFTPServer = "ftp.dropship.com";
global $dropshipFTPOutDirectory;
$dropshipFTPOutDirectory = "out";
global $dropshipFTPInDirectory;
$dropshipFTPInDirectory = "in";

/** Product Related */
define( 'PRODUCT_TAB_FILE_NAME', DCS_DROPSHIP_DIR."files/Product.tab" );
define( 'INVENTORY_TAB_FILE_NAME', DCS_DROPSHIP_DIR."files/Inventory.tab" );
define( 'PRODUCT_NUM_LINES', 5 );
define( 'PRODUCT_NUM_COLS', 4 );
define( 'PRODUCT_NUM', 2800 );

/** Functions */
require_once(DCS_DROPSHIP_DIR.'product-functions.php');
require_once(DCS_DROPSHIP_DIR.'functions.php');

