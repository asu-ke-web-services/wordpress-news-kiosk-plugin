<?php

/**
 * CSS Styles
 *
 * Enqueue styles that are specific to this plugin.
 * WordPress only file.
 */

namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  trigger_error( 'Error: This file should be accessed directly', E_USER_ERROR );
}

/**
 * CSS Styles Class
 *
 * @extends Base_Registrar
 */
class CSS_Styles extends Base_Registrar {
  /** @type String */
  protected $plugin_slug;
  /** @type String */
  protected $version;

  /**
   * Setup plugin and versioning.
   */
  public function __construct()  {
    $this->plugin_slug = 'kiosk-css-styles';
    $this->version     = '0.1';
    $this->css         = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/style.css';

    $this->define_hooks();
  }

  /**
   * Setup WordPress hooks for filters and actions.
   * @override
   */
  public function define_hooks() {
    $this->add_action( 'wp_enqueue_scripts',  $this,'asu_webstandards_scripts' );
    $this->add_action( 'wp_enqueue_scripts', $this, 'wp_enqueue_scripts' );
  }

  /**
   * Enqueue the CSS
   * Hooks onto `wp_enqueue_scritps`.
   */
  public function wp_enqueue_scripts() {
    wp_enqueue_style( $this->plugin_slug, $this->css, array(), $this->version );
  }
  /**
  * Enqueue scripts and styles.
  */
  function asu_webstandards_scripts() {
    // Wordpress provides jquery, but we enqueue our own mainly so we include it in the footer and control the version.
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', plugin_dir_url( dirname( __FILE__ ) ) . '/assets/js/jquery-1.11.2.min.js', array(), '1.11.2', true );
    wp_register_script( 'bootstrap-js', plugin_dir_url( dirname( __FILE__ ) ) . '/assets/bootstrap-3.1.1-dist/ js/bootstrap.min.js', array( 'jquery' ), '3.1.1', true );
    wp_register_style( 'bootstrap-css', plugin_dir_url( dirname( __FILE__ ) ) . '/assets/bootstrap-3.1.1-dist/css/bootstrap.min.css', array(), '3.1.1', 'all' );
    wp_register_style( 'base-wordpress-theme', plugin_dir_url( dirname( __FILE__ ) ) . '/style.css', array(), false, 'all' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'bootstrap-js' );
    wp_enqueue_style( 'bootstrap-css' );
    wp_enqueue_style( 'base-wordpress-theme' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri() );
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
      wp_enqueue_script( 'comment-reply' );
    }
  }
}
