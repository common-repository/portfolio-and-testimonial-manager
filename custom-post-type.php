<?php

add_action('init', 'portfolio_create_custom_post_type');
add_action('after_setup_theme','portfolio_setup_post_thumbnails', 999);
add_action('manage_posts_custom_column', 'manage_custom_columns', 10, 2);
add_filter('manage_edit_portfolio_columns', 'add_new_portfolio_columns');
add_filter('manage_edit_portfolio_sortable_columns', 'portfolio_sortable_columns' );
add_filter('request', 'portfolio_sortable_columns_orderby' );
add_filter('post_updated_messages', 'portfolio_updated_messages');
add_action('admin_init', 'portfolio_meta_init');
add_action('before_delete_post', 'portfolio_delete_portfolio');

define('SCRIPT_DEBUG', true);

function portfolio_create_custom_post_type() {
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-datepicker');

	$labels = array(
		'name' => __('portfolios', 'portfolio'),
		'singular_name' => __('Portfolio', 'portfolio'),
		'add_new' => __('Add New Portfolio', 'portfolio'),
		'add_new_item' => __('Add New Portfolio', 'portfolio'),
		'all_items' => __('Manage Portfolios', 'portfolio'),
		'edit_item' => __('Edit Portfolio', 'portfolio'),
		'new_item' => __('New Portfolio', 'portfolio'),
		'view_item' => __('View Portfolio', 'portfolio'),
		'menu_name' => __('Portfolio', 'portfolio'),
		'not_found' =>  __('No Portfolios Found', 'portfolio'),
		'not_found_in_trash' => __('No Portfolios Found in Trash', 'portfolio'),
		'search_items' => __('Search Portfolios', 'portfolio'),
		'parent_item_colon' => ''
	);

    $post_type_support = array('title', 'editor');
	$custom_post_slug = "portfolio";
    
	if(defined('PORTFOLIO_CUSTOM_POST_TYPE_SLUG') && PORTFOLIO_CUSTOM_POST_TYPE_SLUG != "") {
		$custom_post_slug = PORTFOLIO_CUSTOM_POST_TYPE_SLUG;
	}
    
	register_post_type(PORTFOLIO_CUSTOM_POST_TYPE, array(
		'labels' => $labels,
		'description' => __('Portfolios', 'portfolio'),
		'public' => true,
		'show_ui' => true,
		'capability_type' => 'page',
		'hierarchical' => false,
		'supports' => $post_type_support,
		'rewrite' => array("slug" => $custom_post_slug),
		'menu_icon' => plugins_url('/img/icon_portfolio.png', __FILE__)
	));
}

function portfolio_delete_portfolio($postid) {
	global $post, $wpdb;
	//check post type
	if(get_post_type($postid) == PORTFOLIO_CUSTOM_POST_TYPE) {
		//set inventory id
		$portfolio_id = $postid;
        //delete inventory categories
		$wpdb->query("DELETE FROM " . $wpdb->prefix . "portfolio_to_category WHERE portfolio_id='" . $portfolio_id . "'");
	}
}

function portfolio_sortable_columns( $columns ) {
	$columns['clientname'] = 'clientname';
	$columns['id'] = 'id';
	return $columns;
}

function portfolio_sortable_columns_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'clientname' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => '_clientname',
			'orderby' => 'meta_value'
		) );
	}

	return $vars;
}

function add_new_portfolio_columns($cols) {
	$new_columns['cb'] = '<input type="checkbox" />';
	$new_columns['id'] = __('ID', 'portfolio');
	$new_columns['title'] = __('Portfolio Title', 'portfolio');
	$new_columns['description'] = __('Description', 'portfolio');
	$new_columns['clientname'] = __('Client Name', 'portfolio');
	$new_columns['portfolioimage'] = __('Image', 'portfolio');
	$new_columns['date'] = __('Date', 'portfolio');
	return $new_columns;
}

