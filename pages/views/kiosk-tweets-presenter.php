<?php

/**
* Do not put the 403 Forbidden code on this page.
* It is accessed directly
*/
define( 'WP_USE_THEMES', false );
$range = array(
    'options' => array(
        'min_range' => 1,
        'max_range' => 100,
    )
);

$limit  = filter_input( INPUT_GET, 'limit', FILTER_VALIDATE_INT, $range );
if ( false === $limit || null === $limit ) {
  die();
} else {
  load_kiosk_tweets_dependencies();
  do_kiosk_tweets_request_processing();
}

function load_kiosk_tweets_dependencies() {
  require( '../../../../../wp-blog-header.php' );
  require( '../../kiosk-plugin.php' );
}

function do_kiosk_tweets_request_processing() {
  global $limit;
  $query  = filter_input( INPUT_GET, 'query', FILTER_SANITIZE_STRING );
  if ( shortcode_exists( 'kiosk-tweets' ) ) {
    status_header( 200 );
    echo do_shortcode( "[kiosk-tweets limit=$limit query=$query]" );
  } else {
    echo '';
  }
}
