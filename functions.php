<?php
//Theme setup.
if ( ! defined( 'ABSPATH' ) ) { exit; }
# Including theme components
require_once get_template_directory().'/framework/includes.php';
if (!function_exists('apress_theme_setup')) {
	function apress_theme_setup() {	
		// Make theme available for translation
		load_theme_textdomain( 'apress', get_template_directory() . '/languages' );
	
		// Default WP generated title support
		add_theme_support( 'title-tag' );
		// Default RSS feed links
		add_theme_support( 'automatic-feed-links' );
		
		/*
		 * Switches default core markup for search form, comment form,
		 * and comments to output valid HTML5.
		 */
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
	
	
		// Post Formats
		add_theme_support( 'post-formats', array( 'audio', 'gallery', 'quote', 'video', 'link' ) );
	
		// This theme uses wp_nav_menu() in one location.
		register_nav_menu( 'primary-nav', __( 'Navigation Menu', 'apress' ) );
		register_nav_menu( 'top-nav', __( 'Top Menu', 'apress' ) );
		
		add_filter( 'wp_nav_menu_args', 'apress_theme_main_menu_args', 5 );
		
		//Feaured Image
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 604, 270, true );
		
		add_theme_support( 'align-wide' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/admin/css/style-editor.min.css' );
		//Thumb Image Size
		
		add_image_size('apcore_blog_large', 1066, 546, true);
		add_image_size('apcore_blog_medium', 900, 622, true);
		add_image_size('apcore_blogstyle_thumb', 700, 737, true);
		
		add_image_size('apcore_modern3_thumb_big', 533, 546, true);
		add_image_size('apcore_modern4_thumb_big', 1066, 546, true);
		add_image_size('apcore_modern4_thumb_small', 533, 273, true);
		
		add_image_size ('apcore_shortcode_portfolio_squared', 768 , 768 , true);
		add_image_size ('apcore_shortcode_portfolio_landscape', 768 , 384 , true);
		add_image_size ('apcore_shortcode_portfolio_portrait', 384 , 768 , true);
		add_image_size ('apcore_shortcode_portfolio_small_squared', 384 , 384 , true);
	
		// This theme uses its own gallery styles.
		add_filter( 'use_default_gallery_style', '__return_false' );
		
		// Enqueue theme scripts and styles
		add_action('wp_enqueue_scripts', 'apress_themes_scripts', 100);
		
		// Enqueue admin scripts and styles
		add_action('admin_enqueue_scripts', 'apress_themes_admin_scripts');
		
		if(class_exists('WooCommerce')) {
			apress_themes_woocommerce_support();
			apress_themes_woocommerce_rating_show_hide();
		}
		
		/*
		Visual Composer theme integration
		*/
		
		if ( class_exists( 'Vc_Manager', false ) ) {
			
			if ( function_exists( 'vc_set_as_theme' ) ) {
				add_action( 'vc_before_init', 'apress_vc_set_as_theme' );
				function apress_vc_set_as_theme() {
					vc_set_as_theme(true);
				}
			}
		}
		remove_action( 'admin_init', 'vc_page_welcome_redirect' );
		
		if ( function_exists('vc_set_default_editor_post_types') ) {
			vc_set_default_editor_post_types( array( 'page', 'post', 'apcore_footer', 'zt_portfolio' ) );
		}
		
		add_action( 'wp_head', 'apcore_fire_dyn_styles' );
		add_action( 'wp_footer', 'apcore_fire_plugin_dyn_styles' );
		
	}
}
add_action( 'after_setup_theme', 'apress_theme_setup' );


if (!function_exists('apress_themes_woocommerce_support')) {
	/**
	 * WooCommerce support
	 *
	 * @return true
	 */
	function apress_themes_woocommerce_support() {
		add_theme_support( 'woocommerce' );
		include_once( get_template_directory() . '/framework/woo-config.php' );
	}
}

