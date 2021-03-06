<?php

/**
 * Weather Page functionality.
 *
 */
namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

class Kiosk_Weather_Page extends Base_Registrar {
  public static $page_name    = 'kiosk';
  public static $param_slug   = 'weather';
  public static $extra_params = array( 'location' );

  public function __construct() {
    $this->define_hooks();
  }
  /**
   * Add filters and action
   *
   * The majority of the work happend in the action 'init'
   */
  public function define_hooks() {
    // Register Actions
    $this->add_action( 'init', $this, 'setup_rewrites' );
  }

  /**
   * Set up url rewrites. Maps pretty urls to page actions.
   */
  public function setup_rewrites() {
    /*
     * Wordpress requires that you tell it that you are using
     * additional parameters.
     *
     * Allow everything to come in through the rsvp_submit url
     * and transform the post so that url parameters do not
     * conflict with Wordpress's reserved parameters.
     */
    add_rewrite_tag( '%' . Kiosk_Weather_Page::$page_name . '%' , '([^&]+)' );
    add_rewrite_tag( '%' . Kiosk_Weather_Page::$param_slug . '%' , '([^&]+)' );
    add_rewrite_tag( '%' . Kiosk_Weather_Page::$extra_params[0]
        . '%' , '([^&]+)'
    );

    /*
     * Add the rewrite rules
     */
    // ======================================================
    /* Rule: /kiosk/weather => wp-content/plugins/pages/views
        /kiosk-weather-presenter.php*/
    // ======================================================
    $from_url = '.*' . Kiosk_Weather_Page::$page_name . '/'
        . Kiosk_Weather_Page::$param_slug . '/'
        . Kiosk_Weather_Page::$extra_params[0] . '=([^/][a-zA-Z,\s]+)?$';
    $to_url   = 'wp-content/plugins/' . plugin_basename( dirname( __FILE__ ) );
    $to_url  .= '/views/kiosk-weather-presenter.php'
        . '?'. Kiosk_Weather_Page::$extra_params[0] . '=$1';
    add_rewrite_rule( $from_url, $to_url, 'top' );

    // Flush them!
    // TODO move this to register_activation_hook when rules are set in stone
    flush_rewrite_rules();
  }
}