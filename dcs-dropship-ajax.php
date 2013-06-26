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

dcsLogToFile( "testing" );

/** Validate **/

/** Process **/

switch(@$_POST["action"])
{
	default:
}

die($response);
