<?php

/**
 * Getter for the products.
 */ 
function dcs_dropship_getProducts()
{
	global $dropshipProducts;

	if( $dropshopProducts == NULL )
	{
		dcs_dropship_loadProducts();
	}

	return $dropshipProducts;
}

/**
 * Load the products from the database.
 */
function dcs_dropship_loadProducts($pageNumber = 1, $category="all")
{
	global $wpdb;
	global $dropshipProducts;

	//Clear out the existing pages. We don't want to overload the db.
	if( isset($dropshipProducts) )
	{
		foreach( $dropshipProducts as $product )
		{
			$existingPage = get_page_by_title( $product['sku'], ARRAY_A, "page" );
			if( !$existingPage )
			{
				wp_delete_post( $existingPage['ID'], true );
			}
		}
	}

	//Limits.
	$start = PRODUCT_NUM_COLS*PRODUCT_NUM_LINES*($pageNumber-1);
	$end = (PRODUCT_NUM_COLS*PRODUCT_NUM_LINES*$pageNumber)+1;

	//Conditions
	$condition = "";
	if( $category != "all" )
	{
		$category = " WHERE category LIKE (".$category."%)";
	}

	$sql = "SELECT * FROM dcs_dropship_products LIMIT ".$start.",".$end.$condition.";";
	dcsLogToFile( "LoadProducts SQL: " . $sql );

	//Load the products from the database
	$dropshipProducts = $wpdb->get_results( $sql, ARRAY_A );
	asort( $dropshipProducts );
}

/**
 * Load the brands and categories.
 */ 
function dcs_dropship_loadBrandsAndCats()
{
	global $wpdb;
	global $dropshipCategories;
	global $dropshipBrands;

	//Reset the globals
	$dropshipCategories = array();
	$dropshipBrands = array();

	//Load the categories
	$catObjs = $wpdb->get_results( "SELECT * FROM dcs_dropship_product_categories;", ARRAY_A ); 
	foreach( $catObjs as $catObj )
	{
		$parts = explode( "|", $catObj['category'] );
		if( !array_key_exists($parts[0], $dropshipCategories) )
		{
			$dropshipCategories[$parts[0]] = array();
		}
		if( isset($parts[1]) )
		{
			$dropshipCategories[$parts[0]][] = $parts[1];
		}
	}

	//Load the brands
	$dropshipBrands = $wpdb->get_results( "SELECT * FROM dcs_dropship_product_brands;", ARRAY_A );
}

/**
 * Display Products in a table. Mostly for debug purposes.
 */
function dcs_dropship_generateProductTable($showKeys=NULL)
{
	$dropshipProducts = dcs_dropship_getProducts();

	$retval = "<table class='dcs_dropship_product_table'>";
	$numLines = 1;

	//$retval .= dcsVarDumpStr( $dropshipProducts );

	//The header
	$retval .= "<tr>";
	foreach( $dropshipProducts[1] as $key=>$value )
	{
		if( $showKeys == NULL || in_array($key,$showKeys) )
		{
			$retval .= "<th>".$key."</th>";
		}
	}
	$retval .= "</tr>";

	foreach( $dropshipProducts as $product )
	{
		$retval .= "<tr>";
		foreach( $product as $key=>$value )
		{
			if( $showKeys == NULL || in_array($key,$showKeys) )
			{
				$retval .= "<td>".$value."</td>"; 
			}
		}
		$retval .= "</tr>";
		$numLines++;
	}

	$retval .= "</table>";
	return $retval;
}

/**
 * Display Products Categories in a table.
 */
function dcs_dropship_generateProductCategoryTable()
{                               
	global $wpdb;
	global $dropshipCategories;
	$retval = "";

	if( !isset($dropshipCategories) )
	{
		dcs_dropship_loadBrandsAndCats();
	}

	ksort( $dropshipCategories );

	foreach( $dropshipCategories as $category=>$subCats )
	{
		$sql = "SELECT COUNT(*) from dcs_dropship_products where category like '".$category."%';";
		$result = $wpdb->get_results( $sql, ARRAY_A );
		$retval .= "<h2>$category (".$result[0]['COUNT(*)'].")</h2>";
	}

	return $retval;
}

