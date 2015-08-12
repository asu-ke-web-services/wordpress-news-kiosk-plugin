<?php

/**
 * Tweets Shortcode functionality.
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

class Kiosk_Tweets_Shortcodes extends Base_Registrar {
  public function __construct() {
    $this->define_hooks();
  }


  public function define_hooks() {
    $this->add_shortcode( 'kiosk-tweets', $this, 'kiosk_tweets' );
  }

  /**
   * [kiosk_tweets limit="20" query="@asugreen"]
   *
   * @param $atts array
   * If query for search is not passed it displays user timeline
   * else it display the tweets for given query
   * Generates a <div> tag with tweets
   * Make sure to update twitter_oauth_access_token, twitter_oauth_access_token_secret,
   * twitter_consumer_key,twitter_consumer_secret
   * with required account details in localsettings.php
   *
   */
  public function kiosk_tweets( $atts, $content = null ) {
    $atts                   = shortcode_atts(
        array(
          'limit'    => '20',
          'query'    => '@asugreen',
        ),
        $atts
    );
    $tweets = ( new \Kiosk_WP\Kiosk_Tweets_Handler() )
        ->get_kiosk_tweets_html( $atts, $content );
    return $tweets['response'];
  }
}