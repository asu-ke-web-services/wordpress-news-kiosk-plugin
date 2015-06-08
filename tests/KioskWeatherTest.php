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
    //$content = do_shortcode( '[kiosk-weather]' );
    // Test with mockup data
    $stub = $this->getMock( 'Kiosk_WP\Kiosk_Weather_Helper', array( 'get_weather_json' ) );

    $stub->expects( $this->any() )
         ->method( 'get_weather_json' )
		  ->with( $this->equalTo( 'tempe, az' ) )
         ->will( $this->returnValue( $this->return_unit_test_data() ) );

    $content = $stub->kiosk_weather( '' );
    $this->assertContains( 'kiosk-weather__current ', $content, 'Should return current weather block' );
    $this->assertContains( 'kiosk-weather__forecast', $content, 'Should return forecast weather block' );
    $this->assertContains( 'kiosk-weather__forecast__location', $content, 'Should return location block' );
  }
  /**
  * return_unit_test_data() creates a mock up data to be used as yahoo weather response json
  * @return string
  */
  function return_unit_test_data(){
    $sample_json = <<<JSON
{"query":{"count":1,"created":"2015-05-21T19:36:41Z","lang":"en-US","results":{"channel":{"title":"Yahoo! Weather - Tempe, AZ","link":"http://us.rd.yahoo.com/dailynews/rss/weather/Tempe__AZ/*http://weather.yahoo.com/forecast/USAZ0233_f.html","description":"Yahoo! Weather for Tempe, AZ","language":"en-us","lastBuildDate":"Thu, 21 May 2015 11:51 am MST","ttl":"60","location":{"city":"Tempe","country":"United States","region":"AZ"},"units":{"distance":"mi","pressure":"in","speed":"mph","temperature":"F"},"wind":{"chill":"85","direction":"150","speed":"14"},"atmosphere":{"humidity":"13","pressure":"29.81","rising":"2","visibility":"10"},"astronomy":{"sunrise":"5:23 am","sunset":"7:23 pm"},"image":{"title":"Yahoo! Weather","width":"142","height":"18","link":"http://weather.yahoo.com","url":"http://l.yimg.com/a/i/brand/purplelogo//uh/us/news-wea.gif"},"item":{"title":"Conditions for Tempe, AZ at 11:51 am MST","lat":"33.43","long":"-111.94","link":"http://us.rd.yahoo.com/dailynews/rss/weather/Tempe__AZ/*http://weather.yahoo.com/forecast/USAZ0233_f.html","pubDate":"Thu, 21 May 2015 11:51 am MST","condition":{"code":"28","date":"Thu, 21 May 2015 11:51 am MST","temp":"85","text":"Mostly Cloudy"},"description":"\n<img src=\"http://l.yimg.com/a/i/us/we/52/28.gif\"/><br />\n<b>Current Conditions:</b><br />\nMostly Cloudy, 85 F<BR />\n<BR /><b>Forecast:</b><BR />\nThu - Partly Cloudy. High: 89 Low: 65<br />\nFri - Mostly Sunny. High: 84 Low: 62<br />\nSat - Sunny. High: 84 Low: 65<br />\nSun - Sunny. High: 88 Low: 68<br />\nMon - Sunny. High: 92 Low: 69<br />\n<br />\n<a href=\"http://us.rd.yahoo.com/dailynews/rss/weather/Tempe__AZ/*http://weather.yahoo.com/forecast/USAZ0233_f.html\">Full Forecast at Yahoo! Weather</a><BR/><BR/>\n(provided by <a href=\"http://www.weather.com\" >The Weather Channel</a>)<br/>\n","forecast":[{"code":"30","date":"21 May 2015","day":"Thu","high":"89","low":"65","text":"Partly Cloudy"},{"code":"34","date":"22 May 2015","day":"Fri","high":"84","low":"62","text":"Mostly Sunny"},{"code":"32","date":"23 May 2015","day":"Sat","high":"84","low":"65","text":"Sunny"},{"code":"32","date":"24 May 2015","day":"Sun","high":"88","low":"68","text":"Sunny"},{"code":"32","date":"25 May 2015","day":"Mon","high":"92","low":"69","text":"Sunny"}],"guid":{"isPermaLink":"false","content":"USAZ0233_2015_05_25_7_00_MST"}}}}}}
JSON;
    return $sample_json;
  }
}