//	Excerpt Length
function apress_theme_excerpt_length( $length ) {
global $post, $apress_data;
$excerpt_length_blog = isset($apress_data['excerpt_length_blog']) ? $apress_data['excerpt_length_blog'] : '55';

	if ($post->post_type == 'post'){
		if(isset($excerpt_length_blog)) {
			return $excerpt_length_blog;
		}
	}else if ($post->post_type == 'tribe_events'){
		return 40;
	}else{
		return 40;
	}	
}
add_filter('excerpt_length', 'apress_theme_excerpt_length', 999);

//	Replaces the excerpt "more" text by a link
function apress_theme_excerpt_more($more) {
	global $post, $apress_data;
	
	$post_continue_show_hide = isset($apress_data['post_continue_reading_show_hide']) ? $apress_data['post_continue_reading_show_hide'] : 'show';
	$post_continue_reading_modify = isset($apress_data['post_continue_reading_modify']) ? $apress_data['post_continue_reading_modify'] : __('Continue Reading','apress');	
	
    if ($post->post_type == 'post'){
		if($post_continue_show_hide == 'show'){
			return '<span class="read_more_area"><a class="read-more" href="'. esc_url(get_permalink($post->ID)) . '"> '.$post_continue_reading_modify.' </a></span>';
		}
	}else if ($post->post_type == 'tribe_events'){
		return '';
	}else{
		if($post_continue_show_hide == 'show'){
			return '<span class="read_more_area"><a class="read-more" href="'. esc_url(get_permalink($post->ID)) . '"> '.$post_continue_reading_modify.' </a></span>';
		}
	}
}
add_filter('excerpt_more', 'apress_theme_excerpt_more');

// Register widget areas.
function apress_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Left Sidebar Widget Area', 'apress' ),
		'id'            => 'sidebar',
		'description'   => __( 'Appears on posts and pages in the sidebar.', 'apress' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
	) );
}
add_action( 'widgets_init', 'apress_widgets_init' );


if ( ! function_exists( 'apress_theme_the_attached_image' ) ) :
//Print the attached image with a link to the next attached image.
function apress_theme_the_attached_image() {	
	$attachment_size     = apply_filters( 'apress_attachment_size', array( 724, 724 ) );
	$next_attachment_url = wp_get_attachment_url();
	$post                = get_post();
	
	$attachment_ids = get_posts( array(
		'post_parent'    => $post->post_parent,
		'fields'         => 'ids',
		'numberposts'    => -1,
		'post_status'    => 'inherit',
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'order'          => 'ASC',
		'orderby'        => 'menu_order ID'
	) );

	// If there is more than 1 attachment in a gallery...
	if ( count( $attachment_ids ) > 1 ) {
		foreach ( $attachment_ids as $attachment_id ) {
			if ( $attachment_id == $post->ID ) {
				$next_id = current( $attachment_ids );
				break;
			}
		}

		// get the URL of the next image attachment...
		if ( $next_id )
			$next_attachment_url = get_attachment_link( $next_id );

		// or get the URL of the first image attachment.
		else
			$next_attachment_url = get_attachment_link( array_shift( $attachment_ids ) );
	}

	printf( '<a href="%1$s" title="%2$s" rel="attachment">%3$s</a>',
		esc_url( $next_attachment_url ),
		the_title_attribute( array( 'echo' => false ) ),
		wp_get_attachment_image( $post->ID, $attachment_size )
	);
}
endif;



