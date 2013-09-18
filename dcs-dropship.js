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

		var firstName = jQuery("input[name=shipping_first_name]").val();
		var lastName = jQuery("input[name=shipping_last_name]").val();
		var company = jQuery("input[name=shipping_company]").val();
		var address = jQuery("input[name=shipping_address]").val();
		var city = jQuery("input[name=shipping_city]").val();
		var state = jQuery("input[name=shipping_state]").val();
		var zip = jQuery("input[name=shipping_zip]").val();
		var country = jQuery("input[name=shipping_country]").val();
		var phone = jQuery("input[name=shipping_phone]").val();
		var email = jQuery("input[name=shipping_email]").val();

		//Validation
		if( !firstName )
		{
			alert( "The First Name field is required." );
			return;
		}

		if( !lastName )
		{
			alert( "The Last Name field is required." );
			return;
		}

		if( !address )
		{
			alert( "The Address field is required." );
			return;
		}

		if( !city )
		{
			alert( "The City field is required." );
			return;
		}

		if( !state )
		{
			alert( "The State field is required." );
			return;
		}

		if( !zip )
		{
			alert( "The Zip Code field is required." );
			return;
		}

		if( !country )
		{
			country = "United States";
		}

		//Process shipping data by filling out the values in the form being submitted to the payment gateway.
		jQuery("input[name=ShipFirstName]").attr("value", firstName);
		jQuery("input[name=ShipLastName]").attr("value", lastName);
		jQuery("input[name=ShipCompany]").attr("value", company);
		jQuery("input[name=ShipAddress]").attr("value", address);
		jQuery("input[name=ShipCity]").attr("value", city);
		jQuery("input[name=ShipState]").attr("value", state);
		jQuery("input[name=ShipZip]").attr("value", zip);
		jQuery("input[name=ShipCountry]").attr("value", country);
		jQuery("input[name=ShipPhone]").attr("value", phone);
		jQuery("input[name=ShipEMail]").attr("value", email);


		//Send the data back to the server so we can store it for sending to the dropship server.
		var data = {
			action: 'dcs_dropship_place_order',
			dcs_dropship_place_order_nonce: dcs_dropship_script_vars.dcs_dropship_place_order_nonce,
			first_name: firstName,
			last_name: lastName,
			company: company,
			address: address,
			city: city,
			state: state,
			zip: zip,
			country: country,
			phone: phone,
			email: email
		};

		jQuery.post( dcs_dropship_script_vars.ajaxurl, data, function(response) {
			//Submit the hidden payment form.
			jQuery("#dcs_dropship_payment_form").submit();
		});
	});

	/** Get Products */
	jQuery("#dcs_dropship_get_products").click(function() {

		jQuery("img.dcs_dropship_get_products_loader").show();

		var data = {
			action: 'dcs_dropship_get_products',
		};

		jQuery.post( dcs_dropship_script_vars.ajaxurl, data, function(response) {
			jQuery("img.dcs_dropship_get_products_loader").hide();
			alert( "Product Load Complete." );
		});
	});

	/** Get Invoices */
	jQuery("#dcs_dropship_get_invoices").click(function() {

		jQuery("img.dcs_dropship_get_invoices_loader").show();

		var data = {
			action: 'dcs_dropship_get_invoices',
		};

		jQuery.post( dcs_dropship_script_vars.ajaxurl, data, function(response) {
			jQuery("img.dcs_dropship_get_invoices_loader").hide();
			window.location.reload();
			alert( "Invoices Load Complete." );
		});
	});

	/** Get Inventory */
	jQuery("#dcs_dropship_get_inventory").click(function() {

		jQuery("img.dcs_dropship_get_inventory_loader").show();

		var data = {
			action: 'dcs_dropship_get_inventory',
		};

		jQuery.post( dcs_dropship_script_vars.ajaxurl, data, function(response) {
			jQuery("img.dcs_dropship_get_inventory_loader").hide();
			alert( "Inventory Load Complete." );
		});
	});

	/** Search Products */
	jQuery("#dcs_dropship_search_products").click(function() {

		var data = {
			action: 'dcs_dropship_search_products',
			searchTerms: jQuery('#dcs_dropship_search_terms').val(),
			dcs_dropship_search_products_nonce: dcs_dropship_script_vars.dcs_dropship_search_products_nonce
		};

		jQuery.post( dcs_dropship_script_vars.ajaxurl, data, function(response) {
			window.open( response, "_self" );
		});
	});

	/** Hook Global Search */
	jQuery("#searchform").submit(function() {

		var data = {
			action: 'dcs_dropship_search_products',
			searchTerms: jQuery('input#s').val(),
			dcs_dropship_search_products_nonce: dcs_dropship_script_vars.dcs_dropship_search_products_nonce
		};

		jQuery.post( dcs_dropship_script_vars.ajaxurl, data, function(response) {
			window.open( response, "_self" );
		});

		return false;
	});


});
