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
  public static function extract_weather_data( $weather ) {

    $weather = Kiosk_Helper::convert_json_to_array( $weather );
    if ( ! empty( $weather ) && ! is_array( $weather ) ) {
      error_log( basename( __FILE__ )
          . " Weather API error: JSON $weather\n"
      );
      return '';
    }

    if ( empty( $weather )
        || ! self::has_weather_location_details( $weather ) ) {
      return '';
    }
    $weather_data = array(
      'location' => '',
      'forecast' => array(),
      'image'    => '',
      'unit'     => '',
      'temp'     => '',
      'humidity' => '',
      'speed'    => '',
    );
    $forecast_data = array(
      'date'  => '',
      'image' => '',
      'low'   => '',
      'high'  => '',
    );

    $location_city            = self::get_city( $weather );
    $location_region          = self::get_region( $weather );
    $forecast                 = self::get_forecast( $weather );
    $weather_data['image']    = self::get_image_src( self::get_current_code( $weather ) );
    $weather_data['unit']     = self::get_temperature_unit( $weather );
    $weather_data['temp']     = self::get_current_temp( $weather );
    $weather_data['humidity'] = self::get_humidity( $weather );
    $weather_data['speed']    = self::get_wind_speed( $weather );
    $weather_data['location'] = "$location_city, $location_region";
    for ( $i = 0; $i < count( $forecast ); $i++ ) {
      $forecast_data[ $i ]['day']   = Kiosk_Helper::get_value_by_key( $forecast[ $i ], 'day' );
      $forecast_data[ $i ]['low']   = Kiosk_Helper::get_value_by_key( $forecast[ $i ], 'low' );
      $forecast_data[ $i ]['high']  = Kiosk_Helper::get_value_by_key( $forecast[ $i ], 'high' );
      $forecast_data[ $i ]['image'] = self::get_image_src( Kiosk_Helper::get_value_by_key( $forecast[ $i ], 'code' ) );
    }
    $weather_data['forecast'] = $forecast_data;
    return $weather_data;
  }

  /**
   * Check for existence of results column in yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return array
   */
  public static function get_query( $weather ) {
    return Kiosk_Helper::get_value_by_key( $weather, 'query' );
  }

  /**
   * Checks whether any results found for search location or not
   * @param array
   * @return boolean
   */
  public static function has_weather_location_details( $weather ) {
    if ( Kiosk_Helper::get_value_by_key( self::get_query( $weather ), 'count' ) > 0 ) {
      return true;
    }
    return false;
  }

  /**
   * Check for existence of results column in yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return array
   */
  public static function get_results( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_query( $weather ),
        'results'
    );
  }
  /**
   * Check for existence of channel column in yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return array
   */
  public static function get_channel( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_results( $weather ),
        'channel'
    );
  }
  /**
   * Check for existence of location column in yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return array
   */
  public static function get_location( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_channel( $weather ),
        'location'
    );
  }
  /**
   * Check for existence of city in yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return String
   */
  public static function get_city( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_location( $weather ),
        'city'
    );
  }
  /**
   * Check for existence of region in yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return String
   */
  public static function get_region( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_location( $weather ),
        'region'
    );
  }
  /**
   * Check for existence of units column in yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return array
   */
  public static function get_units( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_channel( $weather ),
        'units'
    );
  }
  /**
   * Check for existence of temperature under units column in yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return String
   */
  public static function get_temperature_unit( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_units( $weather ),
        'temperature'
    );
  }
  /**
   * Check for existence of item column in yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return array
   */
  public static function get_weather( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_channel( $weather ),
        'item'
    );
  }
  /**
   * Check for existence of forecast column in yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return array
   */
  public static function get_forecast( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_weather( $weather ),
        'forecast'
    );
  }

  /**
   * Check for existence of condition column in yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return array
   */
  public static function get_current( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_weather( $weather ),
        'condition'
    );
  }
  /**
   * Check for existence of condition code from yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return String
   */
  public static function get_current_code( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_current( $weather ),
        'code'
    );
  }

  /**
   * Check for existence of condition temp from yahoo weather array. If
   * found returns its value else null
   */
  public static function get_current_temp( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_current( $weather ),
        'temp'
    );
  }
  /**
   * Check for existence of wind data from yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return array
   */
  public static function get_wind( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_channel( $weather ),
        'wind'
    );
  }
  /**
   * Check for existence of wind speed data from yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return String
   */
  public static function get_wind_speed( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_wind( $weather ),
        'speed'
    );
  }
  /**
   * Check for existence of atmosphere data from yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return array
   */
  public static function get_atmosphere( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_channel( $weather ),
        'atmosphere'
    );
  }
  /**
   * Check for existence of humidity data from yahoo weather array. If
   * found returns its value else null
   * @param array
   * @return String
   */
  public static function get_humidity( $weather ) {
    return Kiosk_Helper::get_value_by_key(
        self::get_atmosphere( $weather ),
        'humidity'
    );
  }
  /**
   * Get the weather image url for given code
   */
  public static function get_image_src( $code ){
    return empty( $code )
        ? '' : "https://s.yimg.com/zz/combo?/a/i/us/we/52/$code.gif";
  }
}