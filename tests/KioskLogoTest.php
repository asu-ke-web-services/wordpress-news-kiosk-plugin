<?php

class KioskTitleTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk logo
   * [kiosk-logo]
   */
  function test_kiosk_logo_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-logo' ) );

    $content = do_shortcode( '[kiosk-logo]' );
    $this->assertContains(
        'kiosk-logo__logo',
        $content,
        'Should return logo image'
    );
  }
}