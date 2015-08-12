<?php

/**
 * Kiosk Weather Helper
 *
 */

namespace Kiosk_WP;

class Kiosk_Weather_Handler {
  /**
   * Retrieves the current and forecast weather data
   * and creates a div block for the current and next 3 days forecast
   * @param
   * @return string HTML markup or empty string on failure
   */
  function kiosk_populate_weather_data( $weather_json ) {
    $forecast_block_template  = <<<HTML
      <div class='kiosk-weather__forecast__item'>
        <div class="kiosk-weather__forecast__item__header">%s</div>
        <img class='kiosk-weather_forecast__item_image' src='%s' >
        <div class="kiosk-weather__forecast__item__text"><b>%s° / %s°</b></div>
      </div>
HTML;
    $weather_details = Yahoo_Weather_Api_Helper::extract_weather_data(
        $weather_json
    );
    if ( empty( $weather_details ) ) {
      return '';
    }
    $location_title  = $weather_details['location'];
    $forecast_block  = '';
    $forecast        = $weather_details['forecast'];
    for ( $i = 0; $i < 3; $i++ ) {
      $forecast_block .= sprintf(
          $forecast_block_template,
          $forecast[ $i ]['day'],
          $forecast[ $i ]['image'],
          $forecast[ $i ]['low'],
          $forecast[ $i ]['high']
      );
    }
    $weather_div = <<<HTML
      <div class="kiosk-weather__forecast__title">
        %s
      </div>
      <div class="kiosk-weather__weather__row">
          <div class="kiosk-weather__current">
            <img class='kiosk-weather__current__item__image' src='%s' >
            <div class="kiosk-weather__current__item__text">
              <b>%s°%s</b>
            </div>
          </div>
          <div class="kiosk-weather__forecast">
            <div class="row">
              %s
            </div>
          </div>
      </div>
HTML;
    return sprintf(
        $weather_div,
        $location_title,
        $weather_details['image'],
        $weather_details['temp'],
        $weather_details['unit'],
        $forecast_block
    );
  }
  /**
   * Create a weather widget. Returns array with two keys status and response.
   * response contains HTML mark up to be displayed.
   * @param string $location
   * @return array< int status, String response<HTML markup>>
   * status = 0 if success else non-negative
   */
  public function get_kiosk_weather_html( $location = 'tempe, az' ) {
    $weather_json       = $this->get_weather_json( $location );
    $status             = 0;
    $kiosk_weather_data = '<div class="kiosk-weather__no-data">Weather Data Not Available</div>';
    if ( ! empty( $weather_json ) ) {
      $kiosk_weather_data = $this->kiosk_populate_weather_data( $weather_json );
    }
    if ( empty( $weather_json ) || empty( $kiosk_weather_data ) ) {
      $status = 1;
    }
    return array(
        'status'    => $status,
        'response'  => '<div class="kiosk-weather">'
            . $kiosk_weather_data
            . '</div>',
    );
  }
  /**
   * Invoked the yahoo weather helper class method to get weather info
   * @param string
   * @return json
   */
  public function get_weather_json( $location ) {
    return Yahoo_Weather_Api_Helper::get_weather_json( $location );
  }
}