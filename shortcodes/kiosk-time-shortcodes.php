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
  public function __construct() {
    $this->load_dependencies();
    $this->define_hooks();
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
        <div class="kiosk-date-time__calendar">
          <div  class="kiosk-date-time__calendar-icon">
            <div class="kiosk-date-time__calendar__month">%s</div>
            <div class="kiosk-date-time__calendar__date">%s</div>
          </div>
        </div>
        <div class="kiosk-date-time__time" id="kiosk_display_time">
          %s
        </div>
HTML;
    date_default_timezone_set( 'America/Phoenix' );
    $month          = date( 'M' );
    $dayOfMonth     = date( 'd' );
    $current_time   = date( 'h:i A' );
    $calender_time  = sprintf(
        $calender_template,
        $month,
        $dayOfMonth,
        $current_time
    );
    $kiosk_time_div = '<div class="kiosk-date-time" id="kiosk_time">'
        . $calender_time . '</div>';
    return $kiosk_time_div;
  }
}