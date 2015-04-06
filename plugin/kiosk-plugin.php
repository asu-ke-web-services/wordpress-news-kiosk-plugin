<?php
/**
 * Kiosk Plugin
 *
 * Takes care of activation and deactivation.
 */

namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

class Kiosk_Plugin extends Base_Registrar {

  public function __construct() {
    $this->plugin_slug = 'kiosk-plugin';
    $this->version     = '0.1';

    $this->load_dependencies();
    $this->define_hooks();
  }

  /**
   * Nothing to load
   * @override
   */
  public function load_dependencies() {
    // Do nothing
  }

  /**
   * Add activation and deactivation hooks
   */
  public function define_hooks() {

  }

}