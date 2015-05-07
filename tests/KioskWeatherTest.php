<?php

class KioskWeatherTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk weather
   * [kiosk-weather]
   */
  function test_kiosk_weather_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-weather' ) );

    // Test no atributes
    $content = do_shortcode( '[kiosk-weather]' );
    $this->assertContains( 'kiosk_weather_current', $content, 'Should return current weather block' );
    $this->assertContains( 'kiosk_weather_forecast', $content, 'Should return forecast weather block' );
  }
}