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
  exit();
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
  public function __construct() {
    $this->plugin_slug = 'kiosk-css-styles';
    $this->version     = '0.1';
    $this->css         = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/wordpress-news-kiosk-plugin.css';

    $this->define_hooks();
  }

  /**
   * Setup WordPress hooks for filters and actions.
   * @override
   */
  public function define_hooks() {
    $this->add_action( 'wp_enqueue_scripts',  $this, 'kiosk_styles', 99 );
  }
  /**
  * Enqueue styles.
  */
  function kiosk_styles() {
    wp_enqueue_style( $this->plugin_slug, $this->css,'', $this->version );
  }
}
