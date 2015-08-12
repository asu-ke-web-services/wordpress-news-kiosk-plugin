<?php

/**
 * Weather Shortcode functionality.
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

class Kiosk_Weather_Shortcodes extends Base_Registrar {
  public function __construct() {
    $this->define_hooks();
  }

  public function define_hooks() {
    $this->add_shortcode( 'kiosk-weather', $this, 'kiosk_weather' );
  }

    /**
   * [kiosk_weather location='tempe, az']
   *
   * @param $atts array
   * Generates a <div> tag with current weather and weather forecast
   *
   */
  public function kiosk_weather( $atts, $content = null ) {
    $atts = shortcode_atts(
        array(
          'location' => 'tempe, az',
        ),
        $atts
    );
    return (new \Kiosk_WP\Kiosk_Weather_Helper())
        ->get_kiosk_weather_html( $atts['location'] );
  }
}
