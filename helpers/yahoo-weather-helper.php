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
  public static function get_weather_json( $location = 'tempe, az' ) {
    $BASE_URL       = 'http://query.yahooapis.com/v1/public/yql';
    $yql_query      = 'select * from weather.forecast where woeid in (select woeid from geo.places(1) where text="' . $location . '")';
    $yql_query_url  = $BASE_URL . '?q=' . urlencode( $yql_query )
        . '&format=json';
    // Make call with cURL
    $session        = curl_init( $yql_query_url );
    curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );
    $json           = curl_exec( $session );
    if ( curl_error( $session ) ) {
      $json         = '';
      error_log( basename( __FILE__ )
          . ' Weather API error: CURL '
          . curl_strerror( curl_errno( $session ) )
          . "\n"
      );
    }
    curl_close( $session );
    return $json;
  }
  /**
   * Parses weather json object and creates a
   * array with current and forecast weather
   * @param JSON object
   * @return array or empty string bad data
   */
  public static function extract_weather_data( $json_weather ) {

    $json_weather = Kiosk_Helper::convert_json_to_array( $json_weather );
    if ( ! empty( $json_weather ) && ! is_array( $json_weather ) ) {
      error_log( basename( __FILE__ )
          . " Weather API error: JSON $json_weather\n"
      );
      return '';
    }

    if ( empty( $json_weather )
        || ! self::has_weather_location_details( $json_weather ) ) {
      return '';
    }
    $weather_data = array(
      'location' => '',
      'forecast' => array(),
      'image'    => '',
      'unit'     => '',
      'temp'     => '',
    );
    $forecast_data = array(
      'date'  => '',
      'image' => '',
      'low'   => '',
      'high'  => '',
    );

    $location_city            = self::get_city_name_from_yahoo_weather( $json_weather );
    $location_region          = self::get_region_name_from_yahoo_weather( $json_weather );
    $forecast                 = self::get_forecast_column_yahoo_weather( $json_weather );
    $yahoo_weather_code       = self::get_condition_code_column_yahoo_weather( $json_weather );
    $weather_data['location'] = "$location_city, $location_region";
    $weather_data['image']    = self::get_yahoo_weather_code_image_url( $yahoo_weather_code );
    $weather_data['unit']     = self::get_temperature_column_yahoo_weather( $json_weather );
    $weather_data['temp']     = self::get_condition_temp_column_yahoo_weather( $json_weather );
    for ( $i = 0; $i < count( $forecast ); $i++ ) {
      $forecast_data[ $i ]['day']   = Kiosk_Helper::get_value_by_key( $forecast[ $i ], 'day' );
      $forecast_data[ $i ]['low']   = Kiosk_Helper::get_value_by_key( $forecast[ $i ], 'low' );
      $forecast_data[ $i ]['high']  = Kiosk_Helper::get_value_by_key( $forecast[ $i ], 'high' );
      $forecast_data[ $i ]['image'] = self::get_yahoo_weather_code_image_url(
          Kiosk_Helper::get_value_by_key( $forecast[ $i ], 'code' )
      );
    }
    $weather_data['forecast'] = $forecast_data;
    return $weather_data;
  }

  /**
   * Checks whether any results found for search location or not
   * @param array
   * @return boolean
   */
  public static function has_weather_location_details( $yahoo_weather_array ) {
    $query = Kiosk_Helper::get_value_by_key( $yahoo_weather_array, 'query' );
    if ( Kiosk_Helper::get_value_by_key( $query, 'count' ) > 0 ) {
      return true;
    }
    return false;
  }

  /**
   * Check for existence of results column in yahoo weather array. If
   * found returns its value else null
   */
  public static function get_results_column_yahoo_weather( $yahoo_weather_array ) {
    $query = Kiosk_Helper::get_value_by_key( $yahoo_weather_array, 'query' );
    return Kiosk_Helper::get_value_by_key( $query, 'results' );
  }
  /**
   * Check for existence of channel column in yahoo weather array. If
   * found returns its value else null
   */
  public static function get_channel_column_yahoo_weather( $yahoo_weather_array ) {
    $results = self::get_results_column_yahoo_weather( $yahoo_weather_array );
    return Kiosk_Helper::get_value_by_key( $results, 'channel' );
  }
  /**
   * Check for existence of location column in yahoo weather array. If
   * found returns its value else null
   */
  public static function get_location_column_yahoo_weather( $yahoo_weather_array ) {
    $channel = self::get_channel_column_yahoo_weather( $yahoo_weather_array );
    return Kiosk_Helper::get_value_by_key( $channel, 'location' );
  }
  /**
   * Check for existence of city in yahoo weather array. If
   * found returns its value else null
   */
  public static function get_city_name_from_yahoo_weather( $yahoo_weather_array ) {
    $location = self::get_location_column_yahoo_weather( $yahoo_weather_array );
    return Kiosk_Helper::get_value_by_key( $location, 'city' );
  }
  /**
   * Check for existence of region in yahoo weather array. If
   * found returns its value else null
   */
  public static function get_region_name_from_yahoo_weather( $yahoo_weather_array ) {
    $location = self::get_location_column_yahoo_weather( $yahoo_weather_array );
    return Kiosk_Helper::get_value_by_key( $location, 'region' );
  }
  /**
   * Check for existence of units column in yahoo weather array. If
   * found returns its value else null
   */
  public static function get_units_column_yahoo_weather( $yahoo_weather_array ) {
    $channel = self::get_channel_column_yahoo_weather( $yahoo_weather_array );
    return Kiosk_Helper::get_value_by_key( $channel, 'units' );
  }
  /**
   * Check for existence of temperature column under units column in yahoo weather array. If
   * found returns its value else null
   */
  public static function get_temperature_column_yahoo_weather( $yahoo_weather_array ) {
    $units = self::get_units_column_yahoo_weather( $yahoo_weather_array );
    return Kiosk_Helper::get_value_by_key( $units, 'temperature' );
  }
  /**
   * Check for existence of item column in yahoo weather array. If
   * found returns its value else null
   */
  public static function get_item_column_yahoo_weather( $yahoo_weather_array ) {
    $channel = self::get_channel_column_yahoo_weather( $yahoo_weather_array );
    return Kiosk_Helper::get_value_by_key( $channel, 'item' );
  }
  /**
   * Check for existence of forecast column in yahoo weather array. If
   * found returns its value else null
   */
  public static function get_forecast_column_yahoo_weather( $yahoo_weather_array ) {
    $item = self::get_item_column_yahoo_weather( $yahoo_weather_array );
    return Kiosk_Helper::get_value_by_key( $item, 'forecast' );
  }

  /**
   * Check for existence of condition column in yahoo weather array. If
   * found returns its value else null
   */
  public static function get_condition_column_yahoo_weather( $yahoo_weather_array ) {
    $item = self::get_item_column_yahoo_weather( $yahoo_weather_array );
    return Kiosk_Helper::get_value_by_key( $item, 'condition' );
  }
  /**
   * Check for existence of condition code from yahoo weather array. If
   * found returns its value else null
   */
  public static function get_condition_code_column_yahoo_weather( $yahoo_weather_array ) {
    $condition = self::get_condition_column_yahoo_weather( $yahoo_weather_array );
    return Kiosk_Helper::get_value_by_key( $condition, 'code' );
  }

  /**
   * Check for existence of condition temp from yahoo weather array. If
   * found returns its value else null
   */
  public static function get_condition_temp_column_yahoo_weather( $yahoo_weather_array ) {
    $condition = self::get_condition_column_yahoo_weather( $yahoo_weather_array );
    return Kiosk_Helper::get_value_by_key( $condition, 'temp' );
  }
  /**
   * Get the weather image url for given code
   */
  public static function get_yahoo_weather_code_image_url( $code ){
    return empty( $code )
        ? '' : "https://s.yimg.com/zz/combo?/a/i/us/we/52/$code.gif";
  }
}