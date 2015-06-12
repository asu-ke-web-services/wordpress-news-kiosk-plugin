<?php
  /**
   * Kiosk helper
   *
   */
namespace Kiosk_WP;
class People_Slider_Helper {

  public function get_keywords() {
    $keyword_presenter                = new \Keywords_Presenter();
    return $keyword_presenter->get_expertise_category_keywords();
  }

  public function get_people( $keyword ) {
    $people_presenter                 = new \People_Presenter();
    return $people_presenter
                     ->get_sustainability_scientists_by_expertise_keyword(
                         slugify( $keyword->keyword )
                     );
  }

  /**
   * get_sliders( $persons_images_category_keywords )
   * For each category creates a slider with images of people who belong to it
   * and checks for the content image and quote for the associated keyword
   * @param array $persons_images_category_keywords an indexed array of associative arrays, where entries are "person_image" and "keyword". 
   * @param array $keyword_slug_quote_image an indexed array of associative arrays, where entries are "keyword", "person-slug", "quote", "image"
   * @param String $gios_url
   * @return array
   */
  public static function get_sliders( $data_sections, $gios_url ) {
    $carousel_siders = array();

    foreach ( $data_sections as $section ) {
      $carousel_sliders[] = self::create_slider_layout(
          $section,
          $gios_url
      );
    }

    return $carousel_sliders;
  }

  /**
   * create_slider_layout( $images, $content_image, $quote = '' )
   * creates a slider template and fills it with atmost 15 images along with  featured
   * image from the content and quote.
   * @param array $section named array with keyword, people-images, quote, person-slug and featured-image
   * @return string
   */
  public static function create_slider_layout( $section, $gios_url ) {
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
    if ( $section['featured-image'] === false ) {
      $content_image = end( $section['people-images'] );
    } else {
      $content_image = $section['featured-image'];
    }

    $count_images        = count( $section['people-images'] );
    $top_row             = '';
    $bottom_row          = '';
    $right_column        = '';
    $left_column         = '';
    $center_left_column  = '';
    $center_right_column = '';
    $title               = $section['keyword'];
    $quote               = $section['quote'];
    $images_to_display   = $count_images < 21 ? $count_images : 21;
    for ( $i = 0; $i < $images_to_display - 1; $i++ ) {
      $surrond_image = sprintf(
          $image_tag_template,
          'kiosk-people-slider__layout__image',
          Kiosk_Helper::relative_to_absolute_url( $section['people-images'][$i], $gios_url )
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