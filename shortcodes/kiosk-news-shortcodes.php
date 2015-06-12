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
  protected $feed_helper;

  public function __construct( $feed_helper ) {
    $this->plugin_slug = 'kiosk-news-shortcodes';
    $this->version     = '0.1';
    $this->load_dependencies();
    $this->define_hooks();
    //$this->feed_helper = new Feed_Helper();
    $this->feed_helper = $feed_helper;
  }

  /**
   * @override
   */
  public function load_dependencies() {

  }

  public function define_hooks() {
    $this->add_shortcode( 'kiosk-asu-news', $this, 'kiosk_asu_news' );
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
    $atts                  = shortcode_atts(
        array(
          'feed'           => '153,178,358,40',
          'limit'          => '20',
          'content_limit'  => '50',
        ),
        $atts
    );
    $feed                  = explode( ',', $atts['feed'] );
    for ( $i = 0 ; $i < count( $feed ); $i++ ) {
      $feed_urls_array[]   = "https://asunews.asu.edu/taxonomy/term/$feed[$i]/all/feed";
    }
    $limit                 = $atts['limit'];
    $content_limit         = $atts['content_limit'];
    $items                 = $this->feed_helper->get_feed_data( $feed_urls_array );
    // sorts the news ordered by date using helper function in Feed_Helper class
    usort( $items, array( 'Kiosk_WP\Feed_Helper', 'rss_sort_date_dsc' ) );
    // remove the duplicate items based on title
    $items  = Feed_Helper::remove_duplicate_rss_items( $items );
    // extract the required content from the feed
    $list_items            = Feed_Helper::extract_news_from_rss_feed( $limit, $content_limit, $items );
    // get the carousel slider
    $carousel_slider    = $this->get_carousel_slider( $list_items );
    $kiosk_asu_news_div = '<div class="kiosk-asu-news">' . $carousel_slider . '</div>';
    return $kiosk_asu_news_div;
  }
  /**
   * get_carousel_slider( $list_items )
   * creates a carousel slider and returns it for the given items
   * @param array $list_items
   * @return string
   */
  private function get_carousel_slider( $list_items ){
    $carousel_slider = '';
    $prefix = 'kiosk-asu-news';
    $layout_template = <<<HTML
      <div class="kiosk-asu-news__slider__header">
        <a href="%s" title="%s"><h3><p>%s</p></h3></a>
      </div>
      <div class="kiosk-asu-news__slider__time">
        <p>%s</p>
      </div>
      <div class="kiosk-asu-news__slider__content">
        <p>%s</p>
      </div>
HTML;
    if ( count( $list_items ) > 0 ){
      $carousel_slider   = Carosuel_Slider_Helper::generate_carousel_slider( $prefix, $layout_template, $list_items );
    }
    return $carousel_slider;
  }
}