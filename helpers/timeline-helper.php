<?php
  /**
   * TimeLine helper
   *
   */
namespace Kiosk_WP;
class TimeLine_Helper {

  public function __construct() {
  }

  /**
   * Sorts the start dates and gets least date value of all(start year)
   * Sorts the end dates and gets highest data value of all(end year)
   * Creates a header with all years between start and end year from above
   * For each items creates a row with left margin, width, label, status
   * Returns the HTML markup
   * @param array $data <start-date, end-date, label, status> in each row
   * @return string
   */
  public static function create_timeline( $data ) {
    if ( empty( $data ) ) {
      return '';
    }
    $start_dates     = array_column( $data,'start-date' );
    $end_dates       = array_column( $data, 'end-date' );
    // example 2015
    $start_year      = date( 'Y', self::get_least_date( $start_dates ) );
    // example 2016
    $end_year        = date( 'Y', self::get_highest_date( $end_dates ) );
    $timeline_header = self::create_timeline_header_years(
        $start_year,
        $end_year
    );
    $rows            = '';
    $timeline_start  = strtotime( 'Jan 1, ' . $start_year );
    $timeline_end    = strtotime( 'Dec 31, ' . $end_year );
    foreach ( $data as $row ) {
      $post_start_time = strtotime( $row['start-date'] );
      $post_end_time   = strtotime( $row['end-date'] );
      $post_label      = $row['label'];
      $post_status     = $row['status'];
      $margin_left     = self::get_row_margin_percentage(
          $timeline_start,
          $post_start_time,
          $timeline_start,
          $timeline_end
      );
      $row_width       = self::get_row_margin_percentage(
          $post_start_time,
          $post_end_time,
          $timeline_start,
          $timeline_end
      );
      $rows           .= self::create_row(
          $margin_left,
          $row_width, $post_label,
          $post_start_time,
          $post_end_time,
          $post_status
      );
    }
    return '<div class="timeline-block">' . $timeline_header . $rows . '</div>';
  }

  /**
   * Gets the least date value among the array
   * @param array $dates
   * @return int index or array of indexes if duplicates found
   */
  public static function get_least_date( $dates ) {
    $dates    = array_map( 'strtotime', $dates );
    $min_index  = array_keys( $dates, min( $dates ) );
    return $dates[ $min_index[0] ];
  }

  /**
   * Gets the highest date value among the array
   * @param array $dates
   * @return int index or array of indexes if duplicates found
   */
  public static function get_highest_date( $dates ) {
    $dates    = array_map( 'strtotime',$dates );
    $max_index  = array_keys( $dates, max( $dates ) );
    return $dates[ $max_index[0] ];
  }
  /**
   * create_timeline_header_years( $start, $end )
   * creates a div for timeline header from start to end values
   * @param int $start
   * @param int $end
   * @return string
   */
  public static function create_timeline_header_years( $start, $end ) {
    $html = '';
    for ( $i = $start; $i <= $end; $i++ ) {
      $html .= '<div id="header_section_'
          . $i
          . '" class="timeline_header_section">'
          . $i
          .'</div>';
    }
    $html = '<div class="timeline_header">' . $html . '</div>';
    return $html;
  }
  /**
   * get_time_difference( $start, $end )
   * @param string $start
   * @param string $end
   * @return int
   */
  public static function get_time_difference( $start, $end ) {
    return strtotime( $end ) - strtotime( $start );
  }
  /**
   * get_row_margin_percentage()
   * @param int $row_start
   * @param int $row_end
   * @param int $header_start
   * @param int $header_end
   */
  public static function get_row_margin_percentage(
      $row_start,
      $row_end,
      $header_start,
      $header_end
  ) {
    $header_width         = $header_end - $header_start;
    $row_width            = $row_end - $row_start;
    $row_width_percentage = $row_width * 100.0 / $header_width;
    return $row_width_percentage . '%';
  }

  public static function create_row(
      $margin_left,
      $row_width,
      $post_label,
      $post_start_time,
      $post_end_time,
      $post_status
  ) {
    $html = <<<HTML
    <div class="timeline-row-item">
      <div class="timeline-row-item__margin" style="margin-left:%s;width:%s;">
      &nbsp;</div>
      <div class="timeline-row-item__duration">%s to %s</div>
      <div class="timeline-row-item__status">%s</div>
      <div class="timeline-row-item__text">%s</div>
    </div>
HTML;
    return sprintf(
        $html,
        $margin_left,
        $row_width,
        date( 'm/d/y', $post_start_time ),
        date( 'm/d/y', $post_end_time ),
        $post_status,
        $post_label
    );
  }
}