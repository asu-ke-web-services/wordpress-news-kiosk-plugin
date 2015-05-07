<?php

class KioskTitleTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk title can be called with limit attribute
   * [kiosk-title limit ='20']
   */
  function test_kiosk_title_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-title' ) );

    // Test no tags by default limit 20 image items if found
    $content = do_shortcode( '[kiosk-title]' );
    $this->assertContains( 'kiosk_asu_school_sustaintabilty_logo', $content, 'Should return title image' );
  }
}