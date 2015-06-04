<?php

/**
 * Yahoo Weather API helper
 *
 */

namespace Kiosk_WP;


class Yahoo_Weather_Api_Helper {
  /**
   * Connect to Yahoo weather api and gets the json object for tempe area
   * The function is seperated for unit test mocking purpose
   * It returns either the actual feed in case of normal flow
   * for unit test case it returns the mock up data.
   * Returns a JSON type formatted string.
   * @return string
   */
  public function get_weather_json( $location = 'tempe, az' ){
    $BASE_URL = 'http://query.yahooapis.com/v1/public/yql';
    $yql_query = 'select * from weather.forecast where woeid in (select woeid from geo.places(1) where text="' . $location . '")';
    $yql_query_url = $BASE_URL . '?q=' . urlencode( $yql_query ) . '&format=json';
    // Make call with cURL
    $session = curl_init( $yql_query_url );
    curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );
    $json = curl_exec( $session );
    if ( curl_error( $session ) ) {
      $json = '';
      error_log( basename( __FILE__ ) .'Weather API error: CURL ' . curl_strerror( curl_errno( $session ) ) . "\n" );
    }
    curl_close( $session );
    return $json;
  }
  /**
   * extract_weather_data( $json_weather ) parses weather json object and creates a
   * array with current and forecast weather
   * @param JSON object
   * @return array
   */
  public function extract_weather_data( $json_weather ){
    $weather_details = array(
      'location_title'         => '',
      'forecast'               => array( 3 ),
      'current_weather_image'  => '',
      'current_weather_unit'   => '',
      'current_weather_temp'   => '',
      );
    $forecast_weather_details = array(
      'date'  => '',
      'image' => '',
      'low'   => '',
      'high'  => '',
      );
    $yahoo_weather_icon_url = 'http://l.yimg.com/a/i/us/we/52/%s.gif';
    $location_city = $json_weather['query']['results']['channel']['location']['city'];
    $location_region = $json_weather['query']['results']['channel']['location']['region'];
    $forecast = $json_weather['query']['results']['channel']['item']['forecast'];
    $yahoo_weather_code = $json_weather['query']['results']['channel']['item']['condition']['code'];
    $weather_details['location_title'] = $location_city.' , '.$location_region;
    $weather_details['current_weather_image'] = sprintf(
        $yahoo_weather_icon_url,
        $yahoo_weather_code
    );
    $weather_details['current_weather_unit'] = $json_weather['query']['results']['channel']['units']['temperature'];
    $weather_details['current_weather_temp'] = $json_weather['query']['results']['channel']['item']['condition']['temp'];
    for ( $i = 0; $i < 3; $i++ ){
      $forecast_weather_details[ $i ]['date'] = date_format( date_create( $forecast[ $i ]['date'] ), 'l' );
      $forecast_weather_details[ $i ]['image'] = sprintf(
          $yahoo_weather_icon_url,
          $forecast[ $i ]['code']
      );
      $forecast_weather_details[ $i ]['low'] = $forecast[ $i ]['low'];
      $forecast_weather_details[ $i ]['high'] = $forecast[ $i ]['high'];
    }
    $weather_details['forecast'] = $forecast_weather_details;
    return $weather_details;
  }
}