//Extend the default WordPress body classes.
function apress_theme_body_class( $body_classes ) {	
	global $apress_data;
	
	$c_pageID = is_search() ? '' : apress_theme_current_page_id();	
	
	$header_position = isset($apress_data["header_position"]) ? $apress_data["header_position"] : 'Top';
	$page_title_100_width = isset($apress_data["page_title_100_width"]) ? $apress_data["page_title_100_width"] : 'off';
	
	$header_100_width = isset($apress_data["header_100_width"]) ? $apress_data["header_100_width"] : 'off';
	$header_100per_width = get_post_meta( $c_pageID, 'zt_header_100_width', true ); 
	
	$footer_100_width = isset($apress_data["footer_100_width"]) ? $apress_data["footer_100_width"] : 'off';
	$footer_100per_width = get_post_meta( $c_pageID, 'zt_footer_100per_width', true ); 
		
	$footer_100_width_upper = isset($apress_data["footer_100_width_upper"]) ? $apress_data["footer_100_width_upper"] : 'off';
	$footer_100per_width_upper = get_post_meta( $c_pageID, 'zt_footer_100per_width_upper', true ); 
	
	$footer_100_width_lower = isset($apress_data["footer_100_width_lower"]) ? $apress_data["footer_100_width_lower"] : 'off';
	$footer_100per_width_lower = get_post_meta( $c_pageID, 'zt_footer_100per_width_lower', true ); 
	
	$layout = isset($apress_data["layout"]) ? $apress_data["layout"] : 'wide';
	
	$option_titlebar_bg_position = isset($apress_data['titlebar_bg_position']) ? $apress_data['titlebar_bg_position'] : 'below';
	$sidebar_widgets_style = isset($apress_data['sidebar_widgets_style']) ? $apress_data['sidebar_widgets_style'] : 'none';
	$single_post_layout_style = isset($apress_data['single_post_layout_style']) ? $apress_data['single_post_layout_style'] : 'layout_style1';
	
	$post_width_100 = get_post_meta( $c_pageID, 'zt_post_width_100', true ); 
	$portfolio_width_100 = get_post_meta( $c_pageID, 'zt_portfolio_width_100', true ); 
	
	$bg_layout = get_post_meta( $c_pageID, 'zt_bg_layout', true ); 
	$titlebar_bg_position  = get_post_meta($c_pageID, 'zt_titlebar_bg_position', true ); 
	$page_single_post_layout_style  = get_post_meta($c_pageID, 'zt_single_post_layout_style', true );	
	
	
	$page_slider_pos  = get_post_meta($c_pageID, 'zt_page_slider_pos', true ); 
	$page_slider_type = get_post_meta($c_pageID, 'zt_page_slider_type', true ); 
	
	$left_right_slider_screen = isset($apress_data['left_right_slider_screen']) ? $apress_data['left_right_slider_screen'] : 'full_screen_slider';
	
	if ( ! is_multi_author() )
		$body_classes[] = 'single-author';
		
	if ( ! get_option( 'show_avatars' ) )
		$body_classes[] = 'no-avatars';
		
	if($header_position == 'Top'){
		$body_classes[] = '';
	}elseif($header_position == 'Left'){
		$body_classes[] = 'zolo_left_vertical_header';
	}elseif($header_position == 'Right'){
		$body_classes[] = 'zolo_right_vertical_header';	
	}else{
		$body_classes[] = '';
		}
	//Title Bar width 100% Class
	if($page_title_100_width == 'on'){
		$body_classes[] = 'titlebar_100width';
	}
	
	//Header width 100% Class	
	if($header_100per_width == 'yes'){
		$body_classes[] = 'header_100width';
	 }elseif($header_100per_width == 'no'){
		 $body_classes[] = '';
	 }else{
		if($header_100_width == 'on'){
			$body_classes[] = 'header_100width';
		}
	}
	
	//Footer width 100% Class	
	if($footer_100per_width == 'yes'){
		$body_classes[] = 'footer_100width';
	 }elseif($footer_100per_width == 'no'){
		 $body_classes[] = '';
	 }else{
		if($footer_100_width == 'on'){
			$body_classes[] = 'footer_100width';
		}
	 }
	 
	//Upper Footer width 100% Class	
	if($footer_100per_width_upper == 'yes'){
		$body_classes[] = 'footer_100per_upper';
	 }elseif($footer_100per_width_upper == 'no'){
		 $body_classes[] = '';
	 }else{
		if($footer_100_width_upper == 'on'){
			$body_classes[] = 'footer_100per_upper';
		}
	 }
	
	//Lower Footer width 100% Class	
	if($footer_100per_width_lower == 'yes'){
		$body_classes[] = 'footer_100per_lower';
	 }elseif($footer_100per_width_lower == 'no'){
		 $body_classes[] = '';
	 }else{
		if($footer_100_width_lower == 'on'){
			$body_classes[] = 'footer_100per_lower';
		}
	 }
	 
	//Single Post width 100% Class	
	if($post_width_100 == 'yes'){
		$body_classes[] = 'post_100width';
	 }
	 
	//Single Portfolio width 100% Class	
	if($portfolio_width_100 == 'yes'){
		$body_classes[] = 'portfolio_100width';
	}

	//Site Layout (Div close in Footer File)	
	if($bg_layout == 'wide'){        
		$body_classes[] = 'wide_layout';
	}elseif($bg_layout == 'boxed'){
		$body_classes[] = 'boxed_layout';
	}elseif($bg_layout == 'theater'){
		$body_classes[] = 'theater_layout';
	}else{
		if($layout == 'boxed'){
			$body_classes[] = 'boxed_layout';
		}elseif($layout == 'wide'){
			$body_classes[] = 'wide_layout';
		}elseif($layout == 'theater'){
			$body_classes[] = 'theater_layout';
		}	
	}
	//Slider Position Class			
	if($header_position=='Top'){ 
	
	if($page_slider_type == 'no' || $page_slider_type == ''){
		$body_classes[] = '';
		
	}elseif($page_slider_type == 'rev' || $page_slider_type == 'layer' || $page_slider_type == 'master'){
		if($page_slider_pos == 'below' || $page_slider_pos == 'default' || $page_slider_pos == ''){
			
			$body_classes[] = 'slider_position_below';
			$zt_slider_pos = 'below';
			
			
		}elseif($page_slider_pos == 'above'){
			
			$body_classes[] = 'slider_position_above';
			$zt_slider_pos = 'above';
			
			
		}elseif($page_slider_pos == 'from_top'){
			
			$body_classes[] = 'slider_position_from_top';
			$zt_slider_pos = 'from_top';
		}
		}	
	}
	
	if(isset($zt_slider_pos)){
			$zt_slider_pos = $zt_slider_pos;
		}else{
				$zt_slider_pos = '';
			}
	
	//Title Bar Background Position	
	if($titlebar_bg_position == 'below'){
		$body_classes[] = 'titlebar_position_below';
	}elseif($titlebar_bg_position == 'from_top'){
		$body_classes[] = 'titlebar_position_from_top';	
	}else{
		if($option_titlebar_bg_position == "below"){
			$body_classes[] = 'titlebar_position_below';
		}elseif($option_titlebar_bg_position == "from_top"){
			$body_classes[] = 'titlebar_position_from_top';	
		}
	}
	
	//Vertical Full_screen_slider Class
	if(is_page() || is_singular( array( 'post', 'zt_portfolio' ) )){
		if($header_position == 'Left' || $header_position == 'Right'){ 
			if($left_right_slider_screen == "full_screen_slider"  && ($page_slider_type == 'rev' || $page_slider_type == 'layer' || $page_slider_type == 'master')){
				$body_classes[] = 'ver_full_screen_slider';
			}
		}
	}
	//Sidebar widgets design class
	if($sidebar_widgets_style == "none"){
		$body_classes[] = 'sidebar_widget_style_none';
	}else{
		$body_classes[] = 'sidebar_widget_style_box';
	}
	
	//Single Post Style class
	if(is_single() && 'post' == get_post_type() ){	
	
		if($page_single_post_layout_style == 'default' || $page_single_post_layout_style == ''){
			$body_classes[] = 'single_post_'.esc_attr($single_post_layout_style);
		}else{
			$body_classes[] = 'single_post_'.esc_attr($page_single_post_layout_style);
		}
	}
	//full Page scroll Class
	$page_full_screen_rows  = get_post_meta($c_pageID, 'zt_full_screen_rows', true ); 
	
	if($page_full_screen_rows == 'on'){
		$body_classes[] = 'fullpage_scroll_class';
		$page_fullpage_scroll_dot_navigation  = get_post_meta($c_pageID, 'zt_fullpage_scroll_dot_navigation', true ); 
		$body_classes[] = 'fullpage_scroll_nav_'.$page_fullpage_scroll_dot_navigation;
	}
	
	return $body_classes;
}
add_filter( 'body_class', 'apress_theme_body_class' );

