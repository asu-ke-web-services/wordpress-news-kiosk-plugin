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
  protected $localsettings    = array();
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
    require(  plugin_dir_path( __FILE__ ) . '../localsettings.php' );
    $this->localsettings = $localsettings;
  }

  /**
   * creates a div block with the tweets passed to it
   * @param array $tweets
   * @return string
   */
  private function generate_tweet_block( $tweets ) {
    $div_start   = <<<HTML
    <div class="kiosk-tweets__timeline__title" data-query="%s" data-limit="%s">
       <b class="kiosk-tweets__timeline__title__text">Tweets</b>
       <p class="kiosk-tweets__timeline__title__logo"  title="Twitter"
       target="_blank">Twitter</p>
    </div>
    <div id="kiosk_tweets_scrollContainer" class="kiosk-tweets__container">
      <ul class="kiosk-tweets__tweets" id="kiosk-tweets__tweets">
HTML;
    $item_template     = <<<HTML
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
      $tweet_data = Twitter_Api_Helper::extract_tweet_data(
          $tweets[ $i ],
          'kiosk-tweets__tweet__link'
      );
      $retweet  = '';
      if ( ! empty( $tweet_data['retweet_by'] ) ) {
        $retweet = sprintf(
            $retweet_template,
            $tweet_data['retweet_link'],
            $tweet_data['retweet_by']
        );
      }
      $tweet_items .= sprintf(
          $item_template,
          $tweet_data['profile_pic'],
          $tweet_data['actual_date_time'],
          $tweet_data['relative_date_time'],
          $tweet_data['full_name'],
          $tweet_data['screen_name'],
          $tweet_data['text'],
          $retweet
      );
    }
    // Sending Search query and limit as data-uri's to client so it can send back via rewrites
    $div_start = sprintf( $div_start, $this->query, $this->limit );
    return $div_start . $tweet_items . $div_end;
  }

  /**
   * kiosk_tweets( $atts, $content = null )
   * sets tweets limit, search string, handle  and request to tweets helper
   * to get the data and creates html to return
   * Returns empty string when there are any errors and invoked using wordpress
   * rewrite urls otherwise returns HTML markup
   * @param array
   * @return string
   */
  public function get_kiosk_tweets_html( $atts, $content = null ) {
    $atts = shortcode_atts(
        array(
          'limit'    => '20',
          'query'    => '@asugreen',
        ),
        $atts
    );
    $this->limit  = $atts['limit'];
    $this->query  = $atts['query'];
    $tweets_json  = $this->get_tweets_json();
    if ( empty( $tweets_json ) ) {

      $kiosk_tweets_data = 'Cannot load tweets';

    } else {
      $twitter_api_error_message = Twitter_Api_Helper::get_twitter_api_error_message( $tweets_json );

      if ( empty( $twitter_api_error_message ) ) {
        $kiosk_tweets_data = $this->generate_tweet_block(
            Twitter_Api_Helper::get_tweets_column_from_twitter_api_response( $tweets_json )
        );
      } else {
        $kiosk_tweets_data = 'Cannot load tweets';

        error_log( basename( __FILE__ )
            . 'Twitter API Errored with: '
            . $twitter_api_error_message . "\n"
        );
      }
    }
    return '<div class="kiosk-tweets">'
        . $kiosk_tweets_data
        . '</div>';
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
    } else {
      error_log( basename( __FILE__ )
          . " Missing one or more required Twitter authentication details\n"
      );
      return '';
    }
  }
}
