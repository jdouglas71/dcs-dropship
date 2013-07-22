jQuery(document).ready(function() {
	/** Clear Cart */
	jQuery("#dcs_dropship_clear_cart").click(function() {

		var data = {
			action: 'dcs_dropship_clear_cart',
			dcs_dropship_clear_cart_nonce: dcs_dropship_script_vars.dcs_dropship_clear_cart_nonce
		};

		jQuery.post( dcs_dropship_script_vars.ajaxurl, data, function(response) {
			window.open( response, "_self" );
		});
	});

	/** Add To Cart */
	jQuery(".dcs_dropship_order_button").click(function() {

		var marker =  this.id;

		var data = {
			action: 'dcs_dropship_add_to_cart',
			dcs_dropship_add_to_cart_nonce: dcs_dropship_script_vars.dcs_dropship_add_to_cart_nonce,
			sku: jQuery('#sku'+marker).text(),
			quantity: jQuery('#quantity'+marker).val(),
			price: jQuery('#price'+marker).text(),
			product_name: jQuery('#product_name'+marker).text(),
			shipping_cost: jQuery('#shipping_cost'+marker).text()
		};

		jQuery.post( dcs_dropship_script_vars.ajaxurl, data, function(response) {
			window.open( response, "_self" );
		});
	});

	/** Place Order */
	jQuery("#dcs_dropship_place_order").click(function() {

		var data = {
			action: 'dcs_dropship_place_order',
			dcs_dropship_place_order_nonce: dcs_dropship_script_vars.dcs_dropship_place_order_nonce
		};

		jQuery.post( dcs_dropship_script_vars.ajaxurl, data, function(response) {
			alert( "PlaceOrder return: " + response );
			//window.open( response, "_self" );
		});
	});

});
