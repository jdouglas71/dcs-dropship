<?php 

   	//Vicinity Config File
   	require_once(dirname(__FILE__)."/config.php");

	$dcs_dropship_key;

	if($_POST['dcs_dropship_hidden'] == 'Y') 
	{
		//Form data sent
		$dcs_dropship_key = $_POST['dcs_dropship_key'];
		update_option(DCS_DROPSHIP_KEY, $dcs_dropship_key);
		?>
		<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
		<?php
	} 
	else 
	{
		//Normal page display
		$dcs_dropship_key = get_option(DCS_DROPSHIP_KEY);
	}
?>

<div class="wrap">
	<?php    echo "<h2>" . __( 'DCS Dropship Options', 'dcs_dropship_trdom' ) . "</h2>"; ?>
	
	<form name="dcs_dropship_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="dcs_dropship_hidden" value="Y">
		<p><?php _e("Test Key: " ); ?><input type="text" name="dcs_dropship_key" value="<?php echo $dcs_dropship_key; ?>" size="50"></p>
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Update Options', 'dcs_dropship_trdom' ) ?>" />
		</p>
	</form>
</div>