/**
 * Display Products Brands in a table.
 */
function dcs_dropship_generateProductBrandTable()
{
	global $dropshipBrands;

	if( !isset($dropshipBrands) )
	{
		dcs_dropship_loadBrandsAndCats();
	}

	$brands = array();

	foreach( $dropshipBrands as $brand )
	{
		$brandDisplay = $brand['brand'];
		if( $brandDisplay == "" )
		{
			$brandDisplay = "n/a";
		}
		$brands[] = $brandDisplay;
	}

	asort( $brands );

	foreach( $brands as $brand )
	{
		$retval .= "<h2>$brand</h2>";
	}

	return $retval;
}

/**
 * Pretty Product Table.
 */
function dcs_dropship_generatePrettyProductTable()
{
	$dropshipProducts = dcs_dropship_getProducts();
	$retval = "<table cellpadding='1' class='dcs_dropship_product_table'>";

	$numCols = 1;
	$numLines = 1;

	foreach( $dropshipProducts as $product )
	{
		if( $product['status'] == "in-stock" )
		{
			if( $numCols == 1 )
			{
				$retval .= "<tr>";
			}
			$retval .= dcs_dropship_generateProductCell( $product );
			if( $numCols == PRODUCT_NUM_COLS )
			{
				$retval .= "</tr>";
				$numCols = 0;
				$numLines++;
				if( $numLines > PRODUCT_NUM_LINES )
				{
					break;
				}
			}
			$numCols++;
		}
	}

	$retval .= "</table>";



	return $retval;
}

/**
 * Generate a table cell for the given product.                         
 */
function dcs_dropship_generateProductCell($product)
{
	$retval = "";

	dcs_dropship_createPageForProduct( $product );
	$existingPage = get_page_by_title( $product['sku'], ARRAY_A, "page" );
	$company_url = $existingPage['guid'];
	$markedupPrice = sprintf("%01.2f", ($product['wholesale_cost']*(1+(get_option(DCS_DROPSHIP_MARKUP)/100))));
	$marker = $product['sku'];
	$productImage = $product['product_image'];
	if( $productImage == "" )
	{
		$productImage = plugins_url("/dcs-dropship/res/noImage.png");
	}
	else
	{
		$productImage = $productImage . "?maxX=64&maxY=64";
	}

	$retval .= "<td class='dcs_dropship_product'>";
	$retval .= "<form id='dcs_dropship_product' method='POST'>";
	$retval .= "<div class='dcs_dropship_product'>";
	$retval .= "<input type='hidden' class='dcs_dropship_product_hidden'>";
	$retval .= "<div class='dcs_dropship_product_top_div'>";
	$retval .= "<div class='dcs_dropship_product_title'><span id='product_name".$marker."'>".$product['product_title']."</span></div><br />";
	$retval .= "<div class='dcs_dropship_product_img_div'>";
	$retval .= "<a href='".$company_url."'><img class='dcs_dropship_product' src='".$productImage."'></a><br />";
	$retval .= "</div>";
	$retval .= "<div class='dcs_dropship_product_text'>";
	$retval .= "<span>SKU:<span id='sku".$marker."'> ".$product['sku']."</span></span><br />";
	$retval .= "<span>Quantity: ".$product['quantity_available']."</span><br />";
	$retval .= "<div class='dcs_dropship_product_price'><span id='price".$marker."'>$".$markedupPrice."</span><br />";
	$retval .= "</div>";
	$retval .= "</div>";
	$retval .= "<hr class='dcs_dropship_line'>";
	$retval .= "<div class='dcs_dropship_product_order'>";
	$retval .= "Number <select id='quantity".$marker."'>";
	for($i = 1; $i <= $product['quantity_available']; $i++)
	{
		$retval .= "<option value='".$i."'>".$i."</option>";
	}
	$retval .= "</select>";
	$retval .= "<input type='button' id='".$marker."' value='Order' class='dcs_dropship_order_button'></input>";
	$retval .= "</div>";
	$retval .= "<div class='dcs_dropship_product_decoration'><!-- decorative --></div>";
	$retval .= "</div>";
	$retval .= "<input type='hidden' id='shipping_cost".$marker."' value='".$product['estimated_shipping_cost']."'>";
	$retval .= "</form>";
	$retval .= "</td>";

	return $retval;
}

