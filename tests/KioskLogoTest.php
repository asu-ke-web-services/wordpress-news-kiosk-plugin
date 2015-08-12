<?php
/**
 * @group logo
 */
class KioskTitleTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   *
   */
  function test_kiosk_logo_shortcode_exists() {
    $this->assertTrue( shortcode_exists( 'kiosk-logo' ) );
  }
  /**
   * To Test Kiosk logo
   * [kiosk-logo]
   */
  function test_kiosk_logo_shortcode() {
    $content = do_shortcode( '[kiosk-logo]' );
    $this->assertContains(
        'kiosk-logo__logo',
        $content,
        'Should return logo image'
    );
    $this->assertContains(
        'wp-content/plugins/wordpress-news-kiosk-plugin/assets/images/sos-logo.png',
        $content,
        'Should contain default image path'
    );
  }
  /**
   * To Test Kiosk logo with path attribute
   */
  function test_kiosk_logo_shortcode_with_attribute() {
    $content = do_shortcode( '[kiosk-logo path="https://example.com/JAW-GIOS_RGB.png"]' );
    $this->assertContains(
        'kiosk-logo__logo',
        $content,
        'Should return logo image'
    );
    $this->assertContains(
        '<img src="https://example.com/JAW-GIOS_RGB.png"
       class="kiosk-logo__image">',
        $content,
        'Should contain image src as path'
    );
  }
}