<?php
/*
Plugin Name: KIOSK Wordpress Plugin
Plugin URI: http://kiosk.asu.edu
Description: The KIOSK Wordpress Plugin that handles posts, weather report, time, gallery, tweets, asu news
Version: 1.1
Author: The Global Institute of Sustainability
License: Copyright 2015

GitHub Plugin URI: https://github.com/postss-asu/wordpress-news-kiosk-plugin
GitHub Branch:    master
*/

if ( ! function_exists( 'add_filter' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

define( 'KIOSK_WP_VERSION', '1.1' );
// Until the next signification release!
define( 'KIOSK_API_REQUIRED_VERSION', '~2' );

/**
 * Register your files here!
 */
function setup_kiosk_wp_plugin() {
  // =================
  // Load Dependencies
  // =================
  // No Depencies for now

  // Verify kiosk api version
  // Mo version checking for now

  require_once plugin_dir_path( __FILE__ ) . 'includes/base-registrar.php';
  require_once plugin_dir_path( __FILE__ ) . 'plugin/kiosk-plugin.php';
  require_once plugin_dir_path( __FILE__ ) . 'admin/general-admin.php';
  require_once plugin_dir_path( __FILE__ ) . 'admin/posts-admin.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-posts-shortcodes.php';
  //require_once plugin_dir_path( __FILE__ ) . 'pages/posts-pages.php';
  require_once plugin_dir_path( __FILE__ ) . 'page-templates/posts-page-templates.php';
  require_once plugin_dir_path( __FILE__ ) . 'globals/css-styles.php';
  require_once plugin_dir_path( __FILE__ ) . 'globals/ratio-slim-feature-images.php';

  // ===================
  // Plugin Registration
  // ===================
  $plugin = new \Kiosk_WP\Kiosk_Plugin();
  $plugin->run();

  // ==========
  // Shortcodes
  // ==========
  $posts_shortcodes = new \Kiosk_WP\Kiosk_Posts_Shortcodes();
  $posts_shortcodes->run();

  // =====
  // Pages
  // =====
  // $posts_pages = new \Kiosk_WP\Posts_Pages();
  // $posts_pages->run();

  // =====
  // Admin
  // =====
  $general_admin          = new \Kiosk_WP\General_Admin();
  $posts_admin           = new \Kiosk_WP\Posts_Admin( $general_admin );
  $general_admin->run();
  $posts_admin->run();

  // ==============
  // Page Templates
  // ==============
  $posts_page_templates = new \Kiosk_WP\Posts_Page_Templates();
  $posts_page_templates->run();

  // =============
  // Miscellaneous
  // =============
  $css_styles = new \Kiosk_WP\CSS_Styles();
  $css_styles->run();

  $feature = new \Kiosk_WP\Ratio_Slim_Feature_Images();
  $feature->run();
}
setup_kiosk_wp_plugin();