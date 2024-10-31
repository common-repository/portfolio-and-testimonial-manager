<?php
function portfolio_ajax() {
    global $wpdb; 
    
    if ( ! defined( 'ABSPATH' ) ){
		die( 'Direct access not permitted.' );
	}
	//global vars
	$mode = portfolio_FixGetVar('m');	
	header('Content-type: application/json'); 	
	if($mode == "update-images") {
		$result = array();
		
		$session_id = portfolio_FixGetVar('sid');
		if($session_id == session_id()) {
			$portfolio_id = intval(portfolio_FixGetVar('portfolio-id'));
			$imageorder = portfolio_FixGetVar('order');
			$images = explode(",", $imageorder);
			
			// Remove all product images associated with this product
			$query = "DELETE FROM " . $wpdb->prefix . "portfolio_images WHERE portfolio_id='" . $wpdb->escape($portfolio_id) . "'";		
			$result['delete'] = $wpdb->query($query);
			
			if ($result['delete'] === false) {
				// Delete query had some problems...
				$result['failed_query'] = $query;
			} else {
				// Delete was successful
				
				// Loop through images, adding each to the portfolio_images table
				$numImages = count($images);
				for ($i = 0; $i < $numImages; $i++) {
					// Make sure this isn't an empty string
					if (strlen($images[$i]) > 0) {
					
						// Get image ID
						$imageExploded = explode("-", $images[$i]);
						$imageid = intval($imageExploded[count($imageExploded) - 1]);
						
						// Insert into database
						$result['insert-' . $i] = $wpdb->insert( 
							$wpdb->prefix . "portfolio_images", 
							array( 
								'portfolio_id' => $portfolio_id, 
								'attached_image_id' => $imageid,
								'image_order' => $i
							), 
							array( 
								'%d', 
								'%d',
								'%d'
							) 
						);
						
						// Return some extra data in the result array if the insert was unsuccessful
						if ($result['insert-' . $i] === false) {
							$result['insert-' . $i . '-error'] = "inventoryId: $portfolio_id; attached_image_id: $imageid; image_order: $i";
						}
					}
				}
			}
			
			// Return result as a JSON object
			echo json_encode($result);
		} else {
			echo(GetErrorJSON());
		}
	} else {
		echo(GetErrorJSON());	
	}
	

	
    exit;
} 


	function GetErrorJSON() {
		return "{\"ajax_status\":\"error\"}";
	}
    ?>