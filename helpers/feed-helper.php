<?php
  /**
   * Feed helper
   *
   */
namespace Kiosk_WP;
class Feed_Helper {
  public function __construct() {
  }
    /**
   * rss_sort_date_dsc( $a, $b ) compares two items
   * by date and returns 0 if they are equal else 1 or -1
   * Examples usage: usort( $items, array( 'Kiosk_Helper', 'rss_sort_date_dsc' ) )
   * @param simplepie_object $a
   * @param simplepie_object $b
   * @return int
   */
  public static function rss_sort_date_dsc( $a, $b ) {
    $a_start_date = strtotime( $a->get_date() );
    $b_start_date = strtotime( $b->get_date() );
    if ( $a_start_date == $b_start_date ) {
      return 0;
    }
    return ( $a_start_date < $b_start_date ) ? 1 : -1;
  }
  /**
   * remove_duplicate_rss_items( $rss ) removes duplicate items based on title
   * @param array of simplepie objects $rss
   * @return array
   */
  public static function remove_duplicate_rss_items( $rss ) {
    /* new length of modified array */
    $newlength = 1;
    $length    = count( $rss );
    for ( $i = 1; $i < $length; $i++ ) {
      for ( $j = 0; $j < $newlength ; $j++ ) {
        if ( $rss[ $i ]->get_title() == $rss[ $j ]->get_title() ) {
          break;
        }
      }
      /* if none of the values in index[0..j] of array is not same as array[i],
      then copy the current value to corresponding new position in array */
      if ( $j == $newlength ) {
        $rss[ $newlength++ ] = $rss[ $i ];
      }
    }
    return array_slice( $rss, 0, $newlength - 1 );
  }
  /**
   * get_feed_data( $feed_urls_array )
   * @param array
   * @return array
   */
  public function get_feed_data( $feed_urls_array ) {
    $items                 = [];
    $total_feed_count      = 0;
    for ( $i = 0 ; $i < count( $feed_urls_array ); $i++ ) {
      $feed_url = $feed_urls_array[ $i ];
      $feed     = $this->simple_pie_feed_fetch( $feed_url );
      if ( ! is_wp_error( $feed ) ) : // Checks that the object is created correctly
        $items            = array_merge( $items, $feed->get_items( 0 ) ); // create an array of items
        $total_feed_count = $total_feed_count + count( $items );
      endif;
      // If feed is not avaialable and tried all the feed urls show as feed
      // unavailable else try next feed url
      if ( 0 == $total_feed_count ){
        if ( $i == count( $feed ) - 1 ){
          error_log( basename( __FILE__ ) . " The feed is either empty or unavailable\n" );
        }else {
          continue;
        }
      }
    }
    return $items;
  }
  /**
  * simple_pie_feed_fetch( $feed_url ) connects to url and fetches rss feed using
  * simple pie for wordpress
  * This method is not declared static to mock for unit test cases
  * @param string $feed_url
  * @return simple_pie object
  */
  public function simple_pie_feed_fetch( $feed_url ) {
    if ( function_exists( 'fetch_feed' ) ) {
      include_once( ABSPATH . WPINC . '/feed.php' ); // include the required file to pull feed
    }else {
      error_log( 'Required file missing to import feed' );
      return '';
    }
    return fetch_feed( $feed_url );
  }
  /**
   * extract_images_from_flicker_feed( $items, $limit )
   * @param array of simple pie objects $items
   * @param int $limit
   * @return array with image_urls, alt text, title
   */
  public static function extract_images_from_flicker_feed( $items, $limit ) {
    $total_feed_count = count( $items );
    $list_item = null;
    for ( $i = 0; ( $i < $limit ) && $total_feed_count > 0 && ( $i <= $total_feed_count ); $i++ ) {
      $item = $items[ $i ];
      // Take the image tag src attribute from the content and store it in pics variable
      //(?<!_)negative lookbehind  [\'"] match either ' or " (abc)capture group \1 backreference to group #1
      preg_match_all( '/<img[^>]+>/i', $item->get_description(), $pics );
      if ( 1 <= count( $pics[0] ) ) {
        $res       = explode( '"', $pics[0][0] );
        $list_item[] = array( str_replace( '_m.jpg', '_b.jpg', $res[1] ), $res[7], $res[7] );
      }else {
        continue;
      }
    }
    return $list_item;
  }
    /**
   * extract_news_from_rss_feed( $limit, $content_limit, $items )
   * extracts the required number of items(limit) with required
   * content using word limit(content_limit)
   * @param string string array
   * @return array
   */
  public static function extract_news_from_rss_feed( $limit, $content_limit, $items ){
    $count_after_remove_duplicates  = count( $items );
    $list_items = array();
    $current_post_count = 0;
    if ( $count_after_remove_duplicates > 0 ) {
      for ( $current_feed = 0; ( $current_feed < $limit ) && $count_after_remove_duplicates > 0 && ( $current_feed < $count_after_remove_duplicates ); $current_feed++ ) {
        $item         = $items[ $current_feed ];
        $list_item    = array();
        $list_item[]  = $item->get_permalink();
        $list_item[]  = $item->get_title();
        $list_item[]  = $item->get_title();
        $list_item[]  = $item->get_date( 'j F Y @ g:i a' );
        $list_item[]  = Feed_Helper::content_excerpt( $item->get_description(), $content_limit );
        $list_items[] = $list_item;
        $current_post_count++;
      }
    }
    return $list_items;
  }
  /**
   * Excerpt. Uses the excerpt if it is set, otherwise uses the main body if it is
   * less than 50 words.
   * @param string $contentExcerpt
   * @return int $words
   */
  public static function content_excerpt( $contentExcerpt, $words = 50 ) {
    $content = strip_tags( $contentExcerpt );
    if ( true === strpos( $content, 'Article source:' ) ){
      $content = substr_replace( $content,'',strpos( $content, 'Article source:' ) );
    }
    if ( true === strpos( $content, 'read more' ) ) {
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
}