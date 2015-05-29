<?php

class KioskTitleTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk title
   * [kiosk-title]
   */
  function test_kiosk_title_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-title' ) );

    $content = do_shortcode( '[kiosk-title]' );
    $this->assertContains( 'kiosk-title__logo', $content, 'Should return title image' );
  }
}