<?php
  /**
   * Kiosk helper
   *
   */
namespace Kiosk_WP;
class People_Slider_Helper {

  /**
   * Gets the list of keywords from GIOS_API
   * @return array
   */
  public function get_keywords() {
    $keyword_presenter                = new \Keywords_Presenter();
    return $keyword_presenter->get_expertise_category_keywords();
  }

  /**
   * Gets the list of people for a keyword from GIOS_API
   * @param string $keyword
   * @return array
   */
  public function get_people( $keyword ) {
    $people_presenter                 = new \People_Presenter();

    return $people_presenter
                     ->get_sustainability_scientists_by_expertise_keyword(
                         $keyword->keyword
                     );
  }

  /**
   * Relates the content data of people slider shortcode with data from GIOS_API
   * for keywords and persons.
   * @param array $keywords
   * @param array $parsed_content
   * @return array<keyword, people-images, quote, person-slug, featured-image>
   */
  public function get_sliders_data( $keywords, $parsed_content ) {
    $data_sections          = array();
    foreach ( $keywords as $keyword ) {
      $featured_image = false;
      // Get all the images for the people in those keywords
      $people = $this->get_people( $keyword );
      $images = array();
      foreach ( $people as $person ) {
        $image = $person->photo_url();

        if ( false !== $image ) {
          $images[] = $image;
        }
      }

      // Match the quote to the keyword
      $quote          = false;
      $person_slug    = false;
      for ( $i = 0; $i < count( $parsed_content ); $i++ ) {
        $part = $parsed_content[ $i ];
        if ( ( array_key_exists( 'keyword', $part ) &&
               0 == strcasecmp( $part['keyword'], $keyword->keyword ) )
             || ( array_key_exists( 'keyword-slug', $part ) &&
                  0 == strcasecmp( $part['keyword-slug'], $keyword->slug ) ) ) {
          $quote       = array_key_exists( 'quote', $part )
              ? $part['quote']
              : false;
          $person_slug = array_key_exists( 'person-slug', $part )
              ? $part['person-slug']
              : false;

          foreach ( $people as $person ) {
            $image = $person->photo_url();

            if ( false !== $image && $person->slug === $person_slug ) {
              $featured_image = $image;
            }
          }

          break;
        }
      }

      $data_sections[] = array(
        'keyword' => $keyword->keyword,
        'people-images' => $images,
        'quote' => $quote,
        'person-slug' => $person_slug,
        'featured-image' => $featured_image,
      );
    }
    return $data_sections;
  }

  /**
   * For each category creates a slider with images of people who belong to it
   * and checks for the content image and quote for the associated keyword
   * @param array $data_sections<keyword, people-images, quote, person-slug,
   * featured-image>
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
   * Creates a slider template and fills it with atmost 21 images along
   * with featured image from the content and quote.
   * @param array $section<keyword, people-images, quote, person-slug,
   * featured-image>
   * @return string
   */
  public static function create_slider_layout( $section, $gios_url ) {
    $layout_template = <<<HTML
	<div class='kiosk-people-slider__layout__header'>%s</div>
  <div class="kiosk-people-slider__layout__body">
  <div class="kiosk-people-slider__aspect-ratio">
    <img class="kiosk-people-slider__aspect-ratio__dummy" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
    <div class="kiosk-people-slider__layout">
      <div class="kiosk-people-slider__layout__row-top">
        %s
      </div>
      <div class="kiosk-people-slider__layout__row-middle">
        <div class="kiosk-people-slider__layout__row-middle--column1">
          %s
        </div>
        <div class="kiosk-people-slider__layout__row-middle--column2">
          %s
        </div>
        <div class="kiosk-people-slider__layout__row-middle--column3">
          %s
        </div>
      </div>
      <div class="kiosk-people-slider__layout__row-bottom">
        %s
      </div>
    </div>
  </div>
  </div>
HTML;
    $layout_center_with_quote = <<<HTML
    <div class="kiosk-people-slider__layout__featured--column1">
      %s
    </div>
    <div class="kiosk-people-slider__layout__featured--column2">
      %s
    </div>
HTML;
    $layout_center_no_quote = <<<HTML
    <div class ="kiosk-people-slider__layout__featured--column1">
      %s
    </div>
    <div class ="kiosk-people-slider__layout__featured--column2">
      %s
    </div>
    <div class ="kiosk-people-slider__layout__featured--column3">
      %s
    </div>
HTML;
    $image_tag_template = '<img class="%s" src="%s">';
    if ( false === $section['featured-image'] ) {
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
          Kiosk_Helper::relative_to_absolute_url(
              $section['people-images'][ $i ],
              $gios_url
          )
      );
      if ( $i < 7 ) {
        $top_row .= $surrond_image;
      } else if ( $i < 10 ) {
        $right_column .= $surrond_image;
      } else if ( $i < 17 ) {
        $bottom_row .= $surrond_image;
      } else if ( $i < 21 ) {
        $left_column .= $surrond_image;
      }
    }
    if ( ! empty( $content_image ) ) {
      $center_image = sprintf(
          $image_tag_template,
          'kiosk-people-slider__layout__featured-image',
          Kiosk_Helper::relative_to_absolute_url( $content_image, $gios_url )
      );
    } else {
      $center_image = '';
    }
    $layout_center = sprintf(
        $layout_center_with_quote,
        $center_image,
        $quote
    );
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