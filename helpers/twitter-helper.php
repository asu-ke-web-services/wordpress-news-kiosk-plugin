<?php

/**
 * Twitter API helper
 *
 */

namespace Kiosk_WP;

class Twitter_Api_Helper {
  /**
   * Creates encoded url with given parameters
   * We need to use ksort as this uses Hashing for the oauth_signature_method
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
  public static function create_base_url( $baseURI, $method, $params ) {
    $encode_params      = array();
    ksort( $params );
    foreach ( $params as $key => $value ) {
      $encode_params[]  = "$key=" . rawurlencode( $value );
    }
    return $method
        . '&'
        . rawurlencode( $baseURI )
        . '&'
        . rawurlencode( implode( '&', $encode_params ) );
  }

  /**
   * Creates a Header for authentication to invoke twitter streaming API
   * @return string
   */
  public static function create_request_header( $oauth ) {
    $encode_header  = 'Authorization: OAuth ';
    $values         = array();
    foreach ( $oauth as $key => $value ) {
      $values[]     = "$key=\"" . rawurlencode( $value ) . '"';
    }
    $encode_header .= implode( ', ', $values );
    return $encode_header;
  }

  /**
   * Connects to twitter streaming API using given credentails
   * and creates a json formatted string with all the given limit of tweets
   * @param string $oauth_access_token
   * @param string $oauth_access_token_secret
   * @param string $consumer_key
   * @param string $consumer_secret
   * @param string $query
   * @param int $limit
   * @param string $handle
   * @return string
   *
   */
  public static function get_tweets_json(
    $oauth_access_token,
    $oauth_access_token_secret,
    $consumer_key,
    $consumer_secret,
    $query,
    $limit ) {

    if ( ! empty( $query ) ) {
      $twitter_api_url = 'https://api.twitter.com/1.1/search/tweets.json';
    } else {
      $twitter_api_url = 'https://api.twitter.com/1.1/'
          . 'statuses/user_timeline.json';
    }
    $oauth                    = array(
     'oauth_consumer_key'     => $consumer_key,
     'oauth_nonce'            => uniqid(),
     'oauth_signature_method' => 'HMAC-SHA1',
     'oauth_token'            => $oauth_access_token,
     'oauth_timestamp'        => time(),
     'oauth_version'          => '1.0',
     'q'                      => $query,
     'count'                  => $limit,
     'include_rts'            => 1,
    );

    $base_url                 = self::create_base_url(
        $twitter_api_url,
        'GET',
        $oauth
    );
    $composite_key            = rawurlencode( $consumer_secret )
        . '&'
        . rawurlencode( $oauth_access_token_secret );
    $oauth_signature          = base64_encode(
        hash_hmac( 'sha1', $base_url, $composite_key, true )
    );
    $oauth['oauth_signature'] = $oauth_signature;

    // Make Requests
    $header                   = array(
      self::create_request_header( $oauth ),
      'Content-Type: application/json',
      'Expect:',
    );
    $curl_options             = array(
      CURLOPT_HTTPHEADER      => $header,
      CURLOPT_HEADER          => false,
      CURLOPT_URL             => $twitter_api_url . '?q=' . rawurlencode( $query ) . '&count=' . $limit . '&include_rts=1',
      CURLOPT_RETURNTRANSFER  => true,
      CURLOPT_SSL_VERIFYPEER  => false,
    );
    $feed                     = curl_init();
    curl_setopt_array( $feed, $curl_options );
    $json                     = curl_exec( $feed );
    if ( curl_error( $feed ) ) {
      $json   = '';
      error_log(
          basename( __FILE__ )
          . ' Twitter API error: CURL '
          . curl_strerror( curl_errno( $feed ) )
          . "\n"
      );
    }
    curl_close( $feed );
    return $json;
  }

