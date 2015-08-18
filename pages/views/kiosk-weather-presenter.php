<?php

/**
* Do not put the 403 Forbidden code on this page.
* It is accessed directly
*/

define( 'WP_USE_THEMES', false );

load_kiosk_weather_presenter_dependencies();
do_kiosk_weather_presenter_request_processing();

function load_kiosk_weather_presenter_dependencies() {
  require( '../../../../../wp-blog-header.php' );
  require( '../../kiosk-plugin.php' );
}

function do_kiosk_weather_presenter_request_processing() {
  global $kiosk_weather_api_status;
  $location = filter_input(
      INPUT_GET,
      'location',
      FILTER_SANITIZE_STRING
  );
  $weather_details = (new \Kiosk_WP\Kiosk_Weather_Handler())
      ->get_kiosk_weather_html( $location );

  status_header( $weather_details['status'] );
  echo $weather_details['response'];
}