/**
 * Generate a Page for the given product.                         
 */
function dcs_dropship_generateProductPage($product)
{
	$marker = $product['sku'];
	$markedupPrice = sprintf("%01.2f", ($product['wholesale_cost']*(1+(get_option(DCS_DROPSHIP_MARKUP)/100))));
	$productImage = $product['product_image'];
	if( $productImage == "" )
	{
		$productImage = plugins_url("/dcs-dropship/res/noImage.png");
	}
	else
	{
		$productImage = $productImage . "?maxY=256";
	}

	$retval = "";

	$retval .= "<div class='dcs_dropship_product'>";
	$retval .= "<input type='hidden' class='dcs_dropship_product_hidden'>";
	$retval .= "<div class='dcs_dropship_product_top_div'>";
	$retval .= "<div class='dcs_dropship_product_page_title'><span id='product_name".$marker."'>".$product['product_title']."</span></div><br />";
	$retval .= "<div class='dcs_dropship_product_img_div'>";
	$retval .= "<img class='dcs_dropship_product_page' src='".$productImage."'><br />";
	$retval .= "</div>";
	$retval .= "<div class='dcs_dropship_product_text'>";

	$retval .= "<span>SKU:<span id='sku".$marker."'> ".$product['sku']."</span></span><br />";
	$retval .= "<span>Quantity: ".$product['quantity_available']."</span><br />";
	$retval .= "<div class='dcs_dropship_product_price'><span id='price".$marker."'>$".$markedupPrice."</span>";
	$retval .= "</div>";
	$retval .= "</div>";
	$retval .= "<hr class='dcs_dropship_line'>";
	$retval .= "<div class='dcs_dropship_product_order'>";
	$retval .= "Number <select id='quantity".$marker."'>";
	for($i = 1; $i <= $product['quantity_available']; $i++)
	{
		$retval .= "<option value='".$i."'>".$i."</option>";
	}
	$retval .= "</select>&nbsp;&nbsp;&nbsp;&nbsp;";
	$retval .= "<input type='button' id='".$marker."' value='Order' class='dcs_dropship_order_button'></input>";
	$retval .= "</div>";

	$retval .= "<input type='hidden' id='shipping_cost".$marker."' value='".$product['estimated_shipping_cost']."'>";
	$retval .= "<div class='dcs_dropship_product_decoration'><!-- decorative --></div>";
	$retval .= "</div>";

	$retval .= "<div class='dcs_dropship_product_info'>";
	$retval .= "<div class='dcs_dropship_product_page_title'>Details</div><br />";
	$retval .= "<b>Product Title:</b> ".$product['product_title']."<br />";
	$retval .= "<b>Product Group:</b> ".$product['product_group']."<br />";
	$retval .= "<b>Category:</b> ".$product['category']."<br />";
	$retval .= "<b>Brand:</b> ".$product['brand']."<br />";
	$retval .= "<b>Manufacturer:</b> ".$product['manufacturer']."<br />";
	$retval .= "<b>Description:</b> ".$product['long_description']."<br />";
	$retval .= "</div>";
	$retval .= "<div class='dcs_dropship_product_decoration'><!-- decorative --></div>";
	$retval .= "</div>";

	//dcsLogToFile( $retval );

	return $retval;
}

/**
 * Create page for product.
 */
