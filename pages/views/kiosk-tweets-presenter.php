<?php

/**
* Do not put the 403 Forbidden code on this page.
* It is accessed directly
*/
$limit = $_GET['limit'];
if ( is_numeric( $limit ) && 1 <= $limit && 100 >= $limit ) {
  $plugin_path = $_SERVER['DOCUMENT_ROOT'].dirname( $_SERVER['PHP_SELF'] );
  require_once $plugin_path . '/../../helpers/kiosk-tweets-helper.php';
  require_once $plugin_path . '/../../helpers/twitter-helper.php';
  require_once $plugin_path . '/../../helpers/json-decode-helper.php';
  $parent = $_SERVER['DOCUMENT_ROOT'] . dirname( $_SERVER['PHP_SELF'] ) . '/../';
  $tweet_helper = new \Kiosk_WP\Kiosk_Tweets_Helper();
  echo $tweet_helper->kiosk_tweets( array( 'limit' => $limit, ) );
} else {
  die();
}