function manage_custom_columns($column_name, $id) {
    echo $column_name;
	global $wpdb;
	switch ($column_name) {
		case 'id':
			echo $id;
			break;
		case 'description':
			$description = get_post($id)->post_content;
			echo portfolio_TruncateString(stripslashes(strip_tags($description)), 25);
			break;
		case 'clientname':
			$clientname = get_post_meta($id, "_clientname", true);
			echo ($clientname ? $clientname : 'n/a');
			break;
		case 'portfolioimage':
			$src = "";
			if (has_post_thumbnail($id)) {
				// Get featured image if it exists
				$featuredImage = wp_get_attachment_image_src(get_post_thumbnail_id($id), "thumbnail");
				$src = $featuredImage[0];
			} else {
				// Use first valid image
				$inventory_images = $wpdb->get_results("SELECT attached_image_id, image_id FROM " . $wpdb->prefix . "portfolio_images WHERE portfolio_id = " . $id . " ORDER BY image_order ASC");
				foreach ( $inventory_images as $image ) {
					if ($image != null) {
						// Check to see if image has been deleted
						if (get_post($image->attached_image_id) == null) {
							// Delete image attachment
							portfolio_RemoveInventoryImage($image->image_id);
						} else {
							$featuredImage = wp_get_attachment_image_src($image->attached_image_id, "full");
							$src = $featuredImage[0];
							break; // Exit foreach loop
						}
					}
				}

				// Use default portfolio image if no attached image was found
				if ($src == "") {
					$src = plugins_url( 'img/' . INVENTORY_DEFAULT_IMAGE , __FILE__ );
				}
			}

			echo '<a href="post.php?post=' . $id . '&amp;action=edit"><img src="' . $src . '" style="max-height:32px; max-width:40px;" /></a>';

			break;
		default:
	}
}


function portfolio_setup_post_thumbnails() {
	add_theme_support('post-thumbnails');
}

function portfolio_updated_messages($messages) {
	global $post, $post_ID;
	$messages[PORTFOLIO_CUSTOM_POST_TYPE] = array(
		1 => __('Portfolio updated.', 'portfolio') . ' <a href="'.esc_url(get_permalink($post_ID)).'">' . __('View portfolio', 'portfolio') . '</a>',
		2 => __('Custom field updated.'),
		3 => __('Custom field deleted.'),
		4 => __('Portfolio updated.'),
		6 => 'Portfolio published. <a href="' . esc_url(get_permalink($post_ID)) . '">' . __('View portfolio', 'portfolio') . '</a>',
		7 => __('Portfolio saved.'),
		8 => 'Portfolio submitted. <a target="_blank" href="'.esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))).'">' . __('Preview portfolio', 'portfolio') . '</a>',
		9 => 'Portfolio scheduled for: <strong>'.date_i18n( __("", "portfolio"), strtotime($post->post_date)).'</strong>. <a target="_blank" href="'.esc_url(get_permalink($post_ID)).'">' . __('Preview portfolio', 'portfolio') . '</a>',
		10 => 'Portfolio draft updated. <a target="_blank" href="'.esc_url(add_query_arg( 'preview', 'true', get_permalink($post_ID))).'">' . __('Preview portfolio', 'portfolio') . '</a>'
	);
	return $messages;
}

function portfolio_meta_init() {
  	global $wpdb;

  	add_meta_box('portfolio_details_meta', 'Portfolio Details', 'portfolio_details_setup', PORTFOLIO_CUSTOM_POST_TYPE, 'normal', 'high');

  	add_meta_box('portfolio_images_meta', 'Portfolio Images', 'portfolio_images_setup', PORTFOLIO_CUSTOM_POST_TYPE, 'normal', 'high');

    remove_meta_box( 'postimagediv', 'portfolio', 'side' );

  	add_action('save_post','portfolio_meta_save');
}

