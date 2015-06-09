<?php
  /**
   * Kiosk helper
   *
   */
namespace Kiosk_WP;
class Kiosk_Helper {

  /**
   * get_posts_items_from_db( $limit, $tags ) pull images that are as attachment
   * or content or page_feature_image attribute and if found returns and array
   * with image_url and alt text
   * @param string string
   * @return array
   */
  public function get_posts_items_from_db( $limit, $tags ){
     $exit_while             = false;
     $cureent_offset_posts   = 0;
     $list_items = array();
    while ( ! $exit_while ) {
      $query_post_options   = array(
        'post_type'         => array( 'attachment', 'page', 'post' ),
        'posts_per_page'    => $limit,
        'orderby'           => 'post_date',
        'order'             => 'DESC',
        'tag'               => $tags,
        'offset'            => $cureent_offset_posts,
        'post_status'       => 'publish',
      );
      $cureent_offset_posts = $cureent_offset_posts + $limit;
      $posts                = get_posts( $query_post_options );
      if ( $posts ) {
        $next_list_items = $this->check_for_image( $posts );
        if ( null != $next_list_items ) {
          $list_items = array_merge( $list_items, $next_list_items );
        }
      }else {
        //No posts or reached end of posts query by offset
        $exit_while = true;
      }
    }
    return $list_items;
  }
  /**
   * check_for_image( $posts ) check for images that are as attachment
   * or content or page_feature_image attribute and if found returns and array
   * with image_url and alt text
   * @param $posts
   * @return array
   */
  public function check_for_image( $posts ){
    $list_items = null;
    foreach ( $posts as $post ) {
      if ( $this->check_post_expiry( $post ) ){
        continue;
      }
      //Check if featured image is present or not
      $image_from_attachment                = $this->get_attachment_image_src( $post );
      $image_from_content                   = $this->get_image_from_content( $post );
      $image_from_page_feature_attribute    = $this->get_image_from_page_feature_attribute( $post );
      if ( null != $image_from_attachment ) {
        $list_items[]  = $image_from_attachment;
        //Check if posts had images in its body
      }else if ( null != $image_from_content ) {
        $list_items[]  = $image_from_content;
        //Check if page_feature_image custom field has image and if it absolute else make absolute url from relative url //TO DO
      }else if ( null != $image_from_page_feature_attribute ) {
        $list_items[]  = $image_from_page_feature_attribute;
      }
    }
    return $list_items;
  }

    /**
   * check_post_expiry( $post ) checks if a post has passed its end date
   * defined by kiosk-end-date attribute
   * @param $post
   * @return boolean
   */
  public function check_post_expiry( $post ){
    $kiosk_end_date     = get_post_meta( $post->ID, 'kiosk-end-date', true );
    $today              = strtotime( date( 'd-m-Y' ) );
    $expiration_date    = strtotime( $kiosk_end_date );
    //Do not show posts which are expired or doesn't have expiration date specified
    if ( empty( $expiration_date ) || $expiration_date < $today ) { // if expiration date is in the past
      return true;
    } else {
      return false;
    }
  }
  /**
   * get_attachment_image_src( $post ) checks of image as an attachment and if present
   * returns an array with image url and alt text else null
   * @param $post
   * @return array
   */
  public function get_attachment_image_src( $post ){
    $list_item          = null;
    $image_attributes   = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) );
    if ( $image_attributes ) {
      $list_item = array( $image_attributes[0], $post->post_title, );

    }
    return $list_item;
  }
   /**
   * get_image_from_content( $post ) checks of image in content and if present
   * returns an array with image url and alt text else null
   * @param $post
   * @return array
   */
  public function get_image_from_content( $post ){
    $content    = $post->post_content;
    $list_item  = null;
    // Take the image tag src attribute from the content and store it in pics variable
    //(?<!_)negative lookbehind  [\'"] match either ' or " (abc)capture group \1 backreference to group #1
    preg_match_all( '/(?<!_)src=([\'"])?(.*?)\\1/', $content, $pics );
    if ( ! empty($pics[2]) ) {
      if ( parse_url( $pics[2][0], PHP_URL_SCHEME ) == '' ) {
        $pics[2][0] = home_url( $pics[2][0] );
      }
      $list_item = array( $pics[2][0], $post->post_title, );
    }
    return $list_item;
  }
  /**
   * get_image_from_page_feature_attribute( $post ) checks of image as
   * page_feature_attribute and if present
   * returns an array with image url and alt text else null
   * @param $post
   * @return array
   */
  public function get_image_from_page_feature_attribute( $post ){
    $list_item          = null;
    $page_feature_image = get_post_meta( $post->ID, 'page_feature_image', true );
    if ( ! empty( $page_feature_image ) ) {
      if ( parse_url( $page_feature_image, PHP_URL_SCHEME ) == '' ) {
        $page_feature_image = home_url( $page_feature_image );
      }
      $list_item = array( $page_feature_image, $post->post_title, );
    }
    return $list_item;
  }
  /**
   * get_default_images( $default_image )
   * returns an array with image url and alt text '' for the given default image urls
   * @param string
   * @return array
   */
  public function get_default_images( $default_image ){
    $list_item  = null;
    if ( ! empty( $default_image ) ) {
      $default_image_array = explode( ',', $default_image );
      for ( $k = 0 ; $k < count( $default_image_array ); $k++ ) {
        if ( parse_url( $default_image_array[ $k ], PHP_URL_SCHEME ) == '' ) {
          $default_image_array[ $k ] = home_url( $default_image_array[ $k ] );
        }
        $list_item[] = array( trim( $default_image_array[ $k ] ), '', );
      }
    }
    return $list_item;
  }
}