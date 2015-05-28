<?php

/**
 * News Shortcode functionality.
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

class Kiosk_News_Shortcodes extends Base_Registrar {
  protected $plugin_slug;
  protected $version;

  public function __construct()  {
    $this->plugin_slug = 'kiosk-news-shortcodes';
    $this->version     = '0.1';

    $this->load_dependencies();
    $this->define_hooks();
  }

  /**
   * @override
   */
  public function load_dependencies() {
    if ( function_exists( 'fetch_feed' ) ) {
        include_once( ABSPATH . WPINC . '/feed.php' ); // include the required file to pull feed
    }else {
      error_log( 'Required file missing to import feed' );
      return '';
    }
  }

  public function define_hooks() {
    $this->add_shortcode( 'kiosk-asu-news', $this, 'kiosk_asu_news' );
  }
  /**
   * Excerpt. Uses the excerpt if it is set, otherwise uses the main body if it is
   * less than 50 words.
   */
  public function content_excerpt( $contentExcerpt, $words = 50 ) {
    $content = strip_tags( $contentExcerpt );
    if ( true == strpos( $content, 'Article source:' ) ){
      $content = substr_replace( $content,'',strpos( $content, 'Article source:' ) );
    }
    if ( true == strpos( $content, 'read more' ) ){
      $content = substr_replace( $content,'',strpos( $content, 'read more' ) );
    }
    $content = trim( $content );
    // If we only have 1 paragraph and less than $words words, reset the content
    // to the full event content
    if ( count( explode( ' ', $content ) ) < $words ) {
        return $content;
    }else {
      // We have some trimming to do
      $content = implode( ' ', array_slice( explode( ' ', $content ), 0, $words ) );
      $content = trim( $content );
      if ( substr( $content, -1 ) == '.' ) {
        $content .= '..';
      } else {
        $content .= '...';
      }
    }

    if ( ! function_exists( 'tidy_parse_string' ) ) {
      error_log( 'Missing tidy_parse_string library.. Failling back to nothing' );
      return $content;
    }

    // Fix any markup we destroyed
    $tidy_config = array(
     'clean'          => true,
     'output-xhtml'   => true,
     'show-body-only' => true,
     'wrap'           => 0,
    );

    $tidy = tidy_parse_string( $content, $tidy_config, 'UTF8' );
    $tidy->cleanRepair();

    return '' . $tidy;
  }

  public function rss_sort_date_dsc($a, $b)
  {
    $a_startDate = strtotime( $a->get_date() );
    $b_startDate = strtotime( $b->get_date() );
    if ( $a_startDate == $b_startDate ) {
      return 0;
    }
    return ( $a_startDate < $b_startDate ) ? 1 : -1;
  }
  public function remove_duplicates_rss( $rss ){
    /* new length of modified array */
    $newlength = 1;
    $length = count( $rss );
    for ( $i = 1; $i < $length; $i++ ){

      for ( $j = 0; $j < $newlength ; $j++ ){
        if ( $rss[ $i ]->get_title() == $rss[ $j ]->get_title() ){
          break;
        }
      }
      /* if none of the values in index[0..j] of array is not same as array[i],
      then copy the current value to corresponding new position in array */
      if ( $j == $newlength ){
        $rss[ $newlength++ ] = $rss[ $i ];
      }
    }
    return array_slice( $rss, 0, $newlength - 1 );
  }

  /**
   * [kiosk_asu_news limit='20' feed='153,178,358,40' content_limit='50']
   *
   * @param $atts array
   * Generates a <div> tag with news from rss feed to display as slider
   * Add more feed data my updating $feed_urls_array variable
   * can be updated to accept as associative array which makes flexible
   *
   */
  public function kiosk_asu_news( $atts, $content = null ) {
    $total_feed_count = 0;
    $atts = shortcode_atts(
        array(
          'feed'  => '153,178,358,40',
          'limit' => '20',
          'content_limit' => '50',
        ),
        $atts
    );
    $feed = explode( ',', $atts['feed'] );
    $limit = $atts['limit'];
    $content_limit = $atts['content_limit'];
    for ( $i = 0 ; $i < count( $feed ); $i++ ) {
      $feed_number = $feed[ $i ];
      $feed_urls_array[ $i ] = "https://asunews.asu.edu/taxonomy/term/$feed_number/all/feed";
    }
    $current_post_count       = 0;
    $kiosk_asu_news_template      = '<li %s data-target="#kiosk_asu_news_slider" data-slide-to="%d"></li>';
    $kiosk_asu_news_item_template = <<<HTML
    <div class="item %s">
      <div class="kiosk-asu-news__slider__header">
        <a href="%s" title="%s"><h3><p>%s</p></h3></a>
        </div>
      <div class="kiosk-asu-news__slider__time">
        <p>%s</p>
      </div>
      <div class="kiosk-asu-news__slider__content">
        <p>%s</p>
      </div>
    </div>
HTML;
    // Prepare carousel
    $div_listitems = <<<HTML
      <div id="kiosk_asu_news_slider" class="carousel slide kiosk-asu-news__slider" data-ride="carousel">
         <ol class="kiosk-asu-news__slider__carousel-indicators carousel-indicators">
HTML;
    $div_sliders        = '<div class="carousel-inner" role="listbox">';
    $items = [];
    for ( $feed_element = 0; $feed_element < count( $feed_urls_array ); $feed_element++ ) {
      //$feed = fetch_feed( $feed_urls_array[ $feed_element ] ); // specify the source feed
      $feed = $this->kiosk_news_fetch_feed( $feed_urls_array[ $feed_element ] );
      if ( ! is_wp_error( $feed ) ) : // Checks that the object is created correctly
        $items = array_merge( $items, $feed->get_items( 0 ) ); // create an array of items
        $total_feed_count = $total_feed_count + count( $items );
      endif;
      // If feed is not avaialable and tried all the feed urls show as feed unavailable else try next feed url
      if ( 0 == $total_feed_count ){
        if ( $feed_element == count( $feed_urls_array ) - 1 ){
          $div_sliders .= '<div>The feed is either empty or unavailable.</div>';
        }else {
          continue;
        }
      }
    }
    usort( $items, array( $this, 'rss_sort_date_dsc' ) );
    $items = $this->remove_duplicates_rss( $items );
    $new_total_feed_count = count( $items );
    for ( $current_feed = 0; ( $current_feed < $limit ) && $new_total_feed_count > 0 && ( $current_feed < $new_total_feed_count ); $current_feed++ ){
      $item = $items[ $current_feed ];
      if ( 0 == $current_post_count ) {
        $div_listitems_active = ' class = "active" ';
        $div_slider_active    = ' active ';
      }else {
        $div_listitems_active = '';
        $div_slider_active    = '';
      }

      $div_listitems .= sprintf(
          $kiosk_asu_news_template,
          $div_listitems_active,
          $current_post_count
      );

      $div_sliders  .= sprintf(
          $kiosk_asu_news_item_template,
          $div_slider_active,
          $item->get_permalink(),
          $item->get_title(),
          $item->get_title(),
          $item->get_date( 'j F Y @ g:i a' ),
          $this->content_excerpt( $item->get_description(), $content_limit )
      );

      $current_post_count++;
    }
     $div_listitems .= '</ol>';
     $div_listitems .= $div_sliders;
     $div_listitems .= '</div>';
     $div_listitems .= '</div>';
     $kiosk_asu_news_div = '<div class="kiosk-asu-news">' . $div_listitems . '</div>';
    return $new_total_feed_count > 0 ? $kiosk_asu_news_div : '';
  }

  /**
   * kiosk_news_fetch_feed( $feed_url ) seperated for unit test mocking purpose
   * It returns either the actual feed in case of normal flow
   * for unit test case it returns the mock up data.
   * Returns a SimplePie object type
   * @return SimplePie.
   */
  function kiosk_news_fetch_feed( $feed_url ){
    return fetch_feed( $feed_url ); // specify the source feed
  }
}