function portfolio_images_setup() {
	global $post, $wpdb;
	
	// Check for featured image
	if (has_post_thumbnail($post->ID)) {
		$featuredImage = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), "thumbnail");
		$src = $featuredImage[0]; ?>
		<div class='subhead'><?php _e('Featured Portfolio Image', 'portfolio'); ?></div>
		<div class='PhotoWrapper'><img src="<?php echo $src; ?>" style="max-width:150px;"></div>
		<?php
	}
	
	// Check for current images
	$current_images = "";
	$images = $wpdb->get_results("SELECT attached_image_id, image_id FROM " . $wpdb->prefix . "portfolio_images WHERE portfolio_id = " . $post->ID . " ORDER BY image_order ASC");
	
	foreach ( $images as $image ) {
		// Check to see if image has been deleted
		if (get_post($image->attached_image_id) == null) {
			// Delete image attachment
			portfolio_RemoveInventoryImage($image->image_id);
		} else {
			$current_images .= "<li id='portfolio-image-" . $image->attached_image_id . "' class='CreatePhoto'><div class='PhotoWrapper'><img src='" . wp_get_attachment_thumb_url($image->attached_image_id) . "' style='max-width:150px;' /><div id='remove-image-" . $image->attached_image_id . "' class='remove-image'><img src='" . plugins_url( 'img/x.png' , __FILE__ ) . "' /></div></div></li>";
		}
	} ?>
   <!--<div id="AddPortfolioImageButtonDiv"><a class="fp-open-media button" href="#" target="wp-preview" id="post-preview">Add Portfolio Image</a></div>-->
    <div id="AddPortfolioImageButtonDiv">
    <?php if($current_images=="") { ?>
	<a class="fp-open-media button" href="#" target="wp-preview" id="post-preview">Add Portfolio Image</a>
    <?php } ?>
    </div>
	<div id="inventory_images"><ul><?php echo($current_images) ?></ul></div>
	<div style="clear:both;"></div>
	<script>
		jQuery(document).ready(function($){
		    // Prepare the variable that holds our custom media manager.
		    var fp_media_frame;
		    
		    // Bind to our click event in order to open up the new media experience.
		    jQuery(document.body).on('click.fpOpenMediaManager', '.fp-open-media', function(e){
		        // Prevent the default action from occuring.
		        e.preventDefault();
		
		        // If the frame already exists, re-open it.
		        if ( fp_media_frame ) {
		            fp_media_frame.open();
		            return;
		        }
		
		        // Create media frame with options
		        //   *For additional options see wp-includes/js/media-views.js
		        fp_media_frame = wp.media.frames.fp_media_frame = wp.media({
		            
		            className: 'media-frame fp-media-frame', // Custom frame class name
		            frame: 'select', // Frame type. Either 'select' or 'post'
		            multiple: true,
		            title: 'Choose portfolio image',
		            // Limit view to library
		            library: {
		                type: 'image'
		            },
		            button: {
		                text:  'Select Image'
		            }
		        });
		
		        // On form submit event handler
		        fp_media_frame.on('select', function(){
		            // Grab our attachment selection and construct a JSON representation of the model.
		            var media_attachments = fp_media_frame.state().get('selection').toJSON();
					
					// Update Portfolio Images with selected images
					for (var i = 0; i < media_attachments.length; i++) {
						addToPortfolioImages(media_attachments[i]);
					}
					
					portfolioImagesSortable(); // Re-enable sortable 
					saveImageOrder(); // Saves image order
		        });
		
		        // Now that everything has been set, let's open up the frame.
		        fp_media_frame.open();
		    });
		    
		    function addToPortfolioImages(media_attachment) {
		    	
		    	// Check to make sure the image doesn't already exist in the image array
		    	if ($('#portfolio-image-' + media_attachment.id).length > 0) {
		    		// Image already exists - do not add again
		    	} else {
		    		
		    		var thumbnail_url;
		    		if (media_attachment.sizes.thumbnail !== undefined) {
		    			// Image thumbnail exists
		    			thumbnail_url = media_attachment.sizes.thumbnail.url;
		    		} else {
		    			// No thumbnail - use full image URL
		    			thumbnail_url = media_attachment.url;
		    		}

                    if(thumbnail_url!="") 
                        $('#AddPortfolioImageButtonDiv').hide();
                    else
                        $('#AddPortfolioImageButtonDiv').show();

		    		jQuery('#inventory_images > ul').append("<li id='portfolio-image-" + media_attachment.id + "' class='CreatePhoto'><div class='PhotoWrapper'><img src='" + thumbnail_url + "' style='max-width:150px;' /><div id='remove-image-" + media_attachment.id + "' class='remove-image'><img src='<?php echo  plugins_url( 'img/x.png' , __FILE__ ); ?>' /></div></div></li>");
		    	}
		    }
		    
		    function portfolioImagesSortable() {
		    	jQuery( "#inventory_images > *" ).sortable(
		    		{
		    			revert: true,
		    			update: function(event, ui) { saveImageOrder(); }
		    		}
		    	);
		    }
    		
		    // Execute on first load
		    portfolioImagesSortable();
			
			var image_ajax_request;
			
			function saveImageOrder() {
				var ImageOrder = jQuery( "#inventory_images > *" ).sortable("toArray");
	
                var url = "<?php echo "edit.php?post_type=portfolio&page=portfolio-ajax&m=update-images&sid=" . session_id() . "&portfolio-id=" . $post->ID . "&order=" ?>" + ImageOrder;
	
				image_ajax_request = jQuery.ajax({
					url : url,
					type : "GET",
					datatype : "json",
					cache : "false"
				});
			}
			
			jQuery(document).on("click", ".remove-image", function() { 
				// Remove list item from DOM
				jQuery(this).parent().parent().remove();
				// Save new image order
				saveImageOrder();
                document.getElementById('AddPortfolioImageButtonDiv').innerHTML = "<a class=\"fp-open-media button\" href=\"#\" target=\"wp-preview\" id=\"post-preview\">Add Portfolio Image</a>";
                jQuery('#AddPortfolioImageButtonDiv').show();
                
                //document.getElementById('AddPortfolioImageButtonDiv').style.display="block";
                
			});
		});
	</script><?php
}