//Adjust content_width value for video post formats and attachment templates.
function apress_theme_content_width() {
	global $content_width;

	if ( is_attachment() )
		$content_width = 724;
	elseif ( has_post_format( 'audio' ) )
		$content_width = 484;
}
add_action( 'template_redirect', 'apress_theme_content_width' );

//Add postMessage support for site title and description for the Customizer.
function apress_theme_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
}
add_action( 'customize_register', 'apress_theme_customize_register' );

//Enqueue Javascript postMessage handlers for the Customizer.
function apress_theme_customize_preview_js() {
	wp_enqueue_script( 'apress-customizer', get_template_directory_uri() . '/js/theme-customizer.js', array( 'customize-preview' ), '20130226', true );
}
add_action( 'customize_preview_init', 'apress_theme_customize_preview_js' );


/** remove redux menu under the tools **/
add_action( 'admin_menu', 'apress_theme_ReduxMenuRemove',12 );
function apress_theme_ReduxMenuRemove() {
    remove_submenu_page('tools.php','redux-about');
}

function apress_theme_RemoveDemoModeLink() { 
    if ( class_exists('ReduxFrameworkPlugin') ) {
        remove_filter( 'plugin_row_meta', array( ReduxFrameworkPlugin::get_instance(), 'plugin_metalinks'), null, 2 );
    }
    if ( class_exists('ReduxFrameworkPlugin') ) {
        remove_action('admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );    
    }
}
add_action('init', 'apress_theme_RemoveDemoModeLink');

// apress dynamic css
if (!function_exists('apress_theme_dynamic_css_output')) {
	function apress_theme_dynamic_css_output(){
		include(get_template_directory() .'/assets/css/dynamic_css.php');
	}
}

// WMPL Check
if( defined('ICL_SITEPRESS_VERSION') && defined('ICL_LANGUAGE_CODE') ) { 
	//Disabling WPML’s JS files
	define('ICL_DONT_LOAD_LANGUAGES_JS', true);
}

// Remove shortcode from search
function apress_theme_remove_shortcode_from_search($content) {
  if ( is_search() ) {
    $content = strip_shortcodes( $content );
  }
  return $content;
}
add_filter('the_content', 'apress_theme_remove_shortcode_from_search');

// Apress Script
function apress_theme_redux_script_optional(){
	global $apress_data;
	echo isset($apress_data['google_analytics']) ? stripslashes( wp_kses( $apress_data['google_analytics'], array( 'script' => array( 'async' => array(), 'src' => array() ) ) ) ) : ''; 
		
}
add_action('wp_head', 'apress_theme_redux_script_optional','999');

// Supported upload type
add_filter('upload_mimes', 'apress_add_custom_upload_mimes');
function apress_add_custom_upload_mimes($existing_mimes) {
	$existing_mimes['otf'] = 'application/x-font-otf';
	$existing_mimes['woff'] = 'application/x-font-woff';
	$existing_mimes['woff2'] = 'application/x-font-woff2';
	$existing_mimes['ttf'] = 'application/x-font-ttf';
	$existing_mimes['svg'] = 'image/svg+xml';
	$existing_mimes['eot'] = 'application/vnd.ms-fontobject';
	return $existing_mimes;
}

// Show / hide woocommerce ratings
function apress_themes_woocommerce_rating_show_hide(){
	global $apress_data;
	
	$woo_product_rating = isset($apress_data["woo_product_rating"]) ? $apress_data["woo_product_rating"] : 'on';

	if($woo_product_rating == 'on' ){
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
		add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 10 );
	}else{
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	}
}

