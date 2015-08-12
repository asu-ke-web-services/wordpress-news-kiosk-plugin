<?php

/**
* Do not put the 403 Forbidden code on this page.
* It is accessed directly
*/

define( 'WP_USE_THEMES', false );

load_kiosk_weather_presenter_dependencies();
do_kiosk_weather_presenter_request_processing();

function load_kiosk_weather_presenter_dependencies() {
  require('../../../../../wp-blog-header.php');
  require('../../kiosk-plugin.php');
}

function do_kiosk_weather_presenter_request_processing() {
  $location = filter_input(
      INPUT_GET,
      'location',
      FILTER_SANITIZE_STRING
  );
  if ( shortcode_exists( 'kiosk-weather' ) ) {
    status_header( 200 );
    echo do_shortcode( "[kiosk-weather location=$location]" );
  } else {
    echo '';
  }
}