<?php

/**
* Do not put the 403 Forbidden code on this page.
* It is accessed directly
*/



load_dependencies();
do_request_processing();

function load_dependencies() {
  $plugin_path      = $_SERVER['DOCUMENT_ROOT'] . dirname( $_SERVER['PHP_SELF'] );
  require_once $plugin_path . '/../../helpers/kiosk-weather-helper.php';
  require_once $plugin_path . '/../../helpers/yahoo-weather-helper.php';
  require_once $plugin_path . '/../../helpers/json-decode-helper.php';
}

function do_request_processing() {
  $weather_helper   = new \Kiosk_WP\Kiosk_Weather_Helper();
  echo $weather_helper->kiosk_weather( array( '' ) );
}