<?php
  /**
   * Carousel Slider helper
   *
   */
namespace Kiosk_WP;
class Carosuel_Slider_Helper {

  /**
   * Generates the bootstrap carousel div block
   * @param $prefix to prepend use as classes and id as part of carousel div
   * @param $layout_template markup of the content to display in carousel slider
   * @param $list_items array<array> data that needs to be substitute
   * in the layout
   *                          Example:
   *                          ```
   *                           $prefix='kiosk-asu-news';
   *                             $layout_template = <<<HTML
   *                                 <div
   *                                  class="kiosk-asu-news__slider__header">
   *                                  <a href="%s" title="%s"><h3><p>%s</p>
   *                                  </h3></a>
   *                               </div>
   *                           HTML;
   *                          $list_items = array( array( 'a', 'b', '1' ),
   *                           array('c', 'd', '2' ), array('e', 'f', '3' ) )
   *                          ```
   * @return string HTML markup
   */
  public static function generate_carousel_slider(
      $prefix,
      $layout_template,
      $list_items
  ) {
    $carousel_div_start = <<<HTML
      <div class="carousel slide %s__slider" data-ride="carousel" id="%s">
        <ol class="%s__slider__carousel-indicators carousel-indicators">
HTML;
    $carousel_li_item          = '<li %s data-slide-to="%d" data-target="#%s">
        </li>';
    $carousel_inner_div_start  = '<div class="carousel-inner '
        . $prefix
        . '__carousel-inner" role="listbox">';
    $carousel_ol_end           = '</ol>';
    $carousel_div_end          = '</div></div>';
    $carousel_list_items       = '';
    $carousel_div_items        = '';

    for ( $i = 0 ; $i < count( $list_items ); $i++ ) {
      // Set active for the 1st element of li
      if ( 0 === $i ) {
        $div_li_active       = ' class = "active" ';
        $div_item_active     = ' active ';
      } else {
        $div_li_active       = '';
        $div_item_active     = '';
      }
      $carousel_list_items  .= sprintf(
          $carousel_li_item,
          $div_li_active,
          $i,
          $prefix
      );
      $prepare_div_items     = vsprintf(
          $layout_template,
          $list_items[ $i ]
      );
      $carousel_div_items   .= sprintf(
          '<div class="item %s %s__slider__slide">'
          . $prepare_div_items
          . '</div>',
          $div_item_active ,
          $prefix
      );
    }

    $carousel_div_start = sprintf(
        $carousel_div_start,
        $prefix,
        $prefix,
        $prefix
    );
    $carousel_template  = $carousel_div_start
        . $carousel_list_items
        . $carousel_ol_end
        . $carousel_inner_div_start
        . $carousel_div_items
        . $carousel_div_end;
    return $carousel_template;
  }
}