<?php

class KioskSliderTest extends WP_UnitTestCase {
  private $feed_stub  = null;
  private $stub       = null;
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  function setUp() {
    // Mockup data
    $this->feed_stub = $this->getMock(
        'Kiosk_WP\Feed_Helper',
        array( 'fetch_feed' )
    );
    $this->feed_stub->expects( $this->any() )
         ->method( 'fetch_feed' )
         ->with( $this->stringContains( 'flickr' ) )
         ->will( $this->returnValue( $this->return_unit_test_data() ) );
    $this->stub = $this->getMockBuilder( 'Kiosk_WP\Kiosk_Slider_Shortcodes' )
          ->setConstructorArgs( array( $this->feed_stub ) )
          ->setMethods( null )
          ->getMock();
  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk slider can be called with limit attribute
   * [kiosk-slider limit ='20']
   */
  // Test no tags by default limit 20 image items if found
  function test_kiosk_slider_shortcode_default() {
    $this->assertTrue( shortcode_exists( 'kiosk-slider' ) );

    $content = $this->stub->kiosk_slider( '' );
    $this->assertContains(
        'kiosk-gallery__slider__slide',
        $content,
        'Should return images with div tag'
    );
  }
  // Test limit attribute
  function test_kiosk_slider_shortcode_limit_20() {

    $content = $this->stub->kiosk_slider( array( 'limit' => 20 ) );
    $this->assertContains(
        'kiosk-gallery__slider__slide',
        $content,
        'Should return images with div tag'
    );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertLessThanOrEqual(
        20,
        $numberOfEvents,
        'There should be <= 20 image items'
    );
  }
  // Test limit attribute
  function test_kiosk_slider_shortcode_limit_5() {

    // Test limit attribute
    $content = $this->stub->kiosk_slider( array( 'limit' => 5 ) );
    $this->assertContains(
        'kiosk-gallery__slider__slide',
        $content,
        'Should return images with div tag'
    );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertLessThanOrEqual(
        5,
        $numberOfEvents,
        'There should be <= 5 image items'
    );
  }

  /**
    * Creates a mock up data to be used as feed data
    * @return SimplePie
    */
  function return_unit_test_data() {
    $sample_json = <<<XML
<?xml version="1.0" encoding="utf-8"?><rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:creativeCommons="http://cyber.law.harvard.edu/rss/creativeCommonsRssModule.html" xmlns:flickr="urn:flickr:user" ><channel><title>Uploads from Global Institute of Sustainability</title><link>https://www.flickr.com/photos/asu-gios/</link><description></description><pubDate>Thu, 09 Oct 2014 11:51:16 -0700</pubDate><lastBuildDate>Thu, 09 Oct 2014 11:51:16 -0700</lastBuildDate><generator>https://www.flickr.com/</generator><image><url>https://farm2.staticflickr.com/1426/buddyicons/55424394@N03.jpg?1289424405#55424394@N03</url><title>Uploads from Global Institute of Sustainability</title><link>https://www.flickr.com/photos/asu-gios/</link></image><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15489018322/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15489018322/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3927/15489018322_8fc8e5fb5b_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:16 -0700</pubDate><dc:date.Taken>2014-08-19T09:00:41-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15489018322</guid><media:content url="https://farm4.staticflickr.com/3927/15489018322_8fc8e5fb5b_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3927/15489018322_8fc8e5fb5b_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item></channel></rss>

XML;
    $feed = new SimplePie();
    $feed->set_raw_data( $sample_json );
    $feed->init();
    $feed->handle_content_type();
    return $feed;
  }
}