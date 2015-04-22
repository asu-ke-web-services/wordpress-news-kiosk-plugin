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
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-news-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-slider-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-time-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-title-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-tweets-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'page-templates/kiosk-page-templates.php';
  require_once plugin_dir_path( __FILE__ ) . 'globals/css-styles.php';

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

  $posts_shortcodes = new \Kiosk_WP\Kiosk_News_Shortcodes();
  $posts_shortcodes->run();

  $posts_shortcodes = new \Kiosk_WP\Kiosk_Slider_Shortcodes();
  $posts_shortcodes->run();

  $posts_shortcodes = new \Kiosk_WP\Kiosk_Time_Shortcodes();
  $posts_shortcodes->run();

  $posts_shortcodes = new \Kiosk_WP\Kiosk_Title_Shortcodes();
  $posts_shortcodes->run();

  $posts_shortcodes = new \Kiosk_WP\Kiosk_Tweets_Shortcodes();
  $posts_shortcodes->run();

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
  $posts_page_templates = new \Kiosk_WP\Kiosk_Page_Templates();
  $posts_page_templates->run();

  // =============
  // Miscellaneous
  // =============
  $css_styles = new \Kiosk_WP\CSS_Styles();
  $css_styles->run();

}
setup_kiosk_wp_plugin();