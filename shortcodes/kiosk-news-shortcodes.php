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
  /**
   * [kiosk_asu_news]
   *
   * @param $atts array
   * Generates a <div> tag with news from rss feed to display as slider
   * Add more feed data my updating $feed_urls_array variable
   * can be updated to accept as associative array which makes flexible
   *
   */
  public function kiosk_asu_news( $atts, $content = null ) {
    $current_count_feed = 0;
    $atts = shortcode_atts(
        array(
          'feed'  => '153,178,358,40',
          'limit' => '20',
        ),
        $atts
    );
    $feed = explode( ',', $atts['feed'] );
    for ( $i = 0 ; $i < count( $feed ); $i++ ) {
      $feed_number = $feed[ $i ];
      $feed_urls_array[ $i ] = "https://asunews.asu.edu/taxonomy/term/$feed_number/all/feed";
    }
    $current_post_count       = 0;
    $kiosk_asu_news_template      = '<li %s data-target="#kiosk_asu_news_slider" data-slide-to="%d"></li>';
    $kiosk_asu_news_item_template = <<<HTML
    <div class="item %s">
      <div class="kiosk_asu_news_header">
        <a href="%s" title="%s"><h3><p>%s</p></h3></a>
        </div>
      <div>
        <p>%s</p>
      </div>
      <div>
        <p>%s</p>
      </div>
    </div>
HTML;
    // Prepare carousel
    $div_listitems = <<<HTML
      <div id="kiosk_asu_news_slider" class="carousel slide kiosk_asu_news_slider" data-ride="carousel">
         <ol class="kiosk_asu_news_slider_ol carousel-indicators">
HTML;
    $div_sliders        = '<div class="carousel-inner" role="listbox">';
    for ( $feed_element = 0; $feed_element < count( $feed_urls_array ); $feed_element++ ) {
      $feed = fetch_feed( $feed_urls_array[ $feed_element ] ); // specify the source feed
      if ( ! is_wp_error( $feed ) ) : // Checks that the object is created correctly
        //$current_count_feed = $feed->get_item_quantity($current_count_feed); // specify number of items
        $items = $feed->get_items( 0 ); // create an array of items
        $current_count_feed = $current_count_feed + count( $items );
      endif;
      if ( 0 == $current_count_feed ){
        if ( $feed_element == count( $feed_urls_array ) - 1 ){
          $div_sliders .= '<div>The feed is either empty or unavailable.</div>';
        }else {
          continue;
        }
      }
      else {
        foreach ( $items as $item ) :
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
              $this->content_excerpt( $item->get_description(),50 )
          );

          $current_post_count++;
        endforeach;
      }
    }
     $div_listitems .= '</ol>';
     $div_listitems .= $div_sliders;
     $div_listitems .= '</div>';
     $div_listitems .= '</div>';
     $kiosk_asu_news_div = '<div class="kiosk_asu_news">' . $div_listitems . '</div>';
    return $kiosk_asu_news_div;
  }
}