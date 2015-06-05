<?php

/**
 * Twitter API helper
 *
 */

namespace Kiosk_WP;

class Twitter_Api_Helper {
  /**
   * We need to use ksort as this uses Hashing for the oauth_signature_method
   * create_base_url( $baseURI, $method, $params ) helper to create encoded url with given parameters
   * @param string $baseURI Twitter API url
   * @param string $method 'GET' or 'POST'
   * @param array $params authentication oauth parameters for twitter account
   *                      - oauth_consumer_key
   *                      - oauth_nonce
   *                      - oauth_signature_method
   *                      - oauth_token
   *                      - oauth_timestamp
   *                      - oauth_version
   *                      - screen_name
   *                      - count
   *                      - include_rts
   * @return string
   */
  private function create_base_url( $baseURI, $method, $params ) {
    $encode_params = array();
    ksort( $params );
    foreach ( $params as $key => $value ){
      $encode_params[] = "$key=" . rawurlencode( $value );
    }
    return $method . '&' . rawurlencode( $baseURI ) . '&' . rawurlencode( implode( '&', $encode_params ) );
  }

  /**
   * create_request_header( $oauth ) creates a Header for authentication to invoke twitter streaming API
   * @return string
   */
  private function create_request_header( $oauth ) {
    $encode_header = 'Authorization: OAuth ';
    $values = array();
    foreach ( $oauth as $key => $value ) {
      $values[] = "$key=\"" . rawurlencode( $value ) . '"';
    }
    $encode_header .= implode( ', ', $values );
    return $encode_header;
  }
  /**
   * tweets_json( $atts ) Connects to twitter streaming API using given credentails
   * and creates a json formatted string with all the given limit of tweets
   * This function returns mock up data incase of unit testing.
   * @param array
   * @return string
   *
   */
  public function tweets_json( $atts ){
    $twitter_handle            = $atts['twitter_handle'];
    $oauth_access_token        = $atts['oauth_access_token'];
    $oauth_access_token_secret = $atts['oauth_access_token_secret'];
    $consumer_key              = $atts['consumer_key'];
    $consumer_secret           = $atts['consumer_secret'];
    $twitter_api_url           = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    $oauth                     = array(
     'oauth_consumer_key'     => $consumer_key,
     'oauth_nonce'            => uniqid(),
     'oauth_signature_method' => 'HMAC-SHA1',
     'oauth_token'            => $oauth_access_token,
     'oauth_timestamp'        => time(),
     'oauth_version'          => '1.0',
     'screen_name'            => $twitter_handle,
     'count'                  => $atts['limit'],
     'include_rts'            => 1,
    );

    $base_url        = $this->create_base_url( $twitter_api_url, 'GET', $oauth );
    $composite_key    = rawurlencode( $consumer_secret ) . '&' . rawurlencode( $oauth_access_token_secret );
    $oauth_signature  = base64_encode( hash_hmac( 'sha1', $base_url, $composite_key, true ) );
    $oauth['oauth_signature'] = $oauth_signature;

    // Make Requests
    $header = array(
      $this->create_request_header( $oauth ),
      'Content-Type: application/json',
      'Expect:',
    );
    $curl_options = array(
      CURLOPT_HTTPHEADER     => $header,
      CURLOPT_HEADER         => false,
      CURLOPT_URL            => $twitter_api_url . '?screen_name=' . $twitter_handle . '&count=' . $atts['limit'] . '&include_rts=1',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => false,
    );
    $feed   = curl_init();
    curl_setopt_array( $feed, $curl_options );
    $json   = curl_exec( $feed );
    if ( curl_error( $feed ) ) {
      $json = '';
      error_log( basename( __FILE__ ) .' Twitter API error: CURL ' . curl_strerror( curl_errno( $feed ) ) . "\n" );
    }
    curl_close( $feed );
    return $json;
  }
  /**
   * time_short_form($tweet_time) Helper function to
   * update tweet time relative to current time in case of
   * less than 24 hours as hours ago
   * or less than 1 hour as minutes ago
   * or less than 1 minute as seconds ago
   * This is duplicate  functionality of relative time update in Javascript
   * @see kiosk-helper.js calculateRelativeTime()
   * @return string
   */
  public function time_short_form( $tweet_time ) {
    $elapsed_time = time() - strtotime( $tweet_time );
    if ( $elapsed_time < 1 ) {
        return 'now';
    }
    $a = array(
          1                       => 's',
          60                      => 'm',
          60 * 60                 => 'h',
          24 * 60 * 60            => 'd',
    );

    foreach ( $a as $secs => $unit ) {
      $ratio_elapsed_to_unit = $elapsed_time / $secs;
      $not_a_fractional_unit = $ratio_elapsed_to_unit >= 1;
      $less_than_one_day = 'h' == $unit && $ratio_elapsed_to_unit < 24;
      $less_than_one_hour = 'm' == $unit && $ratio_elapsed_to_unit < 60;
      $less_than_one_minute = 's' == $unit && $ratio_elapsed_to_unit < 60;
      if ( $not_a_fractional_unit &&( $less_than_one_day || $less_than_one_hour || $less_than_one_minute ) ) {
          $rounded_time = round( $ratio_elapsed_to_unit );
          return $rounded_time . $unit;
      }
    }
    return date_format( date_create( $tweet_time ),'d M' );
  }
  /**
   * convert_url_text_to_hyperlink( $tweet_text )
   * @param string
   * @return string
   */
  public function convert_url_text_to_hyperlink( $tweet_text ){
    //http://www.dynamicdrive.com/forums/archive/index.php/t-64387.html for regex url
    $regex_url = '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';
        // make links link to URL
    if ( preg_match( $regex_url, $tweet_text, $url ) ) {
      // make the urls hyper links
      $tweet_text = preg_replace( $regex_url, "<a class=\"tweet-text-hyperlink\" href='{$url[0]}'>{$url[0]}</a> ", $tweet_text );
    }
    return $tweet_text;
  }
  /**
   * convert_hash_text_to_hyperlink( $tweet_text )
   * @param string
   * @return string
   */
  public function convert_hash_text_to_hyperlink( $tweet_text ){
    $regex_hash = '/#([a-z_0-9]+)/i';
    if ( preg_match( $regex_hash, $tweet_text, $hash ) ) {
      // make the hash tags hyper links
      $tweet_text = preg_replace( $regex_hash, "<a class=\"tweet-text-hyperlink\" href='https://twitter.com/search?q={$hash[0]}' >{$hash[0]}</a> ", $tweet_text );
      // swap out the # in the URL to make %23
      $tweet_text = str_replace( '/search?q=#', '/search?q=%23', $tweet_text );
    }
    return $tweet_text;
  }
  /**
   * convert_twitter_handle_text_to_hyperlink( $tweet_text )
   * @param string
   * @return string
   */
  public function convert_twitter_handle_text_to_hyperlink( $tweet_text ){
    $regex_user = '/@([a-z_0-9]+)/i';
    if ( preg_match( $regex_user, $tweet_text, $user ) ) {
      $tweet_text = preg_replace( $regex_user , "<a class=\"tweet-text-hyperlink\" href='http://twitter.com/$1'>$0</a >", $tweet_text );
    }
    return $tweet_text;
  }
  /**
   * extract_tweet_details( $tweet ) creates a default tweet details tempalate
   * @param JSON object
   * @return array
   */
  public function extract_tweet_details( $tweet ){
    $tweet_details         = array(
      'tweet_text'              => '',
      'tweet_screen_name'       => '',
      'tweet_full_name'         => '',
      'tweet_profile_pic'       => '',
      'tweet_date_time'         => '',
      'tweet_date_time_actual'  => '',
      'tweet_status_link'       => '',
      'tweet_text_retweet_link' => '',
      'tweet_text_retweet_by'   => '',
      );
    $tweet_details['tweet_text']              = array_key_exists( 'text', $tweet )? $tweet['text'] : ''; //get the tweet
    $tweet_details['tweet_screen_name']       = array_key_exists( 'user', $tweet )? $tweet['user']['screen_name'] : '';
    $tweet_details['tweet_full_name']         = array_key_exists( 'user', $tweet )? $tweet['user']['name'] : '';
    $tweet_details['tweet_profile_pic']       = array_key_exists( 'user', $tweet )? $tweet['user']['profile_image_url'] : '';
    $tweet_details['tweet_date_time']         = array_key_exists( 'created_at', $tweet )? $this->time_short_form( $tweet['created_at'] ): '';
    $tweet_details['tweet_date_time_actual']  = strtotime( $tweet['created_at'] );
    $tweet_details['tweet_status_link ']      = array_key_exists( 'id_str', $tweet )? $tweet['id_str'] : '';

    if ( array_key_exists( 'retweet_count', $tweet ) &&  0 != $tweet['retweet_count'] && array_key_exists( 'retweeted_status', $tweet ) ){
      $tweet_details['tweet_screen_name']       = $tweet['retweeted_status']['user']['screen_name'];
      $tweet_details['tweet_full_name']         = $tweet['retweeted_status']['user']['name'];
      $tweet_details['tweet_profile_pic']       = $tweet['retweeted_status']['user']['profile_image_url'];
      $tweet_details['tweet_date_time']         = $this->time_short_form( $tweet['retweeted_status']['created_at'] );
      $tweet_details['tweet_date_time_actual']  = strtotime( $tweet['retweeted_status']['created_at'] );
      $tweet_details['tweet_status_link']       = $tweet['retweeted_status']['id_str'];
      $tweet_details['tweet_text_retweet_link'] = $tweet['retweeted_status']['user']['profile_image_url'];
      $tweet_details['tweet_text_retweet_by']   = $tweet['user']['screen_name'];
    }

    $tweet_details['tweet_text'] = $this->convert_url_text_to_hyperlink( $tweet_details['tweet_text'] );
    $tweet_details['tweet_text'] = $this->convert_hash_text_to_hyperlink( $tweet_details['tweet_text'] );
    $tweet_details['tweet_text'] = $this->convert_twitter_handle_text_to_hyperlink( $tweet_details['tweet_text'] );
    return $tweet_details ;
  }
}