function dcs_dropship_createPageForProduct($product)
{
	//Global for WordPress database.
	global $wpdb;

	$existingPage = get_page_by_title( $product['sku'], ARRAY_A, "page" );

	if( !$existingPage )
	{
		$page = array();
	
		$page["post_type"] = "page";
		$page["post_content"] = dcs_dropship_generateProductPage($product);
		$page["post_parent"] = 0;
		$page["post_author"] = wp_get_current_user()->ID;
		$page["post_status"] = "publish";
		$page["post_title"] = $product['sku'];
		$page["comment_status"] = "closed";
		$page["ping_status"] = "closed";
		$pageid = wp_insert_post( $page );
	
		if( $pageid == 0 )
		{
			//Page not created.
		}
		else
		{
			//Add to excluded list
			$excluded_ids_str = get_option( "ep_exclude_pages" );
			$excluded_ids = explode( ",", $excluded_ids_str );
			array_push( $excluded_ids, $pageid );
			$excluded_ids = array_unique( $excluded_ids );
			$excluded_ids_str = implode( ",", $excluded_ids );
			delete_option( "ep_exclude_pages" );
			add_option( "ep_exclude_pages", $excluded_ids_str, __( "Comma separated list of post and page IDs to exclude when returning pages from the get_pages function.", "exclude-pages" ) );
		}
	}
	else
	{
		//Page exists. Make sure the content is updated.
		$page = array();
		$page['ID'] = $existingPage['ID'];

		$page['post_content'] = dcs_dropship_generateProductPage($product);

		wp_update_post( $page );
	}
}

/**
 * Parse the product file, creates and loads the database.
 */
function dcs_dropship_loadProductsFromFile($startLine = 0)
{
	global $wpdb;
	
	$useKeys = array( "sku",
					  "category",
					  "brand",
					  "status",
					  "product_title",
					  "product_image",
					  "quantity_available",
					  "product_cost",
					  "manufacturer",
					  "long_description",
					  "estimated_shipping_cost",
					  "street_price",
					  "wholesale_cost",
					  "user_defined_name_1",
					  "user_defined_value_1",
					  "user_defined_name_2",
					  "user_defined_value_2",
					  "user_defined_name_3",
					  "user_defined_value_3",
					  "user_defined_name_4",
					  "user_defined_value_4",
					  "user_defined_name_5",
					  "user_defined_value_5",
					  "user_defined_name_6",
					  "user_defined_value_6",
					  "user_defined_name_7",
					  "user_defined_value_7",
					  "user_defined_name_8",
					  "user_defined_value_8",
					  "user_defined_name_9",
					  "user_defined_value_9",
					  "user_defined_name_10",
					  "user_defined_value_10"
					);

	$retval = "";
	$categories = array();
	$brands = array();

	dcsLogToFile( "LoadProductsFromFile begins for startLine: $startLine." );

	if( $startLine == 0) 
	{
		dcs_dropship_createProductDatabase( array(), $useKeys );
	}

    $file_handle = fopen(PRODUCT_TAB_FILE_NAME, "r");
	if( $file_handle != false )
	{
		$numLines = 1;
		$numCols = 0;
		$keys = array();

		//Get the keys from the first line
		$keyLine = fgets($file_handle);
		$keyLine = str_replace( array("\t") , array("[tAbul*Ator]") , $keyLine ); 
		foreach( explode("[tAbul*Ator]", $keyLine) as $li ) 
		{
			$keys[] = trim($li);
		}

		//Spin to start line
		while( !feof($file_handle) && ($numLines <= $startLine) )
		{
			fgets($file_handle);
			$numLines++;
		}

		//Reset the numLines counter.
		$numLines = 1;
	
		while( !feof($file_handle) && ($numLines <= PRODUCT_NUM) )
		{
			$line = fgets($file_handle);
			$line = str_replace( array("\t") , array("[tAbul*Ator]") , $line ); 
			$lineVals = array();

			//dcsLogToFile( "line: " . $line );
	
			//Parse the line.
			foreach( explode("[tAbul*Ator]", $line) as $li ) 
			{ 
				$lineVals[$keys[$numCols]] = trim($li);
				$numCols++;
			} 

			//dcsLogToFile( "Parsed Line: " . dcsVarDumpStr($lineVals) );

			//Let's not bother with discontinued products, products with blank or zero costs, or zero quantity
			if( ($lineVals['status'] != "discontinued") ||
				($lineVals['wholesale_cost'] == "") ||
				($lineVals['quantity'] == "0") )
			{
				//First line contains the keys, the rest is values.
				if( $numLines > 0 )
				{
					dcs_dropship_insertProductIntoDatabase( $lineVals, $useKeys );

					if( (!in_array($lineVals['category'], $categories)) )
					{
						//dcsLogToFile( "category: " . $lineVals['category'] );
						$categories[] = $lineVals['category'];
					}

					if( (!in_array($lineVals['brand'], $brands)) )
					{
						//dcsLogToFile( "brand: " . $lineVals['brand'] );
						$brands[] = $lineVals['brand'];
					}
				}

				$numLines++;
			}
			$numCols = 0;
		}

		//Update categories table
		foreach( $categories as $category )
		{
			if( $category != "" )  
			{
				$sql = "INSERT IGNORE into dcs_dropship_product_categories (category) VALUES ('".$category."');";
				//dcsLogToFile( "Insert Category sql: " . $sql );
				$result = $wpdb->query( $sql );
				//dcsLogToFile( "result: " . $result );
			}
		}

		//Update brands table
		foreach( $brands as $brand )
		{
			if( $brand != "" )
			{
				$sql = "INSERT IGNORE into dcs_dropship_product_brands (brand) VALUES ('".$brand."');";
				//dcsLogToFile( "Insert brand sql: " . $sql );
				$result = $wpdb->query( $sql );
				//dcsLogToFile( "result: " . $result );
			}
		}
	}
	else
	{
		dcsLogToFile( "Error opening " . PRODUCT_TAB_FILE_NAME );
	}

	dcsLogToFile( "LoadProductsFromFile ends..." );

	return $retval;
}

