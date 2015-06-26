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
   * Compares two items by date and returns 0 if they are equal else 1 or -1
   * Example: usort( $items, array( 'Kiosk_Helper', 'rss_sort_date_dsc' ) )
   * @param SimplePie $a
   * @param SimplePie $b
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
   * Removes duplicate items based on title
   * @param array<SimplePie> $rss
   * @return array<SimplePie>
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
   * Iterates through list of urls passed and fetches the combined feed data
   * @param array $feed_urls_array
   * @return array<SimplePie>
   */
  public function get_feed_data( $feed_urls_array ) {
    $items                 = [];
    $total_feed_count      = 0;
    for ( $i = 0 ; $i < count( $feed_urls_array ); $i++ ) {
      $feed_url = $feed_urls_array[ $i ];
      $feed     = $this->fetch_feed( $feed_url );
      // Checks that the object is created correctly
      if ( ! empty( $feed ) ) {
        // create an array of items
        $items            = array_merge( $items, $feed->get_items( 0 ) );
        $total_feed_count = $total_feed_count + count( $items );
      }
      // If feed is not avaialable and tried all the feed urls show as feed
      // unavailable else try next feed url
      if ( 0 == $total_feed_count ) {
        if ( $i == count( $feed ) - 1 ) {
          error_log(
              basename( __FILE__ )
              . " The feed is either empty or unavailable\n"
          );
        } else {
          continue;
        }
      }
    }
    return $items;
  }

  /**
  * Connects to url and fetches rss feed using
  * simple pie for wordpress
  * This method is not declared static to mock for unit test cases
  * @param string $feed_url
  * @return SimplePie
  */
  public function fetch_feed( $feed_url ) {
    if ( function_exists( 'fetch_feed' ) ) {
      // include the required file to pull feed
      include_once( ABSPATH . WPINC . '/feed.php' );
    }else {
      error_log( 'Required file missing to import feed' );
      return '';
    }
    $feed = fetch_feed( $feed_url );

    if ( is_wp_error( $feed ) ) {
      error_log( 'The feed threw an error.' );
      return null;
    } else {
      return $feed;
    }
  }

  /**
   * Extracts the image source and alt text from the feed data
   * @param array of simple pie objects $items
   * @param int $limit
   * @return array with image_url, alt text, title
   */
  public static function extract_images_from_flickr_feed( $items, $limit ) {
    $total_feed_count = count( $items );
    $list_item = null;
    $image_regex = '/<img[^>]+>/i';
    $image_alt_regex = '/alt="([^"]+)"/i';
    $image_source_regex = '/src="([^"]+)"/i';
    for ( $i = 0; $i < $limit &&  $i < $total_feed_count; $i++ ) {
      $item = $items[ $i ];
      preg_match_all( $image_regex, $item->get_description(), $pics );
      if ( 1 <= count( $pics[0] ) ) {
        // Pull out the image source and the image alt
        preg_match( $image_source_regex, $pics[0][0], $source_matches );
        preg_match( $image_alt_regex, $pics[0][0], $alt_matches );

        if ( count( $source_matches ) > 0 ) {
          $image_source = $source_matches[1];
        }

        if ( count( $alt_matches ) > 0 ) {
          $image_alt = $alt_matches[1];
        }

        $list_item[] = array(
            str_replace( '_m.jpg', '_b.jpg', $image_source ),
            $image_alt,
            $image_alt,
        );
      } else {
        continue;
      }
    }
    return $list_item;
  }

  /**
   * Extracts the required number of news items from the feed data
   * @param int $content_limit to limit the number of words
   * @param int $limit to limit the number of news items
   * @param array $items the rss feed data items
   * @return array<array<news_url,title, title, date, content>>
   */
  public static function extract_news_from_rss_feed(
    $limit,
    $content_limit,
    $items ) {
    $items_count  = count( $items );
    $list_items   = array();
    if ( $items_count > 0 ) {
      for ( $i = 0; $i < $limit && $i < $items_count; $i++ ) {
        $item         = $items[ $i ];
        $list_item    = array();
        $list_item[]  = $item->get_permalink();
        $list_item[]  = $item->get_title();
        $list_item[]  = $item->get_title();
        $list_item[]  = $item->get_date( 'j F Y @ g:i a' );
        $list_item[]  = Feed_Helper::content_excerpt(
            $item->get_description(),
            $content_limit
        );
        $list_items[] = $list_item;
      }
    }
    return $list_items;
  }

  /**
   * Excerpt. Uses the excerpt if it is set, otherwise uses the main body if it
   * is less than 50 words.
   * @param string $content_excerpt
   * @return int $words
   */
  public static function content_excerpt( $content_excerpt, $words = 50 ) {
    $content = self::normalize_html_string( $content_excerpt );
    $content = str_replace(
        array( 'Article source:', 'read more' ),
        '',
        $content
    );
    $content = trim( $content );
    // If we only have 1 paragraph and less than $words words, reset the content
    // to the full event content
    if ( count( explode( ' ', $content ) ) < $words ) {
        return $content;
    } else {
      // We have some trimming to do
      $content = implode(
          ' ',
          array_slice( explode( ' ', $content ), 0, $words )
      );
      $content = trim( $content );
      if ( substr( $content, -1 ) == '.' ) {
        $content .= '..';
      } else {
        $content .= '...';
      }
    }

    if ( ! function_exists( 'tidy_parse_string' ) ) {
      error_log( 'Missing tidy_parse_string library... '
          . 'Falling back to nothing'
      );
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
  public static function normalize_html_string( $input ) {
    // Strip HTML Tags
    $clear = strip_tags( $input );
    // Clean up things like &amp;
    $clear = html_entity_decode( $clear );
    // Strip out any url-encoded stuff
    $clear = urldecode( $clear );
    // Replace Multiple spaces with single space
    $clear = preg_replace( '/\s+/', ' ', $clear );
    // Trim the string of leading/trailing space
    $clear = trim( $clear );
    return $clear;
  }
}