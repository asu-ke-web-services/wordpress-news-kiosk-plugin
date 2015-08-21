<?php

/**
 * Kiosk Javascripts
 *
 * Enqueue Scripts that are specific to this plugin.
 * WordPress only file.
 */

namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

/**
 * Javascripts
 * @extends Base_Registrar
 */
class Javascripts extends Base_Registrar {
  /**
   * Setup plugin and versioning.
   */
  public function __construct( $version )  {
    parent::__construct( 'kiosk-javascripts', $version );

    $this->javascripts = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/build/wordpress-news-kiosk-plugin.min.js';

    $this->define_hooks();
  }

  /**
   * Setup WordPress hooks for filters and actions.
   * @override
   */
  public function define_hooks() {
    $this->add_action( 'wp_enqueue_scripts', $this, 'wp_enqueue_scripts' );
  }

  /**
   * Enqueue the javascripts
   * Hooks onto `wp_enqueue_scritps`.
   */
  public function wp_enqueue_scripts() {

    // Wordpress provides jquery, but we enqueue our own mainly so we include it in the footer and control the version.
    $plugin_dir_url = plugin_dir_url( dirname( __FILE__ ) );
    wp_enqueue_script( 'jquery', $plugin_dir_url . '/assets/js/jquery-1.11.2.min.js', array(), '1.11.2', true );
    wp_enqueue_script( 'bootstrap-js', $plugin_dir_url . '/assets/bootstrap-3.1.1-dist/js/bootstrap.min.js', array( 'jquery' ), '3.1.1', true );
    wp_enqueue_script( $this->plugin_slug, $this->javascripts, array( 'jquery' ), $this->version, true );

  }
}
