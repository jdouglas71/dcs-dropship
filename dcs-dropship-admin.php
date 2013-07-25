<?php 

    //Vicinity Config File
    require_once(dirname(__FILE__)."/config.php");

    $dcs_dropship_inventory_data_url;
    $dcs_dropship_product_data_url;
    $dcs_dropship_orders_url;
    $dcs_dropship_order_status_data_url;
    $dcs_dropship_tracking_data_url;
    $dcs_dropship_order_invoice_data_url;
    $dcs_dropship_ftp_user;
    $dcs_dropship_ftp_password;
    $dcs_dropship_shopping_cart_page;

    if($_POST['dcs_dropship_hidden'] == 'Y') 
    {
        //Form data sent
        $dcs_dropship_inventory_data_url = $_POST['dcs_dropship_inventory_data_url'];
        update_option(DCS_DROPSHIP_INVENTORY_DATA_URL, $dcs_dropship_inventory_data_url);

        $dcs_dropship_product_data_url = $_POST['dcs_dropship_product_data_url'];
        update_option(DCS_DROPSHIP_PRODUCT_DATA_URL, $dcs_dropship_product_data_url);

        $dcs_dropship_orders_url = $_POST['dcs_dropship_orders_url'];
        update_option(DCS_DROPSHIP_ORDERS_URL, $dcs_dropship_orders_url);

        $dcs_dropship_order_status_data_url = $_POST['dcs_dropship_order_status_data_url'];
        update_option(DCS_DROPSHIP_ORDER_STATUS_DATA_URL, $dcs_dropship_order_status_data_url);

        $dcs_dropship_tracking_data_url = $_POST['dcs_dropship_tracking_data_url'];
        update_option(DCS_DROPSHIP_TRACKING_DATA_URL, $dcs_dropship_tracking_data_url);

        $dcs_dropship_order_invoice_data_url = $_POST['dcs_dropship_order_invoice_data_url'];
        update_option(DCS_DROPSHIP_ORDER_INVOICE_DATA_URL, $dcs_dropship_order_invoice_data_url);

        $dcs_dropship_ftp_user = $_POST['dcs_dropship_ftp_user'];
        update_option(DCS_DROPSHIP_FTP_USER, $dcs_dropship_ftp_user);

        $dcs_dropship_ftp_password = $_POST['dcs_dropship_ftp_password'];
        update_option(DCS_DROPSHIP_FTP_PASSWORD, $dcs_dropship_ftp_password);

        $dcs_dropship_shopping_cart_page = $_POST['dcs_dropship_shopping_cart_page'];
        update_option(DCS_DROPSHIP_SHOPPING_CART_PAGE, $dcs_dropship_shopping_cart_page);

        $dcs_dropship_markup = $_POST['dcs_dropship_markup'];
        update_option(DCS_DROPSHIP_MARKUP, $dcs_dropship_markup);

        //$dcs_dropship_ = $_POST['dcs_dropship_'];
        //update_option(DCS_DROPSHIP_, $dcs_dropship_);

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
        $dcs_dropship_order_status_data_url = get_option(DCS_DROPSHIP_ORDER_STATUS_DATA_URL);
        $dcs_dropship_tracking_data_url = get_option(DCS_DROPSHIP_TRACKING_DATA_URL);
        $dcs_dropship_order_invoice_data_url = get_option(DCS_DROPSHIP_ORDER_INVOICE_DATA_URL);
        $dcs_dropship_ftp_user = get_option(DCS_DROPSHIP_FTP_USER);
        $dcs_dropship_ftp_password = get_option(DCS_DROPSHIP_FTP_PASSWORD);
        $dcs_dropship_shopping_cart_page = get_option(DCS_DROPSHIP_SHOPPING_CART_PAGE);
        $dcs_dropship_markup = get_option(DCS_DROPSHIP_MARKUP);
        //$dcs_dropship_ = get_option(DCS_DROPSHIP_);
    }
?>

<div class="wrap">
    <?php    echo "<p style='font:bold 4.0em Verdana;vertical-align:top;'>"."<img src='http://douglasconsulting.net/favicon.ico' width='64'>"."<img src='http://dropship.com/favicon.ico' width='64'>" . __( 'DCS Dropship Options', 'dcs_dropship_trdom' ) . "</p>"; ?>
    
    <form name="dcs_dropship_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="dcs_dropship_hidden" value="Y">
        <table>
        <!-- <tr><td><?php _e("Inventory Data URL" ); ?></td><td><input type="text" name="dcs_dropship_inventory_data_url" value="<?php echo $dcs_dropship_inventory_data_url; ?>" size="128"></td></tr>              -->
        <!-- <tr><td><?php _e("Product Data URL" ); ?></td><td><input type="text" name="dcs_dropship_product_data_url" value="<?php echo $dcs_dropship_product_data_url; ?>" size="128"></td></tr>                    -->
        <!-- <tr><td><?php _e("Orders In URL" ); ?></td><td><input type="text" name="dcs_dropship_orders_url" value="<?php echo $dcs_dropship_orders_url; ?>" size="128"></td></tr>                                   -->
        <!-- <tr><td><?php _e("Order Status Data URL" ); ?></td><td><input type="text" name="dcs_dropship_order_status_data_url" value="<?php echo $dcs_dropship_order_status_data_url; ?>" size="128"></td></tr>     -->
        <!-- <tr><td><?php _e("Tracking Data URL" ); ?></td><td><input type="text" name="dcs_dropship_tracking_data_url" value="<?php echo $dcs_dropship_tracking_data_url; ?>" size="128"></td></tr>                 -->
        <!-- <tr><td><?php _e("Order Invoice Data URL" ); ?></td><td><input type="text" name="dcs_dropship_order_invoice_data_url" value="<?php echo $dcs_dropship_order_invoice_data_url; ?>" size="128"></td></tr>  -->
        <tr><td><?php _e("Dropship FTP username" ); ?></td><td><input type="text" name="dcs_dropship_ftp_user" value="<?php echo $dcs_dropship_ftp_user; ?>" size="64"></td></tr>
        <tr><td><?php _e("Dropship FTP password" ); ?></td><td><input type="text" name="dcs_dropship_ftp_password" value="<?php echo $dcs_dropship_ftp_password; ?>" size="64"></td></tr>
        <tr><td><?php _e("Dropship Shopping Cart Page" ); ?></td><td><input type="text" name="dcs_dropship_shopping_cart_page" value="<?php echo $dcs_dropship_shopping_cart_page; ?>" size="64"></td></tr>
        <tr><td><?php _e("Markup (in percentage)" ); ?></td><td><input type="text" name="dcs_dropship_markup" value="<?php echo $dcs_dropship_markup; ?>" size="2">%</td></tr> 
        <!-- <tr><td><?php _e(": " ); ?></td><td><input type="text" name="dcs_dropship_" value="<?php echo $dcs_dropship_; ?>" size="128"></td></tr> -->
        </table>
		<input type="button" style="border-radius:3px;" id="dcs_dropship_get_products" value="Get Products">
        <p class="submit">
        <input type="submit" style="border-radius:3px;" name="Submit" value="<?php _e('Update Options', 'dcs_dropship_trdom' ) ?>" />
        </p>
    </form>
</div>