function portfolio_details_setup() {
	global $post;
	$_projecturl = get_post_meta($post->ID,'_projecturl',TRUE);
	$_clientname = get_post_meta($post->ID,'_clientname',TRUE);
	$_testimonial = get_post_meta($post->ID,'_testimonial',TRUE); ?>
	<?php _e('Project URL', 'portfolio'); ?></td>
	<input type="text" name="_projecturl" id="_projecturl" class="txtbox" value="<?php echo $_projecturl; ?>" />
    (eg. http://www.media6technologies.com)<br /><br />
	<?php _e('Client Name', 'portfolio'); ?>
    <input type="text" name="_clientname" id="_clientname" class="txtbox" value="<?php echo $_clientname; ?>" /><br /><br />
    <?php _e('Testimonial', 'portfolio'); ?>
    <textarea name="_testimonial" id="_testimonial" class="txtarea"><?php echo $_testimonial; ?></textarea>
    <input type="hidden" name="portfolio_meta_noncename" value="<?php echo(wp_create_nonce(__FILE__)); ?>" />
<?php
}

function portfolio_meta_save($post_id) {
     
	global $wpdb;
	if (!wp_verify_nonce((isset($_POST['portfolio_meta_noncename']) ? $_POST['portfolio_meta_noncename'] : ""),__FILE__)) return $post_id;
	if (!current_user_can('edit_'.($_POST['post_type'] == 'page' ? 'page' : 'post'), $post_id)) return $post_id;
	$portfolio_id = $post_id;
	
	//save details data
	portfolio_save_meta_data($post_id, '_projecturl',trim($_POST['_projecturl']));
	portfolio_save_meta_data($post_id, '_clientname',trim($_POST['_clientname']));
	portfolio_save_meta_data($post_id, '_testimonial', $_POST['_testimonial']);
	
	//categories
	$cats = $_POST['foxy_categories'];
	
	//set categories as defined
	$AllCategories = $wpdb->get_results( "SELECT category_id FROM " . $wpdb->prefix . "portfolio_to_category" );
	$CategoryArray = array();
	foreach($AllCategories as $ac) {
		$CategoryArray[] = $ac->category_id;
	}
	foreach($CategoryArray as $cat) {
		if(in_array($cat, $cats)) {
			//check to see if it exists already
			$relationshipExists = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "portfolio_to_category" . " WHERE portfolio_id = '" . mysql_escape_string($post_id) . "' AND category_id='" . $cat . "'");
			if(empty($relationshipExists)) {
				$sql = "INSERT INTO " . $wpdb->prefix . "portfolio_to_category" . " (portfolio_id, category_id) values ('" . mysql_escape_string($post_id) . "', '" . mysql_escape_string($cat)  . "')";
				$wpdb->query($sql);
			}
		} else {
			$sql = "DELETE FROM " . $wpdb->prefix . "portfolio_to_category" . " WHERE portfolio_id = '" . mysql_escape_string($post_id) . "' and category_id='" . $cat . "'";
			$wpdb->query($sql);
		}
	}
	
	//set Default as category if there were no categories selected
	if (count($cats) == 0) {
		$sql = "INSERT INTO " . $wpdb->prefix . "portfolio_to_category" . " (portfolio_id, category_id) values ('" . mysql_escape_string($post_id) . "', '1')";
		$wpdb->query($sql);
	}

	//update primary category
	$primaryCategories = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "portfolio_to_category" . " WHERE portfolio_id = '" . mysql_escape_string($post_id) ."'");
	foreach($primaryCategories as $pc) {
		$update_value = ($pc->category_id == $_POST['_primary_category'] ? 1 : 0);
		$sql = "UPDATE " . $wpdb->prefix . "portfolio_to_category" . " SET category_primary = " . $update_value . " WHERE category_id = " . mysql_escape_string($pc->category_id)  . " AND portfolio_id = " . $pc->portfolio_id ."";
		$wpdb->query($sql);	
	}

	return $post_id;
} 

function change_default_title( $title ){
     $screen = get_current_screen();
 
     if  ( $screen->post_type == 'portfolio' ) {
          return 'Enter your project name here';
     }
}
 
add_filter( 'enter_title_here', 'change_default_title' );

?>