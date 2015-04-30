<?php

/**
 * Time Shortcode functionality.
 *
 * Provides shortcodes for users to use in Wordpress
 *
 */
namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

class Kiosk_Time_Shortcodes extends Base_Registrar {
  protected $plugin_slug;
  protected $version;

  public function __construct()  {
    $this->plugin_slug = 'kiosk-time-shortcodes';
    $this->version     = '0.1';

    $this->load_dependencies();
    $this->define_hooks();
  }

  /**
   * @override
   */
  public function load_dependencies() {
  }

  public function define_hooks() {
    $this->add_shortcode( 'kiosk-time', $this, 'kiosk_time' );
  }

  /**
   * [kiosk_time]
   *
   * @param $atts array
   * Generates a <div> tag with calender and time
   *
   */
  public function kiosk_time( $atts, $content = null ) {

    $calender_template = <<<HTML
    <div class="row">
      <div class="kiosk_display_cal  col-md-6">
        <time  class="kiosk_calendar_icon">
          <strong>%s</strong>
          <span>%s</span>
        </time>
      </div>
      <div class="kiosk_display_time col-md-6">
        <p>%s</p>
      </div>
    </div>
HTML;
    date_default_timezone_set( 'America/Phoenix' );
    $month = date( 'M' );
    $dayOfMonth = date( 'd' );
    $current_time = date( 'h:i A' );
    $calender_time = sprintf(
        $calender_template,
        $month,
        $dayOfMonth,
        $current_time
    );
    $kiosk_time_div = '<div class="kiosk_time">' . $calender_time . '</div>';
    return $kiosk_time_div;
  }
}