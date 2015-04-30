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

  public function __construct()  {
    $this->plugin_slug = 'kiosk-tweets-shortcodes';
    $this->version     = '0.1';
    $this->load_dependencies();
    $this->define_hooks();
  }

  /**
   * @override
   */
  public function load_dependencies() {
    // file must be present to include account settings;
    if ( file_exists( plugin_dir_path( __FILE__ ) . '../localsettings.php' ) ) {
      require( plugin_dir_path( __FILE__ ) . '../localsettings.php' );
      $this->localsettings = $localsettings;
    }
  }

  public function define_hooks() {
    $this->add_shortcode( 'kiosk-tweets', $this, 'kiosk_tweets' );
  }

  function create_base_url( $baseURI, $method, $params ) {
    $r = array();
    ksort( $params );
    foreach ( $params as $key => $value ){
      $r[] = "$key=" . rawurlencode( $value );
    }
    return $method . '&' . rawurlencode( $baseURI ) . '&' . rawurlencode( implode( '&', $r ) );
  }

  function create_request_header( $oauth ) {
    $r = 'Authorization: OAuth ';
    $values = array();
    foreach ( $oauth as $key => $value ) {
      $values[] = "$key=\"" . rawurlencode( $value ) . '"';
    }
    $r .= implode( ', ', $values );
    return $r;
  }

  function kiosk_parse_tweets( $decode ){
    $kiosk_tweets_header_template = <<<HTML
    <div class="kiosk_tweets_timelineHeader">
       <h1 class="kiosk_tweets_timelineTitle kiosk_tweets_header_font_style">Tweets</h1>
        <a class="kiosk_tweets_twitterLogo" href="https://twitter.com/" title="Twitter" target="_blank">Twitter</a>
    </div>
    <div id="kiosk_tweets_scrollContainer" class="kiosk_tweets_scrollContainer">
      <ol class="kiosk_tweets_list" id="kiosk_tweets_list">
HTML;
    $kiosk_tweets_item_template = <<<HTML
        <li class="kiosk_tweet kiosk_tweets_item kiosk_tweets_separator">
          <div class="kiosk_tweets_avatar">
            <a target="_blank" href="https://twitter.com/%s">
              <img src="%s" class="kiosk_tweets_profile-image kiosk_tweets_large" alt="">
            </a>
          </div>
          <div class="kiosk_tweets_details">
            <a target="_blank" href="https://twitter.com/%s/status/%s" class="kiosk_tweets_permalink kiosk_tweets_timestamp_font_style">
              <time class="date">%s</time>
            </a>
            <div class="kiosk_tweets_header">
              <a target="_blank" href="https://twitter.com/%s">
               <span class="kiosk_tweets_fullName kiosk_tweets_font_style">%s</span>
               <span class="kiosk_tweets_userName kiosk_tweets_font_style">@%s</span>
              </a>
            </div>
            <div class="kiosk_tweets_content">
              <div class="kiosk_tweets_text kiosk_tweets_font_style"> %s </div> %s
            </div>
          </div>
        </li>
HTML;
    $kiosk_tweets_retweet_template = <<<HTML
        <div class="kiosk_tweets_retweet kiosk_tweets_font_style">
          <i class="kiosk_tweets_retweetIcon"></i>Retweeted by <a target="_blank" href="%s" class="kiosk_tweets_font_style"> %s </a>
        </div>
HTML;
    $kiosk_tweets_footer_template = <<<HTML
          </ol>
       </div>
HTML;
    $reg_exUrl            = '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';
    $reg_exHash           = '/#([a-z_0-9]+)/i';
    $reg_exUser           = '/@([a-z_0-9]+)/i';
    $kisok_tweet_items    = '';
    foreach ( $decode as $tweet ) {
        $tweet_text         = $tweet['text']; //get the tweet
        $tweet_screen_name  = $tweet['user']['screen_name'];
        $tweet_full_name    = $tweet['user']['name'];
        $tweet_profile_pic  = $tweet['user']['profile_image_url'];
        $tweet_date_time    = date_format( date_create( $tweet['created_at'] ),'d M' );
        $tweet_status_link  = $tweet['id_str'];

      if ( 0 != $tweet['retweet_count'] && 1 <= count( $tweet['retweeted_status'] ) ){
        $tweet_screen_name       = $tweet['retweeted_status']['user']['screen_name'];
        $tweet_full_name         = $tweet['retweeted_status']['user']['name'];
        $tweet_profile_pic       = $tweet['retweeted_status']['user']['profile_image_url'];
        $tweet_date_time         = date_format( date_create( $tweet['retweeted_status']['created_at'] ),'d M' );
        $tweet_status_link       = $tweet['retweeted_status']['id_str'];
        $tweet_text_retweet_link = $tweet['retweeted_status']['user']['profile_image_url'];
        $tweet_text_retweet_by   = $tweet['retweeted_status']['user']['screen_name'];
        $kiosk_tweets_retweet    = sprintf(
            $kiosk_tweets_retweet_template,
            $tweet_text_retweet_link,
            $tweet_text_retweet_by
        );
      }else {
        $kiosk_tweets_retweet = '';
      }
      // make links link to URL
      if ( preg_match( $reg_exUrl, $tweet_text, $url ) ) {
        // make the urls hyper links
        $tweet_text = preg_replace( $reg_exUrl, "<a class=\"kiosk_tweets_font_style\" href='{$url[0]}'>{$url[0]}</a> ", $tweet_text );
      }
      if ( preg_match( $reg_exHash, $tweet_text, $hash ) ) {
        // make the hash tags hyper links
        $tweet_text = preg_replace( $reg_exHash, "<a class=\"kiosk_tweets_font_style\" href='https://twitter.com/search?q={$hash[0]}' >{$hash[0]}</a> ", $tweet_text );
        // swap out the # in the URL to make %23
        $tweet_text = str_replace( '/search?q=#', '/search?q=%23', $tweet_text );
      }
      if ( preg_match( $reg_exUser, $tweet_text, $user ) ) {
        $tweet_text = preg_replace( '/@([a-z_0-9]+)/i', "<a class=\"kiosk_tweets_font_style\" href='http://twitter.com/$1'>$0</a >", $tweet_text );
      }
      $kisok_tweet = sprintf(
          $kiosk_tweets_item_template,
          $tweet_screen_name,
          $tweet_profile_pic,
          $tweet_screen_name,
          $tweet_status_link,
          $tweet_date_time,
          $tweet_screen_name,
          $tweet_full_name,
          $tweet_screen_name,
          $tweet_text,
          $kiosk_tweets_retweet
      );
      $kisok_tweet_items = $kisok_tweet_items.$kisok_tweet;
    }
    return $kiosk_tweets_header_template.$kisok_tweet_items.$kiosk_tweets_footer_template;
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

    $twitter_handle            = $this->localsettings['twitter_handle'];
    $oauth_access_token        = $this->localsettings['oauth_access_token'];
    $oauth_access_token_secret = $this->localsettings['oauth_access_token_secret'];
    $consumer_key              = $this->localsettings['consumer_key'];
    $consumer_secret           = $this->localsettings['consumer_secret'];
    $twitter_api_url           = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    $oauth                     = array(
     'oauth_consumer_key'      => $consumer_key,
     'oauth_nonce'            => time(),
     'oauth_signature_method' => 'HMAC-SHA1',
     'oauth_token'            => $oauth_access_token,
     'oauth_timestamp'        => time(),
     'oauth_version'          => '1.0',
     'screen_name'            => $twitter_handle,
    );

    $base_info        = $this->create_base_url( $twitter_api_url, 'GET', $oauth );
    $composite_key    = rawurlencode( $consumer_secret ) . '&' . rawurlencode( $oauth_access_token_secret );
    $oauth_signature  = base64_encode( hash_hmac( 'sha1', $base_info, $composite_key, true ) );
    $oauth['oauth_signature'] = $oauth_signature;

    // Make Requests
    $header = array(
      $this->create_request_header( $oauth ),
      'Content-Type: application/json',
      'Expect:',
    );
    $options = array(
      CURLOPT_HTTPHEADER     => $header,
      CURLOPT_HEADER         => false,
      CURLOPT_URL            => $twitter_api_url . '?screen_name=' . $twitter_handle,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => false,
    );
    $feed   = curl_init();
    curl_setopt_array( $feed, $options );
    $json   = curl_exec( $feed );
    curl_close( $feed );
    $decode = json_decode( $json, true ); //getting the file content as array
    $kiosk_tweets_div = '<div class="kiosk_tweets">' . $this->kiosk_parse_tweets( $decode ) . '</div>';
    return $kiosk_tweets_div;
  }
}