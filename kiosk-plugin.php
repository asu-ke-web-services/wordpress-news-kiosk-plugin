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
 * load all the dependent files to use kisok plugin
 */
function load_dependencies(){
  // Require all the files for the Kiosk plugin
  set_include_path( get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] );
  require_once 'gios-api-v2.0.php';
  require_once plugin_dir_path( __FILE__ ) . 'includes/base-registrar.php';
  require_once plugin_dir_path( __FILE__ ) . 'helpers/kiosk-tweets-helper.php';
  require_once plugin_dir_path( __FILE__ ) . 'helpers/kiosk-weather-helper.php';
  require_once plugin_dir_path( __FILE__ ) . 'helpers/twitter-helper.php';
  require_once plugin_dir_path( __FILE__ ) . 'helpers/yahoo-weather-helper.php';
  require_once plugin_dir_path( __FILE__ ) . 'helpers/json-decode-helper.php';
  require_once plugin_dir_path( __FILE__ ) . 'helpers/carousel-slider-helper.php';
  require_once plugin_dir_path( __FILE__ ) . 'helpers/feed-helper.php';
  require_once plugin_dir_path( __FILE__ ) . 'helpers/kiosk-helper.php';
  require_once plugin_dir_path( __FILE__ ) . 'helpers/people-slider-helper.php';
  require_once plugin_dir_path( __FILE__ ) . 'plugin/kiosk-plugin.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-posts-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-news-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-slider-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-time-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-logo-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-tweets-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-weather-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'shortcodes/kiosk-people-slider-shortcodes.php';
  require_once plugin_dir_path( __FILE__ ) . 'page-templates/kiosk-page-templates.php';
  require_once plugin_dir_path( __FILE__ ) . 'pages/kiosk-tweets-page.php';
  require_once plugin_dir_path( __FILE__ ) . 'pages/kiosk-weather-page.php';
  require_once plugin_dir_path( __FILE__ ) . 'globals/css-styles.php';
  require_once plugin_dir_path( __FILE__ ) . 'localsettings.php';
}
/**
 * Initialize the required classes for kiosk plugin
 */
function run_loaded_classes(){
  // ==========
  // Helpers
  // ==========
  $feed_helper          = new \Kiosk_WP\Feed_Helper();
  $people_slider_helper = new \Kiosk_WP\People_Slider_Helper();

  // ==========
  // Shortcodes
  // ==========
  $posts_shortcodes = new \Kiosk_WP\Kiosk_Posts_Shortcodes();
  $posts_shortcodes->run();

  $posts_shortcodes = new \Kiosk_WP\Kiosk_News_Shortcodes( $feed_helper );
  $posts_shortcodes->run();

  $posts_shortcodes = new \Kiosk_WP\Kiosk_Slider_Shortcodes( $feed_helper );
  $posts_shortcodes->run();

  $posts_shortcodes = new \Kiosk_WP\Kiosk_Time_Shortcodes();
  $posts_shortcodes->run();

  $posts_shortcodes = new \Kiosk_WP\Kiosk_Logo_Shortcodes();
  $posts_shortcodes->run();

  $posts_shortcodes = new \Kiosk_WP\Kiosk_Tweets_Shortcodes();
  $posts_shortcodes->run();

  $posts_shortcodes = new \Kiosk_WP\Kiosk_Weather_Shortcodes();
  $posts_shortcodes->run();

  $posts_shortcodes = new \Kiosk_WP\Kiosk_People_Slider_Shortcodes( $people_slider_helper );
  $posts_shortcodes->run();

  // =====
  // Pages
  // =====
  $kiosk_pages = new \Kiosk_WP\Kiosk_Tweets_Page();
  $kiosk_pages->run();

  $kiosk_pages = new \Kiosk_WP\Kiosk_Weather_Page();
  $kiosk_pages->run();

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
/**
 * Register your files here!
 */
function setup_kiosk_wp_plugin() {
  // =================
  // Load Dependencies
  // =================
  load_dependencies();

  // ===================
  // Plugin Registration
  // ===================
  $plugin = new \Kiosk_WP\Kiosk_Plugin();
  $plugin->run();

  // Intialize the dependent classes
  run_loaded_classes();
}
setup_kiosk_wp_plugin();