  /**
   * Updates tweet time relative to current time in case of
   * less than 24 hours as hours ago
   * or less than 1 hour as minutes ago
   * or less than 1 minute as seconds ago
   * This is duplicate  functionality of relative time update in Javascript
   * @see kiosk-helper.js calculateRelativeTime()
   * @return string
   */
  public static function time_short_form( $tweet_time ) {
    $elapsed_time = time() - strtotime( $tweet_time );
    if ( $elapsed_time < 1 ) {
        return 'now';
    }
    $time_conversion                            = array(
          1                       => 's',
          60                      => 'm',
          60 * 60                 => 'h',
          24 * 60 * 60            => 'd',
    );

    foreach ( $time_conversion as $secs => $unit ) {
      $elapsed_time_to_unit   = $elapsed_time / $secs;
      $not_fractional_unit    = $elapsed_time_to_unit >= 1;
      $less_than_one_day      = 'h' == $unit && $elapsed_time_to_unit < 24;
      $less_than_one_hour     = 'm' == $unit && $elapsed_time_to_unit < 60;
      $less_than_one_minute   = 's' == $unit && $elapsed_time_to_unit < 60;
      if ( $not_fractional_unit
          && ( $less_than_one_day
                || $less_than_one_hour
                || $less_than_one_minute
            )
      ) {
          $rounded_time       = round( $elapsed_time_to_unit );
          return $rounded_time . $unit;
      }
    }
    return date_format( date_create( $tweet_time ),'d M' );
  }

  /**
   * Converts any URL in text to hyperlink
   * @param string
   */
  public static function convert_url_text_to_hyperlink(
      &$tweet_text,
      $classes
  ) {
    // http://www.dynamicdrive.com/forums/archive/index.php/t-64387.html for
    // regex url
    $regex_url    = '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]'
        . '+\.[a-zA-Z]{2,3}(\/\S*)?/';
        // make links link to URL
    if ( preg_match( $regex_url, $tweet_text, $url ) ) {
      // make the urls hyper links
      $tweet_text = preg_replace(
          $regex_url,
          "<a class=\"tweet-text-hyperlink $classes\""
          . " href='{$url[0]}'>{$url[0]}</a> ",
          $tweet_text
      );
    }
  }

  /**
   * Converts any hash tag text in text to hyperlink
   * @param string
   */
  public static function convert_hash_text_to_hyperlink(
      &$tweet_text,
      $classes
  ) {
    $regex_hash     = '/#([a-z_0-9]+)/i';
    if ( preg_match( $regex_hash, $tweet_text, $hash ) ) {
      // make the hash tags hyper links
      $tweet_text   = preg_replace(
          $regex_hash,
          "<a class=\"tweet-text-hyperlink  $classes \""
          . " href='https://twitter.com/search?q={$hash[0]}' >{$hash[0]}"
          . '</a>',
          $tweet_text
      );
      // swap out the # in the URL to make %23
      $tweet_text   = str_replace(
          '/search?q=#',
          '/search?q=%23',
          $tweet_text
      );
    }
  }

  /**
   * Converts any twitter handle text in text to hyperlink
   * @param string
   */
  public static function convert_twitter_handle_text_to_hyperlink(
      &$tweet_text,
      $classes
  ) {
    $regex_user   = '/@([a-z_0-9]+)/i';
    if ( preg_match( $regex_user, $tweet_text, $user ) ) {
      $tweet_text = preg_replace(
          $regex_user,
          "<a class=\"tweet-text-hyperlink $classes \""
          . " href='http://twitter.com/$1'>$0</a >",
          $tweet_text
      );
    }
  }
  /**
   * JSON return by twitter API will have 'statuses' key in case of twitter
   * search api is used. To read actual tweets take data from statutses key.
   *
   * For user timeline we do not have 'statuses' key so read data as it comes
   * form twitter user_timeline api
   * @param JSON
   * @return array
   */
  public static function get_tweets_data( $json ) {
    if ( empty( self::get_twitter_api_error_message( $json ) ) ) {
      $tweets = json_decode( $json, true );
      $tweets = array_key_exists( 'statuses', $tweets )
          ? $tweets = $tweets['statuses'] : $tweets;
    }
    return $tweets;
  }
  /**
   * Gives error message in case of failure. Checks for both JSON format
   * errors and error message from twitter.
   * Use other function self::get_error_message to just get error message from twitter
   * @param JSON
   * @return String or '' if no error
   */
  public static function get_twitter_api_error_message( $tweets_json ) {
    $tweets_json = Kiosk_Helper::convert_json_to_array( $tweets_json );
    if ( ! empty( $tweets_json ) && ! is_array( $tweets_json ) ) {
      return $tweets_json;
    }

    if ( empty( $tweets_json ) ) {
      return '';
    }
    // Good JSON string but there could be error response
    // from twitter API so check and return it.
    return self::get_error_message( $tweets_json );
  }
  /**
   * Extracts the tweet details and return thems. Prefixes the anchor tag class
   * with $prefix
   * @param JSON string $tweet
   * @param String $prefix
   * @return array<profile_pic, relative_date_time, actual_date_time, full_name,
   * screen_name,text retweet_link retweet_by
   */
  public static function extract_tweet_data( $tweet, $prefix ) {

    $tweet_info = array(
        'text'               => '',
        'screen_name'        => '',
        'full_name'          => '',
        'profile_pic'        => '',
        'relative_date_time' => '',
        'actual_date_time'   => '',
        'status_link'        => '',
        'retweet_link'       => '',
        'retweet_by'         => '',
    );

    if ( self::is_retweet( $tweet ) ) {
      $new_tweet = Kiosk_Helper::get_value_by_key( $tweet, 'retweeted_status' );
      $tweet_info['retweet_link'] = self::get_tweet_profile_image( $tweet );
      $tweet_info['retweet_by']   = self::get_tweet_screen_name( $tweet );
    } else {
      $new_tweet = $tweet;
    }
    $tweet_info['text']         = self::get_tweet_text( $new_tweet );
    $tweet_info['screen_name']  = self::get_tweet_screen_name( $new_tweet );
    $tweet_info['full_name']    = self::get_tweet_full_name( $new_tweet );
    $tweet_info['profile_pic']  = self::get_tweet_profile_image( $new_tweet );
    $tweet_info['status_link '] = self::get_tweet_status_link( $new_tweet );
    self::convert_url_text_to_hyperlink( $tweet_info['text'], $prefix );
    self::convert_hash_text_to_hyperlink( $tweet_info['text'], $prefix );
    self::convert_twitter_handle_text_to_hyperlink( $tweet_info['text'], $prefix );
    $tweet_info['relative_date_time'] = self::get_tweet_created_date_short_form(
        $new_tweet
    );
    $tweet_info['actual_date_time']   = self::get_tweet_created_date_actual(
        $new_tweet
    );
    return $tweet_info ;
  }

