<?php

/**
 * Kiosk Weather Helper
 *
 */

namespace Kiosk_WP;

/**
 * Collection of helper functions for determining base paths
 */
class Kiosk_Weather_Helper {
  public $request_not_from_wp = false;
  public function __construct() {
    if ( ! function_exists( 'plugin_dir_path' ) ) {
      $this->request_not_from_wp = true ;
    }
  }
   /**
  * kiosk_parse_weather( $json_weather ) takes a json formatted string and retrieves the current and forecast data
  * and creates a div block for the current and next 3 days forecast
  * @return string
  */
  function kiosk_parse_weather( $json_weather ) {
    $current_weather_div_template     = <<<HTML
      <img class='kiosk-weather__current__item-image center-block' src='%s' >
      <p><b>%s<sup>o</sup>%s</b></p>
HTML;

    $forecast_weather_block_template  = <<<HTML
      <div class='kiosk-weather__forecast__item col-xs-4'>
        <h5>%s</h5>
        <img class='kiosk-weather_forecast__item_image center-block' src='%s' >
        <p><b>%s<sup>o</sup> / %s<sup>o</sup></b></p>
      </div>
HTML;
    $yahoo_weather_helper             = new \Kiosk_WP\Yahoo_Weather_Api_Helper();
    $weather_details                  = $yahoo_weather_helper->extract_weather_data( $json_weather );
    $location_title                   = $weather_details['location_title'];
    $current_weather_div              = sprintf(
        $current_weather_div_template,
        $weather_details['current_weather_image'],
        $weather_details['current_weather_temp'],
        $weather_details['current_weather_unit']
    );

    $forecast_weather_block           = '';
    $forecast                         = $weather_details['forecast'];
    for ( $i = 0; $i < 3; $i++ ){
      $forecast_weather_block        .= sprintf(
          $forecast_weather_block_template,
          $forecast[ $i ]['date'],
          $forecast[ $i ]['image'],
          $forecast[ $i ]['low'],
          $forecast[ $i ]['high']
      );
    }
    $weather_div                      = <<<HTML
      <div class="row center-block kiosk-weather_vertical-center">
          <div class="kiosk-weather__current col-xs-3">
          %s
          </div>
          <div class="kiosk-weather__forecast col-xs-9">
            <div class ="row">
              <div class="kiosk-weather__forecast__location span12">
                <b>%s</b>
              </div>
            </div>
            <div class="row">
              %s
            </div>            
          </div>
      </div>
HTML;
    return sprintf( $weather_div, $current_weather_div, $location_title, $forecast_weather_block );
  }

  public function kiosk_weather( $location ) {

    $json = $this->get_weather_json( $location );
    if ( empty( $json ) ) {
      if ( $this->request_not_from_wp ) {
        $kiosk_weather_div = '';
      } else {
        $kiosk_weather_div = '<div class="kiosk-weather">Weather API Errored</div>';
      }
    } else {
      // Convert JSON to PHP array
      $json         = Json_Decode_Helper::remove_unwanted_chars( $json );
      $json_weather = json_decode( $json, true );
      if ( $json_weather != null && json_last_error() === JSON_ERROR_NONE ) {
        $kiosk_weather_div = '<div class="kiosk-weather">' . $this->kiosk_parse_weather( $json_weather ) . '</div>';
      } else {
        $kiosk_weather_div = '';
        error_log( basename( __FILE__ ) .' Weather API error: JSON ' . json_last_error_msg() . "\n" );
      }
    }
    return $kiosk_weather_div;
  }
  /**
   * get_weather_json( $location ) is being used as part of unit test case to mock up test data
   * so it is written separate
   * @param string
   * @return json
   */
  public function get_weather_json( $location ) {
    $yahoo_weather_helper = new \Kiosk_WP\Yahoo_Weather_Api_Helper();
    $json                 = $yahoo_weather_helper->get_weather_json( $location );
    return $json;
  }
}