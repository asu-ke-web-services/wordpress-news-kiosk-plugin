<?php

class KioskTimeTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk time
   * [kiosk-time]
   */
  function test_kiosk_time_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-time' ) );

    // Test no tags
    $content = do_shortcode( '[kiosk-time]' );
    $this->assertContains(
        'kiosk-date-time__calendar',
        $content,
        'Should return calender'
    );
    $this->assertContains(
        'kiosk-date-time__time',
        $content,
        'Should return current time'
    );
  }
}