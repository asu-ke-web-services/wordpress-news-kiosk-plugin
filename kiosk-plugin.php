<?php
/*
Plugin Name: Kiosk Wordpress Plugin
Plugin URI: http://kiosk.asu.edu
Description: The Kiosk Wordpress Plugin that handles posts, weather report,
time, gallery, tweets, asu news
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

if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  define( 'KIOSK_WP_VERSION', '1.1' );
}

/**
 * Load all the dependent files to use kiosk plugin
 */
if ( ! function_exists( 'load_kiosk_dependencies' ) ) {
  function load_kiosk_dependencies() {
    // Require all the files for the Kiosk plugin
    /*
     * For unit test this file will not be available during the travis build time
     */
    if ( file_exists( stream_resolve_include_path( 'gios-api-v2.0.php' ) )  ) {
      require_once 'gios-api-v2.0.php';
    }
    require_once_directory( plugin_dir_path( __FILE__ ) . 'includes' );
    require_once_directory( plugin_dir_path( __FILE__ ) . 'handlers' );
    require_once_directory( plugin_dir_path( __FILE__ ) . 'helpers' );
    require_once_directory( plugin_dir_path( __FILE__ ) . 'plugin' );
    require_once_directory( plugin_dir_path( __FILE__ ) . 'admin' );
    require_once_directory( plugin_dir_path( __FILE__ ) . 'shortcodes' );
    require_once_directory( plugin_dir_path( __FILE__ ) . 'pages' );
    require_once_directory( plugin_dir_path( __FILE__ ) . 'page-templates' );
    require_once_directory( plugin_dir_path( __FILE__ ) . 'globals' );
    require_once plugin_dir_path( __FILE__ ) . 'localsettings.php';
  }
}
if ( ! function_exists( 'require_once_directory' ) ) {
  function require_once_directory( $directory ) {
    $files = glob( $directory . '/*.php' );
    foreach ( $files as $file ) {
      require_once( $file );
    }
  }
}
/**
 * Initialize the required classes for kiosk plugin
 */
if ( ! function_exists( 'run_kiosk_loaded_classes' ) ) {
  function run_kiosk_loaded_classes() {
    $version = KIOSK_WP_VERSION;
    // ==========
    // Helpers
    // ==========
    $feed_helper          = new \Kiosk_WP\Feed_Helper();
    // ==========
    // Handler
    // ==========
    $people_slider_handler = new \Kiosk_WP\People_Slider_Handler();
    // =====
    // Admin
    // =====
    $general_admin        = new \Kiosk_WP\General_Admin( $version );
    $posts_admin          = new \Kiosk_WP\Posts_Admin( $general_admin, $version );
    $general_admin->run();
    $posts_admin->run();

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

    $posts_shortcodes = new \Kiosk_WP\Kiosk_People_Slider_Shortcodes(
        $people_slider_handler
    );
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
}
/**
 * Register your files here!
 */
if ( ! function_exists( 'setup_kiosk_wp_plugin' ) ) {
  function setup_kiosk_wp_plugin() {
    // =================
    // Load Dependencies
    // =================
    load_kiosk_dependencies();
    // ===================
    // Plugin Registration
    // ===================
    $plugin = new \Kiosk_WP\Kiosk_Plugin();
    $plugin->run();

    // Intialize the dependent classes
    run_kiosk_loaded_classes();
  }
}
  setup_kiosk_wp_plugin();
