<?php 

   	//Vicinity Config File
   	require_once(dirname(__FILE__)."/config.php");

	$dcs_dropship_inventory_data_url;
	$dcs_dropship_product_data_url;
	$dcs_dropship_orders_url;

	if($_POST['dcs_dropship_hidden'] == 'Y') 
	{
		//Form data sent
		$dcs_dropship_inventory_data_url = $_POST['dcs_dropship_inventory_data_url'];
		update_option(DCS_DROPSHIP_INVENTORY_DATA_URL, $dcs_dropship_inventory_data_url);

		$dcs_dropship_product_data_url = $_POST['dcs_dropship_product_data_url'];
		update_option(DCS_DROPSHIP_PRODUCT_DATA_URL, $dcs_dropship_product_data_url);

		$dcs_dropship_orders_url = $_POST['dcs_dropship_orders_url'];
		update_option(DCS_DROPSHIP_ORDERS_URL, $dcs_dropship_orders_url);

		?>
		<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
		<?php
	} 
	else 
	{
		//Normal page display
		$dcs_dropship_inventory_data_url = get_option(DCS_DROPSHIP_INVENTORY_DATA_URL);
		$dcs_dropship_product_data_url = get_option(DCS_DROPSHIP_PRODUCT_DATA_URL);
		$dcs_dropship_orders_url = get_option(DCS_DROPSHIP_ORDERS_URL);
	}
?>

<div class="wrap">
	<?php    echo "<p style='font:bold 4.0em Verdana;vertical-align:top;'>"."<img src='http://douglasconsulting.net/favicon.ico' width='64'>"."<img src='http://dropship.com/favicon.ico' width='64'>" . __( 'DCS Dropship Options', 'dcs_dropship_trdom' ) . "</p>"; ?>
	
	<form name="dcs_dropship_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="dcs_dropship_hidden" value="Y">
		<p><?php _e("Inventory Data URL: " ); ?><input type="text" name="dcs_dropship_inventory_data_url" value="<?php echo $dcs_dropship_inventory_data_url; ?>" size="128"></p>
		<p><?php _e("Product Data URL: " ); ?><input type="text" name="dcs_dropship_product_data_url" value="<?php echo $dcs_dropship_product_data_url; ?>" size="128"></p>
		<p><?php _e("Orders In URL: " ); ?><input type="text" name="dcs_dropship_orders_url" value="<?php echo $dcs_dropship_orders_url; ?>" size="128"></p>
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Update Options', 'dcs_dropship_trdom' ) ?>" />
		</p>
	</form>
</div>