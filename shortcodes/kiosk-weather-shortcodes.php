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

  protected $plugin_slug;
  protected $version;
  protected $localsettings = array();

  public function __construct()  {
    $this->plugin_slug = 'kiosk-weather-shortcodes';
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
    $this->add_shortcode( 'kiosk-weather', $this, 'kiosk_weather' );
  }

  function kiosk_parse_weather( $json_weather ) {

    $channel_title = $json_weather['query']['results']['channel']['title'];
    $location_title = $json_weather['query']['results']['channel']['location']['city']. ', ' .$json_weather['query']['results']['channel']['location']['region'];
    $forecast = $json_weather['query']['results']['channel']['item']['forecast'];
    $current_condition_code = $json_weather['query']['results']['channel']['item']['condition']['code'];
    $current_condition_date = date_format( date_create( $json_weather['query']['results']['channel']['item']['condition']['date'] ),'l' );
    $current_condition_temp = $json_weather['query']['results']['channel']['item']['condition']['temp'];

    $current_weather_div_template = <<<HTML
      <img class="kiosk_weather_current_image" src="http://l.yimg.com/a/i/us/we/52/%s.gif"/>
      <p><b>%s<sup>o</sup>%s</b></p>
HTML;

    $forecast_weather_block_template = <<<HTML
      <div class="kiosk_weather_forecast_block col-md-4">
        <h5>%s</h5>
        <img class="kiosk_weather_forecast_block_image" src="http://l.yimg.com/a/i/us/we/52/%s.gif"/>
        <p><b>%s<sup>o</sup> / %s<sup>o</sup></b></p>
      </div>
HTML;

    $current_weather_div = sprintf(
        $current_weather_div_template,
        $json_weather['query']['results']['channel']['item']['condition']['code'],
        $json_weather['query']['results']['channel']['item']['condition']['temp'],
        $json_weather['query']['results']['channel']['units']['temperature']
    );

    $forecast_weather_block = '';
    for ( $i = 1; $i < 4; $i++ ){
      $forecast_weather_block .= sprintf(
          $forecast_weather_block_template,
          date_format( date_create( $forecast[ $i ]['date'] ), 'l' ),
          $forecast[ $i ]['code'],
          $forecast[ $i ]['low'],
          $forecast[ $i ]['high']
      );
    }
    $weather_div = <<<HTML
    <div class="container-fluid">
      <div class="row">
          <div class="kiosk_weather_current col-md-3">
          %s
          </div>
          <div class="kiosk_weather_forecast col-md-9">
            <div class="container-fluid">
              <div class ="row">
                <div class="kiosk_weather_location">
                  <b>%s</b>
                </div>
              </div>
              <div class="row">
                %s
              </div>
            </div>
          </div>
      </div>
    </div>
HTML;
    return sprintf( $weather_div, $current_weather_div, $location_title, $forecast_weather_block );
  }
    /**
   * [kiosk_weather]
   *
   * @param $atts array
   * Generates a <div> tag with current weather and weather forecast
   *
   */
  public function kiosk_weather( $atts, $content = null ) {

    $BASE_URL = 'http://query.yahooapis.com/v1/public/yql';
    $yql_query = 'select * from weather.forecast where woeid in (select woeid from geo.places(1) where text="tempe, az")';
    $yql_query_url = $BASE_URL . '?q=' . urlencode( $yql_query ) . '&format=json';
    // Make call with cURL
    $session = curl_init( $yql_query_url );
    curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );
    $json = curl_exec( $session );
    // Convert JSON to PHP array
    $json_weather = json_decode( $json, true );
    $kiosk_weather_div = '<div class="kiosk_weather">' . $this->kiosk_parse_weather( $json_weather ) . '</div>';
    return $kiosk_weather_div;
  }
}