/**
 * Parse the Inventory file, update the database.
 */
function dcs_dropship_loadInventoryFromFile()
{
	global $wpdb;

	$useKeys = array( "sku",
					  "status",
					  "quantity_available",
					  "product_cost",
					  "wholesale_cost",
					  "street_price"
					);

	$retval = "";

    $file_handle = fopen(INVENTORY_TAB_FILE_NAME, "r");
	$numLines = 0;
	$numCols = 0;
	$keys = array();

	while( !feof($file_handle) )
	{
		$line = fgets($file_handle);
		$line = str_replace( array("\t") , array("[tAbul*Ator]") , $line ); 
		$lineVals = array();

		//dcsLogToFile( "Line: " . $line );

		//Parse the line.
		foreach( explode("[tAbul*Ator]", $line) as $li ) 
		{ 
			if( $numLines == 0 )
			{
				$keys[] = trim($li);
			}
			else
			{
				$lineVals[$keys[$numCols]] = trim($li);
			}
			$numCols++;
		} 

		//dcsLogToFile( "Inventory lineVals: " . dcsVarDumpStr(lineVals) );

		//Let's not bother with discontinued products.
		if( $lineVals['status'] != "discontinued" )
		{
			//First line contains the keys, the rest is values.
			if( $numLines > 0 )
			{
				//Update the database
				$sql = "UPDATE dcs_dropship_products SET ";
				foreach( $lineVals as $key => $val )
				{
					if( ($key != 'sku') && (in_array($key, $useKeys)) && ($val != "") )
					{
						$sql .= "$key = '$val',";
					}
				}
				$sql = substr( $sql, 0, strlen($sql)-1 );
				$sql .= " WHERE sku='".$lineVals['sku']."';";
				//dcsLogToFile( "Update sql: " . $sql );
				$result = $wpdb->query( $sql );
				//dcsLogToFile( "Update result: " . $result );
			}

			$numLines++;
		}
		$numCols = 0;
	}

	//$retval .= dcsVarDumpStr( $dropshipProducts ) . "<br />";

	return $retval;
}

