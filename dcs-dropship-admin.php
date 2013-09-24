<?php 

    //Vicinity Config File
    require_once(dirname(__FILE__)."/config.php");

	global $wpdb;

    $dcs_dropship_ftp_user;
    $dcs_dropship_ftp_password;
	$dcs_dropship_approved_page;
	$dcs_dropship_declined_page;
	$dcs_dropship_product_page;
    $dcs_dropship_shopping_cart_page;
	$dcs_dropship_logo_url;

    if($_POST['dcs_dropship_hidden'] == 'Y') 
    {
        //Form data sent
        $dcs_dropship_ftp_user = $_POST['dcs_dropship_ftp_user'];
        update_option(DCS_DROPSHIP_FTP_USER, $dcs_dropship_ftp_user);

        $dcs_dropship_ftp_password = $_POST['dcs_dropship_ftp_password'];
        update_option(DCS_DROPSHIP_FTP_PASSWORD, $dcs_dropship_ftp_password);

		$dcs_dropship_approved_page = $_POST['dcs_dropship_approved_page'];
		update_option(DCS_DROPSHIP_APPROVED_PAGE, $dcs_dropship_approved_page);

		$dcs_dropship_declined_page = $_POST['dcs_dropship_declined_page'];
		update_option(DCS_DROPSHIP_DECLINED_PAGE, $dcs_dropship_declined_page);

        $dcs_dropship_shopping_cart_page = $_POST['dcs_dropship_shopping_cart_page'];
        update_option(DCS_DROPSHIP_SHOPPING_CART_PAGE, $dcs_dropship_shopping_cart_page);

		$dcs_dropship_product_page = $_POST['dcs_dropship_product_page'];
		update_option(DCS_DROPSHIP_PRODUCT_PAGE, $dcs_dropship_product_page);

        $dcs_dropship_markup = $_POST['dcs_dropship_markup'];
        update_option(DCS_DROPSHIP_MARKUP, $dcs_dropship_markup);

		$dcs_dropship_logo_url = $_POST['dcs_dropship_logo_url'];
		update_option(DCS_DROPSHIP_LOGO_URL, $dcs_dropship_logo_url);

		$dcs_dropship_shipping_percentage = $_POST['dcs_dropship_shipping_percentage'];
		update_option(DCS_DROPSHIP_SHIPPING_PERCENTAGE, $dcs_dropship_shipping_percentage);

		$dcs_dropship_shipping_minimum = $_POST['dcs_dropship_shipping_minimum'];
		update_option(DCS_DROPSHIP_SHIPPING_MINIMUM, $dcs_dropship_shipping_minimum);

        //$dcs_dropship_ = $_POST['dcs_dropship_'];
        //update_option(DCS_DROPSHIP_, $dcs_dropship_);

        ?>
        <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
        <?php
    } 
    else 
    {
        //Normal page display
        $dcs_dropship_ftp_user = get_option(DCS_DROPSHIP_FTP_USER);
        $dcs_dropship_ftp_password = get_option(DCS_DROPSHIP_FTP_PASSWORD);
        $dcs_dropship_shopping_cart_page = get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE);
        $dcs_dropship_product_page = get_option(DCS_DROPSHIP_PRODUCT_PAGE);
        $dcs_dropship_markup = get_option(DCS_DROPSHIP_MARKUP);
        $dcs_dropship_logo_url = get_option(DCS_DROPSHIP_LOGO_URL);
        $dcs_dropship_shipping_percentage = get_option(DCS_DROPSHIP_SHIPPING_PERCENTAGE);
        $dcs_dropship_shipping_minimum = get_option(DCS_DROPSHIP_SHIPPING_MINIMUM);
        $dcs_dropship_approved_page = get_option(DCS_DROPSHIP_APPROVED_PAGE);
        $dcs_dropship_declined_page = get_option(DCS_DROPSHIP_DECLINED_PAGE);
        //$dcs_dropship_ = get_option(DCS_DROPSHIP_);
    }
?>

