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
  function time_short_form($ptime) {
    $etime = time() - strtotime( $ptime );
    if ( $etime < 1 ) {
        return 'now';
    }
    $a = array(
          1                       => 's',
          60                      => 'm',
          60 * 60                   => 'h',
    );

    foreach ( $a as $secs => $str ) {
      $d = $etime / $secs;
      if ( 'h' == $str && $d < 24 || 'm' == $str && $d < 60 || 's' == $str && $d < 60 ) {
          $r = round( $d );
          return $r . $str;
      }
    }
    return date_format( date_create( $ptime ),'d M' );
  }


  function kiosk_parse_tweets( $decode, $limit ){
    $kiosk_tweets_header_template = <<<HTML
    <div class="kiosk_tweets_timelineHeader">
       <b class="kiosk_tweets_timelineTitle kiosk_tweets_header_font_style">Tweets</b>
       <p class="kiosk_tweets_twitterLogo"  title="Twitter" target="_blank">Twitter</p>
    </div>
    <div id="kiosk_tweets_scrollContainer" class="kiosk_tweets_scrollContainer">
      <ol class="kiosk_tweets_list" id="kiosk_tweets_list">
HTML;
    $kiosk_tweets_item_template = <<<HTML
        <li class="kiosk_tweet kiosk_tweets_item kiosk_tweets_separator">
          <div class="kiosk_tweets_avatar">
            <a class="kiosk_tweets_font_style" target="_blank" href="https://twitter.com/%s">
              <img src="%s" class="kiosk_tweets_profile-image kiosk_tweets_large" alt="">
            </a>
          </div>
          <div class="kiosk_tweets_details">
            <div class=" kiosk_tweets_permalink kiosk_tweets_timestamp_font_style kiosk_tweets_font_style">
              <div class="kiosk_date">%s</div>
              <div class="kiosk_actualtweettime">%s</div>
            </div>
            <div class="kiosk_tweets_header">
               <div class="kiosk_tweets_fullName">%s</div>
               <div class="kiosk_tweets_userName">@%s</div>
            </div>
            <div class="kiosk_tweets_content">
              <div class="kiosk_tweets_text kiosk_tweets_font_style"> %s </div> 
              <div>%s</div>
            </div>
          </div>
        </li>
HTML;
    $kiosk_tweets_retweet_template = <<<HTML
        <div class="kiosk_tweets_retweet kiosk_tweets_font_style">
          <i class="kiosk_tweets_retweetIcon"></i>
          Retweeted by
          <a target="_blank" href="%s" class="kiosk_tweets_font_style"> %s </a>
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
    $num_of_items         = 0;
    foreach ( $decode as $tweet ) {
      if ( ++$num_of_items > $limit ){
        break;
      }
      $tweet_text              = array_key_exists( 'text', $tweet )? $tweet['text'] : ''; //get the tweet
      $tweet_screen_name       = array_key_exists( 'user', $tweet )? $tweet['user']['screen_name'] : '';
      $tweet_full_name         = array_key_exists( 'user', $tweet )? $tweet['user']['name'] : '';
      $tweet_profile_pic       = array_key_exists( 'user', $tweet )? $tweet['user']['profile_image_url'] : '';
      $tweet_date_time         = array_key_exists( 'created_at', $tweet )? $this->time_short_form( $tweet['created_at'] ): '';
      $tweet_date_time_actual  = strtotime( $tweet['created_at'] );
      $tweet_status_link       = array_key_exists( 'id_str', $tweet )? $tweet['id_str'] : '';

      if ( array_key_exists( 'retweet_count', $tweet ) &&  0 != $tweet['retweet_count'] && array_key_exists( 'retweeted_status', $tweet ) ){
        $tweet_screen_name       = $tweet['retweeted_status']['user']['screen_name'];
        $tweet_full_name         = $tweet['retweeted_status']['user']['name'];
        $tweet_profile_pic       = $tweet['retweeted_status']['user']['profile_image_url'];
        //$tweet_date_time       = date_format( date_create( $tweet['retweeted_status']['created_at'] ),'d M' );
        $tweet_date_time         = $this->time_short_form( $tweet['retweeted_status']['created_at'] );
        $tweet_date_time_actual  = strtotime( $tweet['retweeted_status']['created_at'] );
        $tweet_status_link       = $tweet['retweeted_status']['id_str'];
        $tweet_text_retweet_link = $tweet['retweeted_status']['user']['profile_image_url'];
        $tweet_text_retweet_by   = $tweet['user']['screen_name'];
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
        $tweet_text = preg_replace( $reg_exUrl, "<a class=\"kiosk_tweets_font_style kiosk_tweets_hyper_link\" href='{$url[0]}'>{$url[0]}</a> ", $tweet_text );
      }
      if ( preg_match( $reg_exHash, $tweet_text, $hash ) ) {
        // make the hash tags hyper links
        $tweet_text = preg_replace( $reg_exHash, "<a class=\"kiosk_tweets_font_style kiosk_tweets_hash_tag\" href='https://twitter.com/search?q={$hash[0]}' >{$hash[0]}</a> ", $tweet_text );
        // swap out the # in the URL to make %23
        $tweet_text = str_replace( '/search?q=#', '/search?q=%23', $tweet_text );
      }
      if ( preg_match( $reg_exUser, $tweet_text, $user ) ) {
        $tweet_text = preg_replace( '/@([a-z_0-9]+)/i', "<a class=\"kiosk_tweets_font_style kiosk_tweets_at\" href='http://twitter.com/$1'>$0</a >", $tweet_text );
      }
      $kisok_tweet = sprintf(
          $kiosk_tweets_item_template,
          $tweet_screen_name,
          $tweet_profile_pic,
          //$tweet_screen_name,
          //$tweet_status_link,
          $tweet_date_time,
          $tweet_date_time_actual,
          //$tweet_screen_name,
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
    $atts                  = shortcode_atts(
        array(
          'limit'  => '20',
        ),
        $atts
    );
    $json   = $this->kiosk_tweets_json( $atts, $content );
    $decode = json_decode( $json, true ); //getting the file content as array
    if ( array_key_exists( 'errors' , $decode ) && array_key_exists( 0 , $decode['errors'] ) && array_key_exists( 'message' , $decode['errors'][0] ) ){
      $kiosk_tweets_div = '<div class="kiosk_tweets">' . $decode['errors'][0]['message']. '</div>';
    } else {
      $kiosk_tweets_div = '<div class="kiosk_tweets">' . $this->kiosk_parse_tweets( $decode, $atts['limit'] ) . '</div>';
    }
    return $kiosk_tweets_div;
  }
  public function kiosk_tweets_json( $atts, $content ){
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
     'count'                  => $atts['limit'],
     'include_rts'            => 1,
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
      CURLOPT_URL            => $twitter_api_url . '?screen_name=' . $twitter_handle . '&count=' . $atts['limit'] . '&include_rts=1',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => false,
    );
    $feed   = curl_init();
    curl_setopt_array( $feed, $options );
    $json   = curl_exec( $feed );
    curl_close( $feed );
    return $json;
  }
}