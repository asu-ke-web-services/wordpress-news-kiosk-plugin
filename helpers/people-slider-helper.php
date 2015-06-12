<?php
  /**
   * Kiosk helper
   *
   */
namespace Kiosk_WP;
class People_Slider_Helper {
  /**
   * get_content_images( $keyword_slug_quote )
   * updates the array with images for each item using person-slug
   * @param array $keyword_slug_quote
   * @return array
   */
  public function get_content_images( $keyword_slug_quote ) {
    $keyword_slug_quote_image       = array();
    if ( empty( $keyword_slug_quote ) ){
      return $keyword_slug_quote_image;
    }
    foreach ( $keyword_slug_quote as $key => $combination ) {
      if ( ! array_key_exists( 'person_slug', $combination ) ) {
        continue;
      }
      $person_slug                  = $combination['person_slug'];
      $combination['content_image'] = $this->get_person_image(
          $person_slug
      );
      $keyword_slug_quote_image[]   = $combination;
    }
    return $keyword_slug_quote_image;
  }
  /**
   *
   */
  public function get_person_image( $person_slug ) {
    $people_presenter             = new \People_Presenter();
    $person_detail                = $people_presenter->get_detail( $person_slug );
    $content_person_image         = $person_detail->head['og:image'];
    return $content_person_image;
  }
  /**
   * split_content( $pattern, $content )
   * splits the content with passed regex
   * @param string $pattern
   * @param string $content
   * @return array
   */
  public static function split_content( $pattern, $content ) {
    return preg_split( $pattern, trim( $content ), null, PREG_SPLIT_NO_EMPTY );
  }

  /**
   * prepare_keyword_slug_quote_by_delimit( $content, $delimit_regex )
   * parses the content by splitting with delimiter and create and array with
   * keyword, person-slug, quotes combination
   * @param string $content
   * @param string $delimit_regex
   */

  public static function prepare_keyword_slug_quote_by_delimit( $content, $delimit_regex ) {
    //Split by $delimit_regex
    $content_lines = People_Slider_Helper::split_content( $delimit_regex, $content );
    $keyword_pairs = array();
    $keyword_pair  = array();
    for ( $i = 0; $i < count( $content_lines ); $i++ ) {
      $key_values = explode( ':', $content_lines[ $i ] );
      if ( 0 == strcasecmp( 'keyword' , trim( $key_values[0] ) ) ) {
        /* When we see next keyword add last keyword, person-slug, quote combination
           to $keyword_pairs array and create new $keyword_pair to track new combination
        */
        if ( ! empty( $keyword_pair ) ) {
          $keyword_pairs[] = $keyword_pair;
          $keyword_pair              = array();
        }
        $keyword_pair['keyword']     = trim( $key_values[1] );
      } else if ( 0 == strcasecmp( 'person-slug' , trim( $key_values[0] ) ) ) {
        $keyword_pair['person_slug'] = trim( $key_values[1] );
      } else if ( 0 == strcasecmp( 'quote' , trim( $key_values[0] ) ) ) {
        $keyword_pair['quote']       = trim( $key_values[1] );
      }
      /* If reached the end of loop add last keyword, person-slug, quote combination
         to $keyword_pairs array*/
      if ( $i == count( $content_lines ) - 1 ) {
        $keyword_pairs[] = $keyword_pair;
      }
    }
    return $keyword_pairs;
  }