<div class="wrap">
    <?php    echo "<p style='font:bold 4.0em Verdana;vertical-align:top;'>"."<img src='http://douglasconsulting.net/favicon.ico' width='64'>"."<img src='http://dropship.com/favicon.ico' width='64'>" . __( 'DCS Dropship Options', 'dcs_dropship_trdom' ) . "</p>"; ?>
    <hr> 
    <form name="dcs_dropship_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="dcs_dropship_hidden" value="Y">
        <table>
        <tr><td><?php _e("Dropship FTP username" ); ?></td><td><input type="text" name="dcs_dropship_ftp_user" value="<?php echo $dcs_dropship_ftp_user; ?>" size="64"></td></tr>
        <tr><td><?php _e("Dropship FTP password" ); ?></td><td><input type="text" name="dcs_dropship_ftp_password" value="<?php echo $dcs_dropship_ftp_password; ?>" size="64"></td></tr>
        <tr><td><?php _e("Markup (in percentage)" ); ?></td><td><input type="text" name="dcs_dropship_markup" value="<?php echo $dcs_dropship_markup; ?>" size="2">%</td></tr> 
        <tr><td><?php _e("Shipping Percentage" ); ?></td><td><input type="text" name="dcs_dropship_shipping_percentage" value="<?php echo $dcs_dropship_shipping_percentage; ?>" size="2">%</td></tr>
        <tr><td><?php _e("Shipping Minimum" ); ?></td><td>$<input type="text" name="dcs_dropship_shipping_minimum" value="<?php echo $dcs_dropship_shipping_minimum; ?>" size="4"></td></tr>
        <tr><td><?php _e("Product Page URL" ); ?></td><td><input type="text" name="dcs_dropship_product_page" value="<?php echo $dcs_dropship_product_page; ?>" size="128"></td></tr>
        <tr><td><?php _e("Shopping Cart URL" ); ?></td><td><input type="text" name="dcs_dropship_shopping_cart_page" value="<?php echo $dcs_dropship_shopping_cart_page; ?>" size="128"></td></tr>
        <tr><td><?php _e("Order Approved URL" ); ?></td><td><input type="text" name="dcs_dropship_approved_page" value="<?php echo $dcs_dropship_approved_page; ?>" size="128"></td></tr>
        <tr><td><?php _e("Order Declined URL" ); ?></td><td><input type="text" name="dcs_dropship_declined_page" value="<?php echo $dcs_dropship_declined_page; ?>" size="128"></td></tr>
        <tr><td><?php _e("Logo URL (display in payment gateway) " ); ?></td><td><input type="text" name="dcs_dropship_logo_url" value="<?php echo $dcs_dropship_logo_url; ?>" size="128"></td></tr>
        <!-- <tr><td><?php _e(" " ); ?></td><td><input type="text" name="dcs_dropship_" value="<?php echo $dcs_dropship_; ?>" size="128"></td></tr> -->
        </table>
		
        <p class="submit">
        <input type="submit" style="border-radius:3px;" name="Submit" value="<?php _e('Update Options', 'dcs_dropship_trdom' ) ?>" />
        </p>
    </form>

	<hr> 
	<h2> Product and Inventory </h2>
	<table>
        <tr><td><?php _e("Press to force product update " ); ?></td><td><input type="button" style="border-radius:3px;" id="dcs_dropship_get_products" value="Get Products"><img class="dcs_dropship_get_products_loader" src="<?php echo DCS_DROPSHIP_CALLBACK_DIR.'res/loader.gif';?>"></td></tr>
        <tr><td><?php _e("Press to force inventory update " ); ?></td><td><input type="button" style="border-radius:3px;" id="dcs_dropship_get_inventory" value="Update Inventory"><img class="dcs_dropship_get_inventory_loader" src="<?php echo DCS_DROPSHIP_CALLBACK_DIR.'res/loader.gif';?>"></td></tr>
	</table>
	<hr>

	<h2>Invoices</h2>
	<div class="dcs_dropship_invoices">
		<table class="dcs_dropship_invoices">
			<tr><th>PO Number</th><th>Invoice ID</th><th>Bill Amount</th><th>SKU</th><th>Quantity</th><th>Ship Date</th><th>Tracking Number</th><th>Ship Cost</th></tr>
		<?php
			$invoices = $wpdb->get_results( "SELECT * from dcs_dropship_invoices;", ARRAY_A );
			foreach( $invoices as $invoice )
			{
				$rowStr = "<tr>";
				$rowStr .= "<td>".$invoices['po_number']."</td>";
				$rowStr .= "<td>".$invoices['invoice_id']."</td>";
				$rowStr .= "<td>".$invoices['bill_amount']."</td>";
				$rowStr .= "<td>".$invoices['package_item_sku']."</td>";
				$rowStr .= "<td>".$invoices['package_item_quantity']."</td>";
				$rowStr .= "<td>".$invoices['package_ship_date']."</td>";
				$rowStr .= "<td>".$invoices['package_tracking_number']."</td>";
				$rowStr .= "<td>".$invoices['package_ship_cost']."</td>";
				$rowStr .= "</tr>";
	
				echo $rowStr;
			}
		?>
		</table> 
	</div>
	<table>
    <tr><td><?php _e("Press to update Invoices " ); ?></td><td><input type="button" style="border-radius:3px;" id="dcs_dropship_get_invoices" value="Update Invoices"><img class="dcs_dropship_get_invoices_loader" src="<?php echo DCS_DROPSHIP_CALLBACK_DIR.'res/loader.gif';?>"></td></tr>
	</table>
</div>