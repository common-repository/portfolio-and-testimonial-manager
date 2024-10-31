<?php /*
**************************************************************************
Plugin Name: Portfolio and Testimonial manager
Plugin URI: http://www.media6technologies.com/services/wordpress/plugin-portfolio-and-testimonial-manager/
Description: Portfolio and Testimonial manager -  Media6 Technologies
Author: Sankar, Media6 Technologies
Version: 1.0
Author URI: http://www.media6technologies.com/
License: GPLv2 or later
**************************************************************************
*/

/*
Copyright (c) 2014, Media6 Technologies.
 
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
 
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

global $portfolio_url;
$portfolio_url = get_option('portfolio_storeurl');

register_activation_hook( __FILE__ , 'portfolio_Install');
register_uninstall_hook( __FILE__ , 'portfolio_Uninstall');

include_once('custom-post-type.php');
include_once('portfolio-settings.php');
include_once('customer-portfolio.php');

include_once('portfolio-ajax.php');

define('PORTFOLIO_CUSTOM_POST_TYPE', 'portfolio');
define('WP_PORTFOLIO_CURRENT_VERSION', "1.0.0");
define('PORTFOLIO_PATH', dirname(__FILE__));
define('PORTFOLIO_USER_PORTAL','user');

add_action('admin_init', 'portfolio_admin_init');
add_action('admin_menu', 'portfolio_menu');
add_action('init', 'portfolio_register');

function portfolio_admin_init() {
    wp_enqueue_style('portfolio_admin_css', plugins_url( '/css/admin-custom.css', __FILE__ ));
}    

function portfolio_register() {
	$labels = array(
		'name' => _x('Items', 'post type general name'),
		'singular_name' => _x('Portfolio Item', 'post type singular name'),
		'add_new' => _x('Add New', 'portfolio item'),
		'add_new_item' => __('Add New Portfolio Item'),
		'edit_item' => __('Edit Portfolio Item'),
		'new_item' => __('New Portfolio Item'),
		'view_item' => __('View Portfolio Item'),
		'search_items' => __('Search Portfolio'),
		'not_found' =>  __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => '',
        'menu_name' => __( 'Portfolios & Testimonials' )
	);
 
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'menu_icon' => plugins_url('/img/icon_portfolio.png', __FILE__),
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
        'show_admin_column' => true,
		'supports' => array('title','editor')
	  ); 
 
	register_post_type( 'portfolio' , $args );
}
add_action( 'init', 'create_portfolio_tax' );

function create_portfolio_tax() {
	register_taxonomy(
        'portfolio-category',
        'portfolio',
		array(
			'label' => __( 'Categories' ),
            'labels'  => $labels,
			'rewrite' => array( 'slug' => 'portfolio-category' ),
			'hierarchical' => true,
		)
	);
}

add_shortcode('testimonials', 'view_testimonials');
add_shortcode('portfolios', 'view_portfolios');

function testmonial_addcssjs() {
    wp_enqueue_script('jquery-easing_js', '/' . PLUGINDIR .'/portfolio-and-testimonial-manager/js/jquery.easing.min.1.3.js');
    wp_enqueue_script('jquery-jcontent_js', '/' . PLUGINDIR .'/portfolio-and-testimonial-manager/js/jquery.jcontent.0.8.min.js');
    wp_enqueue_style('jcontent_css', '/' . PLUGINDIR .'/portfolio-and-testimonial-manager/css/jcontent.css');
	
	wp_enqueue_style('portfolio-style', '/' . PLUGINDIR .'/portfolio-and-testimonial-manager/css/portfolio-style.css');
    wp_enqueue_script('jquery_shuffle', '/' . PLUGINDIR .'/portfolio-and-testimonial-manager/js/jquery.shuffle.modernizr.js');
	wp_enqueue_script('shuffle', '/' . PLUGINDIR .'/portfolio-and-testimonial-manager/js/shuffle.js','','','true');
}

if(!is_admin()) {
    add_action( 'init', 'testmonial_addcssjs' );
}

function set_portfolio_image_size() {
    global $wpdb;
    $theSettings = $wpdb->get_row('SELECT slider_image_width, slider_image_height, page_image_width, page_image_height, portfolio_image_width, portfolio_image_height FROM '.$wpdb->prefix.'portfolio_settings');
    $slider_image_width = $theSettings->slider_image_width; 
    $slider_image_height = $theSettings->slider_image_height;
    
    $page_image_width = $theSettings->page_image_width;
    $page_image_height = $theSettings->page_image_height;
    
    $portfolio_image_width = $theSettings->portfolio_image_width;
    $portfolio_image_height = $theSettings->portfolio_image_height;
    
    add_image_size( 'testimonial_image', $slider_image_width, $slider_image_height, array( 'left', 'top' )  );
    
    add_image_size( 'portfolio_image', $page_image_width, $page_image_height, array( 'left', 'top' )  );
    
    add_image_size( 'portfolio_big_image', $portfolio_image_width, $portfolio_image_height, array( 'left', 'top' )  );
}
add_action('init', 'set_portfolio_image_size');

function portfolio_menu() {
	global $portfolio_url;
	global $current_user;
	if ( !empty ( $portfolio_url  ) ) {
        add_submenu_page('edit.php?post_type=' . PORTFOLIO_CUSTOM_POST_TYPE, __('Manage Categories', 'portfolio'), __('Manage Categories', 'portfolio'), 'manage_options', 'portfolio-category', 'portfolio_category_page_load');
    }
    add_submenu_page('admin.php?page=portfolio-settings', __('Settings', 'portfolio'), __('Settings', 'portfolio'), 'manage_options', 'portfolio-settings', 'portfolio_settings');
    
    add_submenu_page('edit.php?post_type=' . PORTFOLIO_CUSTOM_POST_TYPE, __('Settings'), __('Settings'), 'manage_options', 'portfolio-settings', 'portfolio_settings');
}



function hide_add_new_custom_type() {
    global $submenu;
    // replace my_type with the name of your post type
    unset($submenu['edit.php?post_type=portfolio'][10]);
}
add_action('admin_menu', 'hide_add_new_custom_type');

function portfolio_save_meta_data($post_id, $fieldname, $input) {
	$current_data = get_post_meta($post_id, $fieldname, TRUE);
 	$new_data = $input;
 	if ($new_data == "") $new_data = NULL;
	if (!is_null($current_data)) {
		if (is_null($new_data)) delete_post_meta($post_id,$fieldname);
		else update_post_meta($post_id,$fieldname,$new_data);
	} elseif (!is_null($new_data)) {
		add_post_meta($post_id,$fieldname,$new_data);
	}
}

function portfolio_ReplaceNewLine($string) {
	return (string)str_replace(array("\r", "\r\n", "\n"), '', $string);
}

function portfolio_TruncateString($str, $length) {
	$str = portfolio_ReplaceNewLine($str);
	if(strlen($str) > $length) {
		return substr($str, 0, $length) . "...";
	}
	return $str;
} 

function portfolio_FixGetVar($variable, $default = 'management') {
    $value = $default;
    if(isset($_GET[$variable])) {
        $value = trim($_GET[$variable]);
        if(get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }
        $value = mysql_real_escape_string($value);
    }
    return $value;
} 

function portfolio_Install() {
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	global $wpdb;
    if ( ! empty( $wpdb->charset ) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if ( ! empty( $wpdb->collate ) )
		$charset_collate .= " COLLATE $wpdb->collate";

    // Table structure for table `portfolio_images`            
	if( $wpdb->get_var( "SHOW TABLES LIKE " . $wpdb->prefix . "portfolio_images" ) != $wpdb->prefix . "portfolio_images" ) {
        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "portfolio_images (
            `image_id` bigint(20) NOT NULL AUTO_INCREMENT,
            `portfolio_id` bigint(20) unsigned NOT NULL,
            `attached_image_id` bigint(20) unsigned NOT NULL,
            `image_order` int(11) NOT NULL,
            PRIMARY KEY (`image_id`)
        ) $charset_collate;";
		dbDelta( $sql );
	}
    
    // Table structure for table `portfolio_settings`            
	if( $wpdb->get_var( "SHOW TABLES LIKE " . $wpdb->prefix . "portfolio_settings" ) != $wpdb->prefix . "portfolio_settings" ) {
        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "portfolio_settings (
            `pf_settings_id` bigint(20) NOT NULL AUTO_INCREMENT,
            `slider_image_width` varchar(255) NOT NULL,
            `slider_image_height` varchar(255) NOT NULL,
            `page_image_width` varchar(255) NOT NULL,
            `page_image_height` varchar(255) NOT NULL,
            `portfolio_image_width` int(4) NOT NULL,
            `portfolio_image_height` int(4) NOT NULL,
            PRIMARY KEY (`pf_settings_id`)
        ) $charset_collate;";
		dbDelta( $sql );
        
        if(($wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "portfolio_settings")) == 0) {
            $sql_ins = "INSERT INTO " . $wpdb->prefix . "portfolio_settings (slider_image_width, slider_image_height, page_image_width, page_image_height, portfolio_image_width, portfolio_image_height) values ('150', '175', '250', '275', 450, 475)";
		$wpdb->query($sql_ins);
        }
	}
    
    // Table structure for table `portfolio_to_category`            
	if( $wpdb->get_var( "SHOW TABLES LIKE " . $wpdb->prefix . "portfolio_to_category" ) != $wpdb->prefix . "portfolio_to_category" ) {
        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "portfolio_to_category (
            `itc_id` int(11) NOT NULL AUTO_INCREMENT,
            `portfolio_id` int(11) NOT NULL,
            `category_id` int(11) NOT NULL,
            `sort_order` int(11) NOT NULL DEFAULT '99',
            `category_primary` int(11) NOT NULL,
            PRIMARY KEY (`itc_id`)
        ) $charset_collate;";
		dbDelta( $sql );
	}
}

function portfolio_Uninstall() {
    global $wpdb;
	//delete tables
	$wpdb->query("DROP TABLE " . $wpdb->prefix  . "portfolio_images");
	$wpdb->query("DROP TABLE " . $wpdb->prefix  . "portfolio_settings");
	$wpdb->query("DROP TABLE " . $wpdb->prefix  . "portfolio_to_category");
    
    $wpdb->query("DELETE FROM " . $wpdb->prefix  . "term_relationships WHERE term_taxonomy_id IN (SELECT term_id FROM " . $wpdb->prefix  . "term_taxonomy WHERE taxonomy='portfolio-category')");
    $wpdb->query("DELETE FROM " . $wpdb->prefix  . "terms WHERE term_id IN (SELECT term_id FROM " . $wpdb->prefix  . "term_taxonomy WHERE taxonomy='portfolio-category')");
    $wpdb->query("DELETE FROM " . $wpdb->prefix  . "term_taxonomy WHERE taxonomy='portfolio-category'");
    
    $wpdb->query("DELETE FROM " . $wpdb->prefix  . "postmeta WHERE post_id IN (SELECT ID FROM " . $wpdb->prefix  . "posts WHERE post_type = 'portfolio')");
    $wpdb->query("DELETE FROM " . $wpdb->prefix  . "posts WHERE post_type = 'portfolio'");
} 

function add_query_vars_filter( $vars ){
  $vars[] = "portfolio_id";
  return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );


function deft_filter_image_sizes( $sizes) {
		
	unset( $sizes['thumbnail']);
	unset( $sizes['medium']);
	unset( $sizes['large']);
	
	return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'deft_filter_image_sizes');


if($_GET['page'] == "portfolio-ajax") {
    add_action('admin_menu', 'portfolio_ajax');
}

?>