  /**
   * prepare_keyword_slug_quote( $content )
   * parses content when delimiter is not present
   * assumes format as
   * keyword: xxxxx
   * person-slug: xxxxxxxx
   * quote: xxxxxx
   * @param string $content
   * @return array
   */
  public static function prepare_keyword_slug_quote( $content ){
    //Split by keyword with colon
    $keyword_pairs = array();
    $keyword_lines = People_Slider_Helper::split_content( '/keyword\s*:\s*/', $content );

    if ( empty( $keyword_lines ) ){
      error_log( basename( __FILE__ ) ." No Keywords specified\n" );
      return;
    }
    foreach ( $keyword_lines as $line ) {
      $keyword     = '';
      $person_slug = '';
      $quote       = '';
      //Split person-slug:
      $person_slug_lines = People_Slider_Helper::split_content( '/person-slug\s*:\s*/', $line );

      if ( empty( $keyword_lines ) ){
        error_log( basename( __FILE__ ) . ' No person-slug specified for key word = ' . $person_slug_lines[0] . "\n" );
        continue;
      }

      foreach ( $person_slug_lines as $line ) {
        //Split quote:
        $quote_lines   = People_Slider_Helper::split_content( '/quote\s*:\s*/', $line );
        //trim all the elements in array
        $quote_lines   = array_map( 'trim', $quote_lines );
        if ( 2 == count( $quote_lines ) ) {
          $person_slug = $quote_lines[0];
          $quote       = $quote_lines[1];
        } else if ( 1 == count( $quote_lines ) ) {
          $keyword = $quote_lines[0];
        }
      }
      $keyword_pairs[] = array( $keyword, $person_slug, $quote );
    }
    return $keyword_pairs;
  }
  /**
   * get_persons_images_category_keywords()
   * fetches images of persons from GIOS_API for keywords
   * @return array | person_image, keyword
   */
  public function get_persons_images_category_keywords() {
    // ==========
    // GIOS plugin Presenters
    // ==========
    $keyword_presenter                = new \Keywords_Presenter();
    $people_presenter                 = new \People_Presenter();
    $persons_images_category_keywords = array();
    $keywords                         = $keyword_presenter
                                          ->get_expertise_category_keywords();
    foreach ( $keywords as $keyword ) {
      $people = $people_presenter
                     ->get_sustainability_scientists_by_expertise_keyword(
                         slugify( $keyword->keyword )
                     );
      $persons_images_category_keywords[] = People_Slider_Helper::get_persons_images( $keyword->keyword, $people );
    }
    return $persons_images_category_keywords;
  }
  /**
   * get_persons_images( $keyword, $people )
   * Fetches the image of each person and return back images along with keyword
   * @param string $keyword
   * @param string $array
   * @return array
   */
  public static function get_persons_images( $keyword, $people ) {
    $persons_images   = array();
    for ( $i = 0; $i < count( $people ); $i++ ) {
      $person         = $people[ $i ];
      $person_image   = $person->photo_url();
      if ( false != $person_image ) {
        $persons_images[]  = array(
            'person_image' => $person_image,
            'keyword'      => $keyword,
        );
      }
    }
    return $persons_images;
  }
  // Not using this function for now
  /**
   * get_atmost_quantity_persons_images( $people, $quantity )
   * Helper function to fecth only maximum of required number of people array
   */
  public static function get_atmost_quantity_persons_images( $people, $quantity ) {
    $count_people         = count( $people );
    $persons_images       = array();
    $random_int_exlusion  = array();
    do {
      $quantity = $quantity - count( $persons_images );
      $random_people        = Kiosk_Helper::generate_unique_random_int_in_range(
          0,
          $count_people - 1,
          $quantity,
          $random_int_exlusion
      );
      $tmp_persons_images   = People_Slider_Helper::get_persons_images( $people, $random_people );
      $persons_images       = array_merge( $persons_images, $tmp_persons_images );
      $random_int_exlusion  = array_merge( $random_int_exlusion, $random_people );

    } while ( (count( $persons_images ) < $quantity )
                && ( $count_people > $quantity )
                && (count( $random_int_exlusion ) != $count_people )
      );
    return $persons_images;
  }
  /**
   * get_sliders( $persons_images_category_keywords )
   * For each category creates a slider with images of people who belong to it
   * and checks for the content image and quote for the associated keyword
   * @param array $persons_images_category_keywords
   * @return array
   */
  public static function get_sliders( $persons_images_category_keywords, $keyword_slug_quote_image, $gios_url ) {
    $carousel_sliders = array();
    $content_keywords = array_column( $keyword_slug_quote_image, 'keyword' );
    foreach ( $persons_images_category_keywords as $persons_images_category ) {
      $content_image    = '';
      $content_quote    = '';
      $current_keywords = array_column( $persons_images_category, 'keyword' );
      $current_keyword  = array_unique( $current_keywords )[0];
      if ( Kiosk_Helper::in_arrayi( $current_keyword, $content_keywords ) ) {
        $index = Kiosk_Helper::array_find( $current_keyword, $content_keywords );
        $content_image = $keyword_slug_quote_image[ $index ]['content_image'];
        $content_quote = $keyword_slug_quote_image[ $index ]['quote'];
      }
      $carousel_sliders[] = People_Slider_Helper::create_slider_layout(
          $persons_images_category,
          $content_image,
          $content_quote,
          $gios_url
      );
    }
    return $carousel_sliders;
  }
  /**
   * create_slider_layout( $images, $content_image, $quote = '' )
   * creates a slider template and fills it with atmost 15 images along with  featured
   * image from the content and quote.
   * @param array $images
   * @param string $content_image
   * @param string $quote
   * @return string
   */
  public static function create_slider_layout( $images, $content_image, $quote = '', $gios_url ) {
    $layout_template = <<<HTML
    <div class="kiosk-people-slider__layout">
      <h2 class='kiosk-people-slider__layout__header'>%s</h2>
      <div class="kiosk-people-slider__layout__row-top">
        %s
      </div>
      <div class="kiosk-people-slider__layout__row-middle">
        <div class="kiosk-people-slider__layout__row-middle--col1">
          %s
        </div>
        <div class="kiosk-people-slider__layout__row-middle--col2">
          %s
        </div>
        <div class="kiosk-people-slider__layout__row-middle--col3">
          %s
        </div>
      </div>
      <div class="kiosk-people-slider__layout__row-bottom">
        %s
      </div>
    </div>
HTML;
    $layout_center_with_quote = <<<HTML
    <div class="kiosk-people-slider__layout__featured--col1">
      %s
    </div>
    <div class="kiosk-people-slider__layout__featured--col2">
      %s
    </div>
HTML;
    $layout_center_no_quote = <<<HTML
    <div class ="kiosk-people-slider__layout__featured--col1">
      %s
    </div>
    <div class ="kiosk-people-slider__layout__featured--col2">
      %s
    </div>
    <div class ="kiosk-people-slider__layout__featured--col3">
      %s
    </div>
HTML;
    $image_tag_template = '<img class="%s" src="%s">';
    if ( empty( $content_image ) ) {
      $content_image = end( $images )['person_image'];
    }
    $count_images        = count( $images );
    $top_row             = '';
    $bottom_row          = '';
    $right_column        = '';
    $left_column         = '';
    $center_left_column  = '';
    $center_right_column = '';
    $title = array_column( $images, 'keyword' )[0];
    $images_to_display  = $count_images < 21 ? $count_images : 21;
    for ( $i = 0; $i < $images_to_display - 1; $i++ ) {
      $surrond_image = sprintf(
          $image_tag_template,
          'kiosk-people-slider__layout__image',
          Kiosk_Helper::relative_to_absolute_url( $images[ $i ]['person_image'], $gios_url )
      );
      if ( $i < 7 ) {
        $top_row .= $surrond_image;
      } else if ( $i < 10 ) {
        $right_column .= $surrond_image;
      } else if ( $i < 17 ) {
        $bottom_row .= $surrond_image;
      } else if ( $i < 21 ) {
        $left_column .= $surrond_image;
      } /*else if ( $i < 23 ) {
        $center_left_column .= $surrond_image;
      } else if ( $i < 27 ) {
        $center_right_column .= $surrond_image;
      }*/
    }
    $center_image = sprintf(
        $image_tag_template,
        'kiosk-people-slider__layout__featured-image',
        Kiosk_Helper::relative_to_absolute_url( $content_image, $gios_url )
    );
      /*if ( ! empty( $quote ) ) {*/
      $layout_center = sprintf(
          $layout_center_with_quote,
          $center_image,
          $quote
      );
      /*} else {
        $layout_center = sprintf(
            $layout_center_no_quote,
            $center_left_column,
            $center_image,
            $center_right_column
        );
      }*/
      $slider_layout = sprintf(
          $layout_template,
          $title,
          $top_row, $left_column,
          $layout_center,
          $right_column,
          $bottom_row
      );
    return $slider_layout;
  }
}