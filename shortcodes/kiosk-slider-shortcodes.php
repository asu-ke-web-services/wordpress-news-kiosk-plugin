<?php

/**
 * Slider Shortcode functionality.
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

class Kiosk_Slider_Shortcodes extends Base_Registrar {
  protected $plugin_slug;
  protected $version;

  public function __construct()  {
    $this->plugin_slug = 'kiosk-slider-shortcodes';
    $this->version     = '0.1';

    $this->load_dependencies();
    $this->define_hooks();
  }

  /**
   * @override
   */
  public function load_dependencies() {
  }

  public function define_hooks() {
    $this->add_shortcode( 'kiosk-slider', $this, 'kiosk_slider' );
  }

  /**
   * [kiosk_slider limit='20']
   *
   * @param $atts array
   * Generates a <div> tag with slider from rss feed to display as slider
   * To add feed from other urls update $feed_urls_array we can improve by accepting associative array
   *
   */
  public function kiosk_slider( $atts, $content = null ) {
    $total_feed_count = 0;
    $atts = shortcode_atts(
        array(
          'limit' => '20',
        ),
        $atts
    );
    $feed_urls_array    = array(
      'https://api.flickr.com/services/feeds/photos_public.gne?id=55424394@N03&lang=en-us&format=rss_200',
    );
    $limit = $atts['limit'];
    $current_post_count = 0;
    $kiosk_gallery_slider_template      = '<li %s data-target="#kiosk_gallery_slider" data-slide-to="%d"></li>';
    $kiosk_gallery_slider_item_template = <<<HTML
    <div class="item %s">
      <img src="%s" class="img-responsive" alt="%s"/>
      <div class="kiosk_gallery_slider_caption carousel-caption">
       <h3>%s</h3>
      </div>
    </div>
HTML;
    // Prepare carousel
    $div_listitems = <<<HTML
      <div id="kiosk_gallery_slider" class="carousel slide" data-ride="carousel">
         <ol class="kiosk_gallery_slider_ol carousel-indicators">
HTML;
    $div_sliders        = '<div class="carousel-inner" role="listbox">';
    for ( $feed_element = 0; $feed_element < count( $feed_urls_array ); $feed_element++ ){
      if ( function_exists( 'fetch_feed' ) ) {
        include_once(ABSPATH . WPINC . '/feed.php');               // include the required file
      }else {
        error_log( 'Required file missing to import feed' );
        break;
      }
      $items = [];
      $feed = fetch_feed( $feed_urls_array[ $feed_element ] ); // specify the source feed
      if ( ! is_wp_error( $feed ) ) : // Checks that the object is created correctly
        $items = array_merge( $items, $feed->get_items( 0 ) ); // create an array of items
        $total_feed_count = $total_feed_count + count( $items );
      endif;
      if ( 0 == $total_feed_count ) {
        if ( $feed_element == count( $feed_urls_array ) -1 ) {
          $div_sliders .= '<div>The feed is either empty or unavailable.</div>';
        }
        else {
          continue;
        }
      }
    }
    $kisok_news_shortcodes = new Kiosk_News_Shortcodes();
    usort( $items, array( $kisok_news_shortcodes, 'rss_sort_date_dsc' ) );
    //$items = $this->remove_duplicates_rss( $items );
    for ( $current_feed = 0; ( $current_feed < $limit ) && $total_feed_count > 0 && ( $current_feed <= $total_feed_count ); $current_feed++ ){
      $item = $items[ $current_feed ];
      if ( 0 == $current_post_count ) {
          $div_listitems_active = ' class = "active" ';
          $div_slider_active    = ' active ';
      }else {
        $div_listitems_active = '';
        $div_slider_active    = '';
      }

      // Take the image tag src attribute from the content and store it in pics variable
      //(?<!_)negative lookbehind  [\'"] match either ' or " (abc)capture group \1 backreference to group #1
      preg_match_all( '/<img[^>]+>/i', $item->get_description(), $pics );
      if ( 1 <= count( $pics[0] ) ) {
        $res = explode( '"', $pics[0][0] );
        $div_listitems .= sprintf(
            $kiosk_gallery_slider_template,
            $div_listitems_active,
            $current_post_count
        );

        $div_sliders  .= sprintf(
            $kiosk_gallery_slider_item_template,
            $div_slider_active,
            str_replace( '_m.jpg', '_b.jpg', $res[1] ), // updating to pic large quality image
            $res[7],
            $res[7]
        );
        $current_post_count++;
      }else {
        continue;
      }
    }
     $div_listitems .= '</ol>';
     $div_listitems .= $div_sliders;
     $div_listitems .= '</div>';
     $div_listitems .= '</div>';
     $kiosk_slider_div = '<div class="kiosk_slider">' . $div_listitems . '</div>';
    return $total_feed_count > 0 ? $kiosk_slider_div : '';
  }
}