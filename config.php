<?php

global $wpdb;

/** Directories */
define('DCS_DROPSHIP_DIR', dirname(__FILE__)."/");
define('DCS_DROPSHIP_RELATIVE_DIR', "/wp-content/plugins/dcs-dropship/");
define('DCS_DROPSHIP_CALLBACK_DIR', get_option("siteurl").DCS_DROPSHIP_RELATIVE_DIR);
define('DCS_DROPSHIP_CSS', DCS_DROPSHIP_CALLBACK_DIR."dcs-dropship.css");

/** Logfile */
define('LOGFILE', DCS_DROPSHIP_DIR.'DCS_DROPSHIP.log');
/** WordPress Script Debug Flag */
define('SCRIPT_DEBUG', true );
/**Task start time */
define('DCS_DROPSHIP_PRODUCT_GET_TASK_TIME', 1371121200 ); //6/13/13 6AM EST

/** Version */
$dcs_dropship_version = "0.5";

/** Properties */
define('DCS_DROPSHIP_KEY', 'dcs-dropship-key' );
define('DCS_DROPSHIP_INVENTORY_DATA_URL', 'dcs-dropship-inventory-data-url' );
define('DCS_DROPSHIP_PRODUCT_DATA_URL', 'dcs-dropship-product-data-url' );
define('DCS_DROPSHIP_ORDERS_URL', 'dcs-dropship-orders-url' );
define('DCS_DROPSHIP_ORDER_STATUS_DATA_URL', 'dcs-dropship-order-status-data-url' );
define('DCS_DROPSHIP_TRACKING_DATA_URL', 'dcs-dropship-tracking-data-url' );
define('DCS_DROPSHIP_ORDER_INVOICE_DATA_URL', 'dcs-dropship-order-invoice-data-url' );
define('DCS_DROPSHIP_FTP_USER', 'dcs-dropship-ftp-user' );
define('DCS_DROPSHIP_FTP_PASSWORD', 'dcs-dropship-ftp-password' );

/** Globals */
global $dropshipProducts;
global $dropshipInventory;
global $dropshipCategories;
global $dropshipCategoryNumbers;
global $dropshipBrands;
global $dropshipBrandNumbers;

global $dropshipFTPServer;
$dropshipFTPServer = "ftp.dropship.com";
global $dropshipFTPDirectory;
$dropshipFTPDirectory = "out";

/** Scripts */
require_once(DCS_DROPSHIP_DIR.'product-functions.php');
require_once(DCS_DROPSHIP_DIR.'functions.php');

?>