// Modify individual page / post menu
function apress_theme_main_menu_args( $args ) {
	global $post;
	$cur_id = is_search() ? '' : apress_theme_current_page_id();
	if ( get_post_meta( $cur_id, 'zt_displayed_menu', true ) && 'default' !== get_post_meta( $cur_id, 'zt_displayed_menu', true ) && ( 'primary-nav' === $args['theme_location']) ) {
		$menu = get_post_meta( $cur_id, 'zt_displayed_menu', true );
		$args['menu'] = $menu;
	}
	return $args;
}
// Custom CSS fov VC
function apress_theme_get_vc_custom_css( $id ) {
	$out = '';

	if ( ! $id ) {
		return;
	}

	$post_custom_css = get_post_meta( $id, '_wpb_post_custom_css', true );
	if ( ! empty( $post_custom_css ) ) {
		$post_custom_css = strip_tags( $post_custom_css );
		$out .= '<style type="text/css" data-type="vc_custom-css">';
		$out .= $post_custom_css;
		$out .= '</style>';
	}

	$shortcodes_custom_css = get_post_meta( $id, '_wpb_shortcodes_custom_css', true );
	if ( ! empty( $shortcodes_custom_css ) ) {
		$shortcodes_custom_css = strip_tags( $shortcodes_custom_css );
		$out .= '<style type="text/css" data-type="vc_shortcodes-custom-css">';
		$out .= $shortcodes_custom_css;
		$out .= '</style>';
	}

	return $out;
}
function apress_theme_get_custom_footer_id() {
	$footer_builder_page_id = '';

	include get_template_directory().'/framework/variables/variables-footer.php';

	return $footer_builder_page_id;
}

