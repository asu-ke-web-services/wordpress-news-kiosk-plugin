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

  protected $plugin_slug;
  protected $version;
  protected $localsettings = array();

  public function __construct() {
    $this->plugin_slug = 'kiosk-tweets-shortcodes';
    $this->version     = '0.1';
    $this->define_hooks();
  }


  public function define_hooks() {
    $this->add_shortcode( 'kiosk-tweets', $this, 'kiosk_tweets' );
  }

  /**
   * [kiosk_tweets]
   *
   * @param $atts array
   * Generates a <div> tag with tweets
   * update twitter_handle, oauth_access_token, oauth_access_token_secret,
   * consumer_key,consumer_secret
   * with required account details in localsettings.php
   *
   */
  public function kiosk_tweets( $atts, $content = null ) {
    $atts                   = shortcode_atts(
        array(
          'limit'           => '20',
        ),
        $atts
    );
    $kiosk_tweet_helper     = new \Kiosk_WP\Kiosk_Tweets_Helper();
    return $kiosk_tweet_helper->kiosk_tweets( $atts, $content );
  }
}