  /**
   * Get the tweet text from tweet array
   * @return string
   */
  public static function get_tweet_text( $tweet ) {
    return Kiosk_Helper::get_value_by_key( $tweet, 'text' );
  }

  /**
   * Get the Screen name from tweet array
   * @return string
   */
  public static function get_tweet_screen_name( $tweet ) {
    return Kiosk_Helper::get_value_by_key(
        Kiosk_Helper::get_value_by_key( $tweet, 'user' ),
        'screen_name'
    );
  }

  /**
   * Get the full name from tweet array
   * @return string
   */
  public static function get_tweet_full_name( $tweet ) {
    return Kiosk_Helper::get_value_by_key(
        Kiosk_Helper::get_value_by_key( $tweet, 'user' ),
        'name'
    );
  }

  /**
   * Get the profile image src from tweet array
   * @return string
   */
  public static function get_tweet_profile_image( $tweet ) {
    return Kiosk_Helper::get_value_by_key(
        Kiosk_Helper::get_value_by_key( $tweet, 'user' ),
        'profile_image_url_https'
    );
  }

  /**
   * Get the tweet created time in short realtive format
   * @return string
   */
  public static function get_tweet_created_date_short_form( $tweet ) {
    $created_at = Kiosk_Helper::get_value_by_key( $tweet, 'created_at' );
    return empty( $created_at ) ? '' : self::time_short_form( $created_at );
  }

  /**
   * Get the tweet created time from the tweet array
   * @return time
   */
  public static function get_tweet_created_date_actual( $tweet ) {
    return strtotime( Kiosk_Helper::get_value_by_key( $tweet, 'created_at' ) );
  }

  /**
   * Get the status link from the tweet array
   * @return String
   */
  public static function get_tweet_status_link( $tweet ) {
    return Kiosk_Helper::get_value_by_key( $tweet, 'id_str' );
  }
  /**
   * Checks if the current tweet is a retweet if so returns true
   * @return boolean
   */
  public static function is_retweet( $tweet ) {
    return array_key_exists( 'retweet_count', $tweet )
        &&  0 != $tweet['retweet_count']
        && array_key_exists( 'retweeted_status', $tweet );
  }

  /**
   * Checks if the tweets json has an error message from API
   * @param JSON $tweets_json
   * @return boolean
   */
  public static function has_error_message( $tweets_json ) {
    return array_key_exists( 'errors' , $tweets_json )
          && array_key_exists( 0 , $tweets_json['errors'] )
          && array_key_exists( 'message' , $tweets_json['errors'][0] );
  }
  /**
   * If any error message in twitter json response object it will be returned
   * @param JSON
   * @return string
   */
  public static function get_error_message($tweets_json) {
    return self::has_error_message( $tweets_json )
        ? $tweets_json['errors'][0]['message'] : '';
  }
}