/**
 * [apress_theme_print_custom_footer_css description]
 * @method apress_theme_print_custom_footer_css
 * @return [type]	[description]
 */
add_action( 'wp_head', 'apress_theme_print_custom_footer_css', 1001 );
function apress_theme_print_custom_footer_css() {

	echo apress_theme_get_vc_custom_css( apress_theme_get_custom_footer_id() );
}

/**
 * Inline style for elements options
 *
 */

$GLOBALS['apcore_save_plugin_dyn_styles'] = '' ;

if ( ! get_option( 'apcore_save_plugin_dyn_styles' ) ) {
  
	add_option( 'apcore_save_plugin_dyn_styles', '', '', 'yes' );

}

if ( ! function_exists('apcore_save_plugin_dyn_styles') ) :

	function apcore_save_plugin_dyn_styles( $style = null ) {
		$GLOBALS['apcore_save_plugin_dyn_styles'] .= $style;
		update_option( 'apcore_save_plugin_dyn_styles', $GLOBALS['apcore_save_plugin_dyn_styles'], 'yes' );
  
	}

endif;

function apcore_fire_dyn_styles() {
	$out = '';
	$out .= get_option( 'apcore_dynamic_css' );

	$style = str_replace(
		array( "\r\n", "\r", "\n", "\t", '    ' ),
		'',
		preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $out )
	);

	echo '<style id="apcore-dyn-styles">' . $style . '</style>';

}

function apcore_fire_plugin_dyn_styles() {
	$out = '';
	$out .= get_option( 'apcore_save_plugin_dyn_styles' );

	$style = str_replace(
		array( "\r\n", "\r", "\n", "\t", '    ' ),
		'',
		preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $out )
	);

	echo '<style id="apcore-plugin-dyn-styles">' . $style . '</style>';

}