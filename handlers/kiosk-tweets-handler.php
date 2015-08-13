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
class Kiosk_Tweets_Handler {
  private static $STATUS        = array( 'success' => 200, 'failure' => 502, );
  private static $ERROR_MESSAGE = 'Cannot Load Tweets';
  protected $localsettings      = array();
  protected $limit;
  protected $query;

  public function __construct() {
    $this->load_dependencies();
  }

  /**
   * @override
   */
  public function load_dependencies() {
    // file must be present to include account settings;
    require( plugin_dir_path( __FILE__ ) . '../localsettings.php' );
    $this->localsettings = $localsettings;
  }

  /**
   * creates a div block with the tweets passed to it
   * @param array $tweets
   * @return string
   */
  private function get_tweet_block( $tweets ) {
    if ( empty( $tweets ) ) {
      return '';
    }
    $div_start   = <<<HTML
    <div class="kiosk-tweets__timeline__title" data-query="%s" data-limit="%s">
       <b class="kiosk-tweets__timeline__title__text">Tweets</b>
       <p class="kiosk-tweets__timeline__title__logo"  title="Twitter"
       target="_blank">Twitter</p>
    </div>
    <div id="kiosk_tweets_scrollContainer" class="kiosk-tweets__container">
      <ul class="kiosk-tweets__tweets" id="kiosk-tweets__tweets">
HTML;
    $tweet_template     = <<<HTML
        <li class="kiosk-tweets__tweets__tweet">
          <div class="kiosk-tweets__tweet__avatar">
              <img src="%s" class="kiosk-tweets__tweet__avatar__image" alt="">
          </div>
          <div class="kiosk-tweets__tweet__details">
            <div class="kiosk-tweets__tweet__details__permalink">
              <div class="kiosk-tweets__tweet__details__tweet-time"
              data-actual-time="%s">%s</div>
            </div>
            <div class="kiosk-tweets__tweet__details__header">
               <div class="kiosk-tweets__tweet__details__header__full-name">
                %s
               </div>
               <div class="kiosk-tweets__tweet__details__header__user-name">
                @%s
               </div>
            </div>
            <div>
              <div class="kiosk-tweets__tweet__details__text"> %s </div>
              <div>%s</div>
            </div>
          </div>
        </li>
HTML;
    $retweet_template  = <<<HTML
        <div class="kiosk-tweets__tweet__retweet">
          <i class="kiosk-tweets__tweet__retweet__icon"></i>
          Retweeted by
          <a target="_blank" href="%s" class="kiosk-tweets__tweet__link">
            %s
          </a>
        </div>
HTML;
    $div_end = '</ul></div>';
    $tweet_items  = '';

    for ( $i = 0; $i < $this->limit && $i < count( $tweets ); $i++ ) {
      $tweet = Twitter_Api_Helper::extract_tweet(
          $tweets[ $i ],
          'kiosk-tweets__tweet__link'
      );
      $retweet  = '';
      if ( ! empty( $tweet['retweet_by'] ) ) {
        $retweet = sprintf(
            $retweet_template,
            $tweet['retweet_link'],
            $tweet['retweet_by']
        );
      }
      $tweet_items .= sprintf(
          $tweet_template,
          $tweet['profile_pic'],
          $tweet['actual_date_time'],
          $tweet['relative_date_time'],
          $tweet['full_name'],
          $tweet['screen_name'],
          $tweet['text'],
          $retweet
      );
    }
    // Sending Search query and limit as data-uri's to client so it can send back via rewrites
    $div_start = sprintf( $div_start, $this->query, $this->limit );
    return $div_start . $tweet_items . $div_end;
  }

  /**
   * Creates a Tweets widget
   * Returns array with two keys status and response.
   * response contains HTML mark up to be displayed.
   * @param array
   * @return array< int status, String response<HTML markup>>
   */
  public function get_kiosk_tweets_html( $atts ) {
    $atts = shortcode_atts(
        array(
          'limit'    => '20',
          'query'    => '@asugreen',
        ),
        $atts
    );
    $tweets       = '';
    $this->limit  = $atts['limit'];
    $this->query  = $atts['query'];
    $tweets_json  = $this->get_tweets_json();

    if ( ! empty( $tweets_json ) ) {
      $tweets = $this->get_tweet_block( Twitter_Api_Helper::get_tweets( $tweets_json ) );
    }

    if ( empty( $tweets ) ) {
      $status = self::$STATUS['failure'];
      $tweets = '<div class="kiosk-tweets__no-data">' . self::$ERROR_MESSAGE  . '</div>';
    } else {
      $status = self::$STATUS['success'];
    }

    return array(
        'status'    => $status,
        'response'  => '<div class="kiosk-tweets">' . $tweets . '</div>',
    );
  }

  /**
   * Reads localsettings.php file and invokes twitter helper class methods
   * @return JSON object or '' on failure
   */
  public function get_tweets_json() {
    if ( isset( $this->localsettings['twitter_oauth_access_token'] )
      && isset( $this->localsettings['twitter_oauth_access_token_secret'] )
      && isset( $this->localsettings['twitter_consumer_key'] )
      && isset( $this->localsettings['twitter_consumer_secret'] )
    ) {
      $json = Twitter_Api_Helper::get_tweets_json(
          $this->localsettings['twitter_oauth_access_token'],
          $this->localsettings['twitter_oauth_access_token_secret'],
          $this->localsettings['twitter_consumer_key'],
          $this->localsettings['twitter_consumer_secret'],
          $this->query,
          $this->limit
      );
      return $json;
    }
    error_log( basename( __FILE__ )
        . " Missing one or more required Twitter authentication details\n"
    );
    return '';
  }
}
