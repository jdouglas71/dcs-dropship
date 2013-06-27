<?php

/** If Necessary, Load WordPress */
$wp_root = explode("wp-content",$_SERVER["SCRIPT_FILENAME"]);
$wp_root = $wp_root[0];
if($wp_root == $_SERVER["SCRIPT_FILENAME"]) 
{
    $wp_root = explode("index.php",$_SERVER["SCRIPT_FILENAME"]);
    $wp_root = $wp_root[0];
}

chdir($wp_root);

if(!function_exists("add_action")) 
{
	require_once(file_exists("wp-load.php")?"wp-load.php":"wp-config.php");
}
/** Load WordPress ends **/

//Vicinity Config File										 
require(dirname(__FILE__).'/config.php');

$response = "alert('".@$_POST["action"]."');";
$dataValues = $_POST;

/** Validate **/

/** Process **/

switch(@$_POST["action"])
{
	case "AddToCart":
		dcs_dropship_addToCart( $dataValues );
		$response = "window.open('".site_url(get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE))."', '_self');";
		break;
	case "ClearCart":
		dcs_dropship_clearCart();
		$response = "window.open('".site_url(get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE))."', '_self');";
		break;
	default:
}

die($response);
?>
