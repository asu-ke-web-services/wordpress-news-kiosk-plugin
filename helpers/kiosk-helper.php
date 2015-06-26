<?php
  /**
   * Kiosk helper
   *
   */
namespace Kiosk_WP;
class Kiosk_Helper {

  /**
   * Pulls images that are as attachment or content or page_feature_image
   * attribute and if found returns and array with image_url and alt text
   * @param string string
   * @return array<image_url, alt_text>
   */
  public static function get_posts_items_from_db( $query_post_options ) {
     $exit_while             = false;
     $cureent_offset_posts   = 0;
     $list_items = array();
     $limit      = array_key_exists( 'limit', $query_post_options )
                      ? $query_post_options['limit'] : 20;
    while ( ! $exit_while ) {
      $query_post_options['offset'] = $cureent_offset_posts;
      $cureent_offset_posts = $cureent_offset_posts + $limit;
      $posts                = get_posts( $query_post_options );

      if ( $posts ) {
        $next_list_items = Kiosk_Helper::check_for_image( $posts );
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
   * Check for images that are as attachment or content or page_feature_image
   * attribute and if found returns and array with image_url and alt text
   * @param array $posts
   * @return array<image_url, alt_text>
   */
  public static function check_for_image( $posts ) {
    $list_items = null;
    foreach ( $posts as $post ) {
      if ( Kiosk_Helper::is_post_expired( $post ) ) {
        continue;
      }
      //Check if featured image is present or not
      $attachment_image         = Kiosk_Helper::get_attachment_image_src(
          $post
      );
      $content_image            = Kiosk_Helper::get_content_image( $post );
      $page_feature_attr_image  = Kiosk_Helper::get_page_feature_attr_image(
          $post
      );
      if ( null != $attachment_image ) {
        $list_items[]  = self::prepare_kiosk_posts_data(
            $attachment_image,
            $post
        );
        //Check if posts had images in its body
      } else if ( null != $content_image ) {
        $list_items[]  = self::prepare_kiosk_posts_data(
            $content_image,
            $post
        );
        //Check if page_feature_image custom field has image and if it
        //absolute else make absolute url from relative url
      } else if ( null != $page_feature_attr_image ) {
        $list_items[]  = self::prepare_kiosk_posts_data(
            $page_feature_attr_image,
            $post
        );
      }
    }
    return $list_items;
  }

  /**
   * Extracts the required post details from the post object
   */
  private static function prepare_kiosk_posts_data( $post_image, $post ) {
    $list_items                     = array(
      'image' => '',
      'post-title' => '',
      'kiosk-end-date' => '',
      'post-status' => '',
      'post-id' => '',
      'post-date' => '',
    );
    $list_items['image']            = $post_image[0];
    $list_items['post-title']       = $post->post_title;
    $list_items['kiosk-end-date']   = get_post_meta(
        $post->ID,
        'kiosk-end-date',
        true
    );
    $list_items['post-status']      = $post->post_status;
    $list_items['post-id']          = $post->ID;
    $list_items['post-date']        = $post->post_date;
    return $list_items;
  }

    /**
   * is_post_expired( $post ) checks if a post has passed its end date
   * defined by kiosk-end-date attribute
   * @param $post
   * @return boolean
   */
  public static function is_post_expired( $post ) {
    $kiosk_end_date     = get_post_meta( $post->ID, 'kiosk-end-date', true );
    $today              = strtotime( date( 'd-m-Y' ) );
    $expiration_date    = strtotime( $kiosk_end_date );
    //Do not show posts which are expired or doesn't have expiration date
    if ( empty( $expiration_date ) || $expiration_date < $today ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Checks for image as an attachment and if present
   * returns an array with image url and alt text else null
   * @param $post
   * @return array
   */
  public static function get_attachment_image_src( $post ) {
    $list_item          = null;
    $image_attributes   = wp_get_attachment_image_src(
        get_post_thumbnail_id( $post->ID )
    );
    if ( $image_attributes ) {
      $list_item = array( $image_attributes[0], $post->post_title, );

    }
    return $list_item;
  }

   /**
   * Checks for image in content and if present
   * returns an array with image url and alt text else null
   * @param $post
   * @return array
   */
  public static function get_content_image( $post ) {
    $content    = $post->post_content;
    $list_item  = null;
    // Take the image tag src attribute from the content and store it in pics
    // variable (?<!_)negative lookbehind  [\'"] match either ' or
    // " (abc)capture group \1 backreference to group #1
    preg_match_all( '/(?<!_)src=([\'"])?(.*?)\\1/', $content, $pics );
    if ( ! empty( $pics[2] ) ) {
      if ( parse_url( $pics[2][0], PHP_URL_SCHEME ) == '' ) {
        $pics[2][0] = home_url( $pics[2][0] );
      }
      $list_item = array( $pics[2][0], $post->post_title, );
    }
    return $list_item;
  }

  /**
   * Checks for image as page_feature_attribute and if present
   * returns an array with image url and alt text else null
   * @param $post
   * @return array
   */
  public static function get_page_feature_attr_image( $post ) {
    $list_item          = null;
    $page_feature_image = get_post_meta(
        $post->ID,
        'page_feature_image',
        true
    );
    if ( ! empty( $page_feature_image ) ) {
      if ( parse_url( $page_feature_image, PHP_URL_SCHEME ) == '' ) {
        $page_feature_image = home_url( $page_feature_image );
      }
      $list_item = array( $page_feature_image, $post->post_title, );
    }
    return $list_item;
  }

  /**
   * Splits the string delimited by "," and creates absolute path image url
   * @param string
   * @return array<image_url, alt_text>
   */
  public static function get_default_images( $default_image ) {
    $list_items = null;
    $list_item  = array(
      'image' => '',
      'post-title' => '',
    );
    if ( ! empty( $default_image ) ) {
      $default_image_array = explode( ',', $default_image );
      for ( $k = 0 ; $k < count( $default_image_array ); $k++ ) {
        if ( parse_url( $default_image_array[ $k ], PHP_URL_SCHEME ) == '' ) {
          $default_image_array[ $k ] = home_url( $default_image_array[ $k ] );
        }
        $list_item['image'] = trim( $default_image_array[ $k ] );
        $list_item['post-title'] = '';
        $list_items[] = $list_item;
      }
    }
    return $list_items;
  }

  /**
   * If url is not of absolute path prefix it to make it
   * @param string
   * @return string
   */
  public static function relative_to_absolute_url( $url, $prefix )
  {
    /* return if already absolute URL */
    if ( parse_url( $url, PHP_URL_SCHEME ) != '' ) {
      return $url;
    } else {
      return $prefix . $url;
    }
  }

  //  Can be enhanced to pass key to be used to sort as parameter using closures
  /**
   * Called using usort to sort an array<kiosk-end-date>
   * example: usort($data, "sort_by_date")
   */
  public static function sort_by_date( $first_date, $second_date ) {
    return strtotime( $first_date['kiosk-end-date'] )
            - strtotime( $second_date['kiosk-end-date'] );
  }
}