/**
 * Create the product database using the passed in keys.
 */
function dcs_dropship_createProductDatabase($products, $useKeys, $dropTable = true)
{
	global $wpdb;

	dcsLogToFile( "createProductDatabase begins..." );
	//Delete existing table if it exists.
	if( $dropTable )
	{
		$result = $wpdb->query( "DROP TABLE dcs_dropship_products;" );
		dcsLogToFile( "Drop table Result: " . $result . PHP_EOL );
		$wpdb->print_error();

		$result = $wpdb->query( "DROP TABLE dcs_dropship_product_categories;" );
		dcsLogToFile( "Drop table Result: " . $result . PHP_EOL );
		$wpdb->print_error();

		$result = $wpdb->query( "DROP TABLE dcs_dropship_product_brands;" );
		dcsLogToFile( "Drop table Result: " . $result . PHP_EOL );
		$wpdb->print_error();
	}

	//Create the table.
	$sql = "CREATE TABLE dcs_dropship_products ( ";
	foreach( $useKeys as $key )
	{
		$sql .= " $key varchar(812),";
	}
	$sql .= " PRIMARY KEY(sku) );";
	dcsLogToFile( "Create table SQL: " . $sql );
	$result = $wpdb->query( $sql );
	dcsLogToFile( "Create Table result: " . $result );

	$sql = "CREATE TABLE dcs_dropship_product_categories ( ";
	$sql .= "id int not null auto_increment,";
	$sql .= "category varchar(255) not null,";
	$sql .= " PRIMARY KEY(id), UNIQUE(category) );";
	dcsLogToFile( "Create table SQL: " . $sql );
	$result = $wpdb->query( $sql );
	dcsLogToFile( "Create Table result: " . $result );

	$sql = "CREATE TABLE dcs_dropship_product_brands ( ";
	$sql .= "id int not null auto_increment,";
	$sql .= "brand varchar(255) not null,";
	$sql .= " PRIMARY KEY(id), UNIQUE(brand) );";
	dcsLogToFile( "Create table SQL: " . $sql );
	$result = $wpdb->query( $sql );
	dcsLogToFile( "Create Table result: " . $result );

	dcsLogToFile( "createProductDatabase ends..." );
}

/**
 * Insert a product into the database.
 */
function dcs_dropship_insertProductIntoDatabase($product, $useKeys)
{
	global $wpdb;

	//Create the keyStr
	$keyStr = "(";
	foreach( $useKeys as $key )
	{
		$keyStr .= $key.",";
	}
	$keyStr = substr( $keyStr, 0, strlen($keyStr)-1 );
	$keyStr .= ") ";

	$sql = "INSERT INTO dcs_dropship_products ";
	$valStr = "(";
	foreach( $useKeys as $key )
	{
		$valStr .= "'".$product[$key]."',";
	}
	$valStr = substr( $valStr, 0, strlen($valStr)-1 );
	$valStr .= ");";

	$sql .= $keyStr . " VALUES " . $valStr;
	//dcsLogToFile( "Insert statement: " . $sql );
	$result = $wpdb->query( $sql );
	//dcsLogToFile( "Insert result: " . $result );
}

/**
 * Logging to file.                                       
 */
if( !function_exists("dcsLogToFile") )
{
	function dcsLogToFile($msg)
	{ 
		// open file
		$fd = fopen(DCS_DROPSHIP_LOGFILE, "a");
		// append date/time to message
		$str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . PHP_EOL . $msg; 
		// write string
		fwrite($fd, $str . PHP_EOL);
		// close file
		fclose($fd);
	}
}

/**
 * Dump array/object into a string.
 */
if( !function_exists("dcsVarDumpStr") )
{
	function dcsVarDumpStr($var)
	{
		ob_start();
		var_dump( $var );
		$out = ob_get_clean();
		return $out;
	}
}
