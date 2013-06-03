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

/** Version */
$dcs_dropship_version = "0.5";

/** Properties */
define('DCS_DROPSHIP_KEY', 'dcs-dropship-key' );

/** Scripts */
require_once(DCS_DROPSHIP_DIR.'functions.php');

?>
