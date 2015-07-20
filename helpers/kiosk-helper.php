<?php
  /**
   * Kiosk helper
   *
   */
namespace Kiosk_WP;
class Kiosk_Helper {

  /**
   * Fetches all the posts with a limit until all posts are read
   * @param string string
   * @return array<Post>
   */
  public static function get_posts_from_db( $query_options ) {
     $current_offset = 0;
     $posts_list     = array();
     $limit          = array_key_exists( 'limit', $query_options )
                          ? $query_options['limit'] : 20;
    while ( true ) {
      $query_options['offset'] = $current_offset;
      $current_offset          = $current_offset + $limit;
      $posts                   = get_posts( $query_options );
      if ( $posts ) {
          $posts_list = array_merge( $posts_list, $posts );
      }else {
        break;
      }
    }
    return $posts_list;
  }

  /**
   * Check for image that can be as attachment or content or page_feature_image
   * attribute if found returns url as string else false;
   * @param int $post_id String $content
   * @return String
   */
  public static function get_image( $post_id = null, $content = null ) {
    if ( empty( $post_id ) && empty( $content ) ) {
      return false;
    }

    //Get image from atachment
    $attachment_image        = self::get_attachment_image_src(
        $post_id
    );
    if ( $attachment_image ) {
      return $attachment_image;
    }

    //Get image from Content
    $content_image           = self::get_content_image(
        $content
    );
    if ( $content_image ) {
      return $content_image;

    }

    //Get image from page feature attribute
    $page_feature_attr_image = self::get_page_feature_attr_image(
        $post_id
    );
    if ( $page_feature_attr_image ) {
      return $page_feature_attr_image;
    }

    return false;
  }

  /**
   * Checks if a post has passed its end date
   * defined by kiosk-end-date attribute
   * @param int $post_id
   * @return boolean
   */
  public static function has_post_expired( $post_id ) {
    if ( empty( $post_id ) ) {
      return true;
    }
    $kiosk_end_date     = get_post_meta( $post_id, 'kiosk-end-date', true );
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
   * returns image url else false
   * @param int $post_id
   * @return String
   */
  public static function get_attachment_image_src( $post_id ) {
    if ( empty( $post_id ) ) {
      return false;
    }

    $image_attributes = wp_get_attachment_image_src(
        get_post_thumbnail_id( $post_id )
    );
    if ( $image_attributes ) {
      return self::relative_to_absolute_url( $image_attributes[0] );
    }
    return false;
  }

   /**
   * Checks for image in content and if present in HTML markup with img tag
   * returns image url else false
   * @param String $content
   * @return String
   */
  public static function get_content_image( $content ) {
    if ( empty( $content ) ) {
      return false;
    }

    // Take the image tag src attribute from the content and store it in pics
    // variable (?<!_)negative lookbehind  [\'"] match either ' or
    // " (abc)capture group \1 backreference to group #1
    preg_match_all( '/(?<!_)src=([\'"])?(.*?)\\1/', $content, $pics );

    if ( ! empty( $pics[2] ) ) {
      return self::relative_to_absolute_url( $pics[2][0] );
    }
    return false;
  }

  /**
   * Checks for image as page_feature_attribute and if present
   * returns image url else false
   * @param int $post_id
   * @return String
   */
  public static function get_page_feature_attr_image( $post_id ) {
    if ( empty( $post_id ) ) {
      return false;
    }
    $page_feature_image = get_post_meta(
        $post_id,
        'page_feature_image',
        true
    );
    if ( ! empty( $page_feature_image ) ) {
      return self::relative_to_absolute_url( $page_feature_image );
    }
    return false;
  }

  /**
   * Splits the string delimited by "," and converts to absolute path urls
   * If input empty returns null
   * @param string
   * @return array<image_url>
   */
  public static function explode_urls( $urls ) {
    if ( empty( $urls ) ) {
      return null;
    }
    $paths = array();
    if ( ! empty( $urls ) ) {
      $url_array = explode( ',', $urls );
      for ( $k = 0 ; $k < count( $url_array ); $k++ ) {
        $paths[] = trim( self::relative_to_absolute_url( $url_array[ $k ] ) );
      }
    }
    return $paths;
  }

  /**
   * If url is not of absolute path prefix it to make it absolute
   * If prefix is null the returns prefixing home url
   * @param string string
   * @return string
   */
  public static function relative_to_absolute_url( $url, $prefix = null )
  {
    /* return if already absolute URL */
    if ( parse_url( $url, PHP_URL_SCHEME ) != '' ) {
      return $url;
    }else if ( empty( $prefix ) ){
      return home_url( $url );
    }else {
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