<?php

/**
* Do not put the 403 Forbidden code on this page.
* It is accessed directly
*/
$range = array(
    'options' => array(
        'min_range' => 1,
        'max_range' => 100,
    )
);

$limit = filter_input( INPUT_GET, 'limit', FILTER_VALIDATE_INT, $range );

if ( false === $limit || null === $limit ) {
  die();
} else {
  load_dependencies();
  do_request_processing();
}

function load_dependencies() {
  //$plugin_path  = $_SERVER['DOCUMENT_ROOT'] . dirname( $_SERVER['PHP_SELF'] );
  $document_root  = filter_input( INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING );
  $php_self       = filter_input( INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING );
  $plugin_path    = $document_root . dirname( $php_self );
  require_once $plugin_path . '/../../helpers/kiosk-tweets-helper.php';
  require_once $plugin_path . '/../../helpers/twitter-helper.php';
  require_once $plugin_path . '/../../helpers/json-decode-helper.php';
}

function do_request_processing() {
  global $limit;
  $tweet_helper = new \Kiosk_WP\Kiosk_Tweets_Helper();
  echo $tweet_helper->kiosk_tweets( array( 'limit' => $limit, ) );
}