<?php

/**
 * Kiosk Tweets Helper
 *
 */

namespace Kiosk_WP;


/**
 * Helper file to get tweets for kiosk-tweets shortcode
 * and lives out of wordpress
 */
class Kiosk_Tweets_Helper {
  protected $localsettings = array();
  public $request_not_from_wp = false;
  public function __construct()  {
    $this->load_dependencies();
  }
  /**
   * @override
   */
  public function load_dependencies() {
    // file must be present to include account settings;
    if ( function_exists( 'plugin_dir_path' ) ){
      if ( file_exists( plugin_dir_path( __FILE__ ) . '../localsettings.php' ) ) {
        require( plugin_dir_path( __FILE__ ) . '../localsettings.php' );
        $this->localsettings = $localsettings;
      }
    } else {
      $plugin_path = $_SERVER['DOCUMENT_ROOT'].dirname( $_SERVER['PHP_SELF'] ).'/../';
      if ( file_exists( $plugin_path . '../localsettings.php' ) ) {
        require( $plugin_path . '../localsettings.php' );
        $this->localsettings = $localsettings;
      }
      $this->request_not_from_wp = true ;
    }
  }

  /**
   * kiosk_parse_tweets( $decode, $limit )
   * Accepts the json formatted string and the limit on number of tweets
   * Creates a div block with all the tweets and by converting the hash tags and @ and urls to hyperlinks
   * returns html formated string. If retweeted by some one it displays the original tweet by the user and who retweeted.
   * @return string
   */
  private function kiosk_parse_tweets( $decode, $limit ){
    $kiosk_tweets_header_template = <<<HTML
    <div class="kiosk-tweets_timeline-header">
       <b class="kiosk-tweets_timeline-header__title">Tweets</b>
       <p class="kiosk-tweets_timeline-header__twitter-logo"  title="Twitter" target="_blank">Twitter</p>
    </div>
    <div id="kiosk_tweets_scrollContainer" class="kiosk-tweets_scroll-container">
      <ul class="kiosk-tweets__list" id="kiosk_tweets_list">
HTML;
    $kiosk_tweets_item_template = <<<HTML
        <li class="kiosk-tweets__tweet">
          <div class="kiosk-tweets__tweet__avatar">
              <img src="%s" class="kiosk-tweets__tweet__avatar__image" alt="">
          </div>
          <div class="kiosk-tweets__tweet__details">
            <div class="kiosk-tweets__tweet__details_permalink kiosk-tweets__tweet__font-style">
              <div class="kiosk-tweets__tweet__details__tweet-time">%s</div>
              <div class="kiosk-tweets__tweet__details__actual-tweet-time">%s</div>
            </div>
            <div class="kiosk-tweets__tweet__details__header">
               <div class="kiosk-tweets__tweet__details__header__full-name">%s</div>
               <div class="kiosk-tweets__tweet__details__header__user-name">@%s</div>
            </div>
            <div>
              <div class="kiosk-tweets__tweet__details__text kiosk-tweets__tweet__font-style"> %s </div> 
              <div>%s</div>
            </div>
          </div>
        </li>
HTML;
    $kiosk_tweets_retweet_template = <<<HTML
        <div class="kiosk-tweets__tweet__retweet kiosk-tweets__tweet__font-style">
          <i class="kiosk-tweets__tweet__retweet__icon"></i>
          Retweeted by
          <a target="_blank" href="%s" class="kiosk-tweets__tweet__font-style"> %s </a>
        </div>
HTML;
    $kiosk_tweets_footer_template = <<<HTML
          </ul>
       </div>
HTML;
    $kisok_tweet_items    = '';
    $num_of_items         = 0;
    foreach ( $decode as $tweet ) {
      if ( ++$num_of_items > $limit ){
        break;
      }
      $twitter_api_helper = new \Kiosk_WP\Twitter_Api_Helper();
      $tweet_details = $twitter_api_helper->extract_tweet_details( $tweet );
      if ( ! empty( $tweet_details['tweet_text_retweet_by'] ) ) {
        $kiosk_tweets_retweet    = sprintf(
            $kiosk_tweets_retweet_template,
            $tweet_details['tweet_text_retweet_link'],
            $tweet_details['tweet_text_retweet_by']
        );
      }else {
        $kiosk_tweets_retweet = '';
      }
      $kisok_tweet = sprintf(
          $kiosk_tweets_item_template,
          $tweet_details['tweet_profile_pic'],
          $tweet_details['tweet_date_time'],
          $tweet_details['tweet_date_time_actual'],
          $tweet_details['tweet_full_name'],
          $tweet_details['tweet_screen_name'],
          $tweet_details['tweet_text'],
          $kiosk_tweets_retweet
      );
      $kisok_tweet_items = $kisok_tweet_items.$kisok_tweet;
    }
    return $kiosk_tweets_header_template.$kisok_tweet_items.$kiosk_tweets_footer_template;
  }

  public function kiosk_tweets( $atts, $content = null ) {
    if ( ! is_array( $atts ) ) {
      $atts = array( 'limit' => 20 );
    } else if ( ! array_key_exists( 'limit', $atts ) ){
        $atts['limit'] = 20;
    }

    $twitter_api_params = array(
      'twitter_handle'            => $this->localsettings['twitter_handle'],
      'oauth_access_token'        => $this->localsettings['oauth_access_token'],
      'oauth_access_token_secret' => $this->localsettings['oauth_access_token_secret'],
      'consumer_key'              => $this->localsettings['consumer_key'],
      'consumer_secret'           => $this->localsettings['consumer_secret'],
      'limit'                     => $atts['limit'],
      );
    $json = $this->get_tweets_json( $twitter_api_params );
    if ( empty( $json ) ){
      if ( $this->request_not_from_wp ){
        $kiosk_tweets_div = '';
      } else {
        $kiosk_tweets_div = '<div class="kiosk-tweets">Twitter API Errored</div>';
      }
    } else {
      $decode = json_decode( $json, true ); //getting the file content as array
      if ( $decode != null && json_last_error( ) === JSON_ERROR_NONE ) {
        if ( array_key_exists( 'errors' , $decode ) && array_key_exists( 0 , $decode['errors'] ) && array_key_exists( 'message' , $decode['errors'][0] ) ){
          $kiosk_tweets_div = '<div class="kiosk-tweets">' . $decode['errors'][0]['message']. '</div>';
        } else {
          $kiosk_tweets_div = '<div class="kiosk-tweets">' . $this->kiosk_parse_tweets( $decode, $atts['limit'] ) . '</div>';
        }
      } else {
        $kiosk_tweets_div = '';
        error_log( basename( __FILE__ ) .' Twitter API error: JSON ' . json_last_error_msg() . "\n" );
      }
    }
    return $kiosk_tweets_div;
  }
  /**
   * get_tweets_json( $twitter_api_params ) is being used as part of unit test cases to mock
   * up data it written separately.
   * @param array
   * @return JSON object
   */
  public function get_tweets_json( $twitter_api_params ){
    $twitter_api_helper = new \Kiosk_WP\Twitter_Api_Helper();
    $json = $twitter_api_helper->tweets_json( $twitter_api_params );
    return $json;
  }
}
