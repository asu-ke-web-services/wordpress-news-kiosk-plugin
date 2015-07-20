<?php

/**
 * Posts Shortcode functionality.
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

class Kiosk_Posts_Shortcodes extends Base_Registrar {
  protected $plugin_slug;
  protected $version;
  public function __construct() {
    $this->plugin_slug = 'kiosk-post-shortcodes';
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
    $this->add_shortcode( 'kiosk-posts', $this, 'kiosk_posts' );
  }

  /**
   * [kiosk_posts tags="t,a,g,s" default_image="http://www.example1.jpg,
   * http://www.example2.jpg,http://www.example3.jpg"]
   * @param $atts array
   * Generates a <div> tag with images from post to display as slider
   * Query the featured images which are attachments by thumbnail ID by
   * passing post ID.
   * Store current post item  and parse it the check if post has any
   * images in the body.
   * Regex string to search for images
   * Run preg_match_all to grab all the images and save the results in $pics
   * Query any custom fields for page_feature_image
   * Query any custom fields for kiosk-end-date
   * Show posts which are having either image as attachment or images in
   * body of post
   * or image with custom field and not expired
   */
  public function kiosk_posts( $atts, $content = null ) {
    $limit                  = 20;
    $atts                   = shortcode_atts(
        array(
          'tags'            => '',
          'default_image'   => '',
        ),
        $atts
    );
    $query_options          = array(
        'post_type'         => array( 'attachment', 'page', 'post' ),
        'posts_per_page'    => $limit,
        'orderby'           => 'post_date',
        'order'             => 'DESC',
        'tag'               => $atts['tags'],
        'post_status'       => 'publish',
    );
    $slider_data = array();
    $posts      = Kiosk_Helper::get_posts_from_db( $query_options );
    foreach ( $posts as $post ) {
      if ( Kiosk_Helper::has_post_expired( $post->ID ) ) {
        continue;
      }

      $image_url = Kiosk_Helper::get_image( $post->ID, $post->post_content );

      if ( $image_url ) {
        $slider_data[] = array( $image_url );
      }
    }

    if ( 0 === count( $slider_data ) ) {
      $slider_data = Kiosk_Helper::explode_urls( $atts['default_image'] );
    }

    $carousel_slider = '';
    if ( 0 != count( $slider_data ) ) {
      $carousel_slider     = $this->get_posts_carousel_slider( $slider_data );
    }
    $kiosk_posts_div       = '<div class="kiosk-posts">'
        . $carousel_slider . '</div>';
    return $kiosk_posts_div;
  }
  /**
   * Creates required template and invokes helper function to create carousel
   * @param array<array> $slider_data
   * @return string
   */
  private function get_posts_carousel_slider( $slider_data ) {
    $prefix          = 'kiosk-posts';
    $carousel_slider = '';
    $layout_template = '<img src="%s" alt="">';
    if ( count( $slider_data ) > 0 ) {
      $carousel_slider   = Carosuel_Slider_Helper::generate_carousel_slider(
          $prefix,
          $layout_template,
          $slider_data
      );
    }
    return $carousel_slider;
  }
}
