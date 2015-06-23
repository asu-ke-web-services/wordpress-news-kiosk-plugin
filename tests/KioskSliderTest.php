<?php

class KioskSliderTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk slider can be called with limit attribute
   * [kiosk-slider limit ='20']
   */
  function test_kiosk_slider_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-slider' ) );
    // Test with mockup data
    // $stub = $this->getMock( 'Kiosk_WP\Kiosk_Slider_Shortcodes', array( 'kiosk_slider_fetch_feed' ) );
    // $stub->expects( $this->any() )
    //      ->method( 'kiosk_slider_fetch_feed' )
    //      ->will( $this->returnValue( $this->return_unit_test_data() ) );

    $feed_stub = $this->getMock( 'Kiosk_WP\Feed_Helper', array( 'simple_pie_feed_fetch' ) );
    $feed_stub->expects( $this->any() )
         ->method( 'simple_pie_feed_fetch' )
         ->with( $this->stringContains( 'flickr' ) )
         ->will( $this->returnValue( $this->return_unit_test_data() ) );
    $stub = $this->getMockBuilder( 'Kiosk_WP\Kiosk_Slider_Shortcodes' )
          ->setConstructorArgs( array( $feed_stub ) )
          ->setMethods( null )
          ->getMock();

    // Test no tags by default limit 20 image items if found
    $content = $stub->kiosk_slider( '' );
    $this->assertContains( 'kiosk-gallery__slider__slide', $content, 'Should return images with div tag' );

    // Test limit attribute
    $content = $stub->kiosk_slider( array( 'limit' => 20 ) );
    $this->assertContains( 'kiosk-gallery__slider__slide', $content, 'Should return images with div tag' );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertLessThanOrEqual( 20, $numberOfEvents, 'There should be <= 20 image items' );

    // Test limit attribute
    $content = $stub->kiosk_slider( array( 'limit' => 5 ) );
    $this->assertContains( 'kiosk-gallery__slider__slide', $content, 'Should return images with div tag' );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertLessThanOrEqual( 5, $numberOfEvents, 'There should be <= 5 image items' );
  }
  /**
    * return_unit_test_data() creates a mock up data to be used as feed data
    * @return SimplePie
    */
  function return_unit_test_data(){
    $sample_json = <<<XML
<?xml version="1.0" encoding="utf-8"?><rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:creativeCommons="http://cyber.law.harvard.edu/rss/creativeCommonsRssModule.html" xmlns:flickr="urn:flickr:user" ><channel><title>Uploads from Global Institute of Sustainability</title><link>https://www.flickr.com/photos/asu-gios/</link><description></description><pubDate>Thu, 09 Oct 2014 11:51:16 -0700</pubDate><lastBuildDate>Thu, 09 Oct 2014 11:51:16 -0700</lastBuildDate><generator>https://www.flickr.com/</generator><image><url>https://farm2.staticflickr.com/1426/buddyicons/55424394@N03.jpg?1289424405#55424394@N03</url><title>Uploads from Global Institute of Sustainability</title><link>https://www.flickr.com/photos/asu-gios/</link></image><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15489018322/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15489018322/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3927/15489018322_8fc8e5fb5b_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:16 -0700</pubDate><dc:date.Taken>2014-08-19T09:00:41-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15489018322</guid><media:content url="https://farm4.staticflickr.com/3927/15489018322_8fc8e5fb5b_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3927/15489018322_8fc8e5fb5b_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15486230201/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15486230201/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3941/15486230201_afe8ce1354_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:16 -0700</pubDate><dc:date.Taken>2014-08-19T13:01:17-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15486230201</guid><media:content url="https://farm4.staticflickr.com/3941/15486230201_afe8ce1354_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3941/15486230201_afe8ce1354_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15486230221/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15486230221/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3953/15486230221_757eb6a436_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:15 -0700</pubDate><dc:date.Taken>2014-08-19T13:00:52-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15486230221</guid><media:content url="https://farm4.staticflickr.com/3953/15486230221_757eb6a436_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3953/15486230221_757eb6a436_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15302729298/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15302729298/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3936/15302729298_6ffa50c586_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:15 -0700</pubDate><dc:date.Taken>2014-08-19T12:02:13-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15302729298</guid><media:content url="https://farm4.staticflickr.com/3936/15302729298_6ffa50c586_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3936/15302729298_6ffa50c586_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15489375665/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15489375665/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3949/15489375665_f85cf8237e_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:14 -0700</pubDate><dc:date.Taken>2014-08-19T11:19:32-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15489375665</guid><media:content url="https://farm4.staticflickr.com/3949/15489375665_f85cf8237e_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3949/15489375665_f85cf8237e_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15489375935/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15489375935/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3939/15489375935_114d9fcd3f_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:13 -0700</pubDate><dc:date.Taken>2014-08-19T11:04:12-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15489375935</guid><media:content url="https://farm4.staticflickr.com/3939/15489375935_114d9fcd3f_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3939/15489375935_114d9fcd3f_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15486230411/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15486230411/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3927/15486230411_4f154ac632_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:13 -0700</pubDate><dc:date.Taken>2014-08-19T09:52:20-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15486230411</guid><media:content url="https://farm4.staticflickr.com/3927/15486230411_4f154ac632_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3927/15486230411_4f154ac632_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15466250916/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15466250916/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3937/15466250916_690f89e440_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:13 -0700</pubDate><dc:date.Taken>2014-08-19T09:51:54-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15466250916</guid><media:content url="https://farm4.staticflickr.com/3937/15466250916_690f89e440_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3937/15466250916_690f89e440_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15489018732/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15489018732/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm3.staticflickr.com/2945/15489018732_91696e4348_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:12 -0700</pubDate><dc:date.Taken>2014-08-19T09:48:52-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15489018732</guid><media:content url="https://farm3.staticflickr.com/2945/15489018732_91696e4348_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm3.staticflickr.com/2945/15489018732_91696e4348_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15302797587/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15302797587/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3931/15302797587_33258ec616_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:12 -0700</pubDate><dc:date.Taken>2014-08-19T09:46:20-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15302797587</guid><media:content url="https://farm4.staticflickr.com/3931/15302797587_33258ec616_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3931/15302797587_33258ec616_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15489018792/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15489018792/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm6.staticflickr.com/5598/15489018792_eeb74f746a_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:11 -0700</pubDate><dc:date.Taken>2014-08-19T09:42:32-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15489018792</guid><media:content url="https://farm6.staticflickr.com/5598/15489018792_eeb74f746a_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm6.staticflickr.com/5598/15489018792_eeb74f746a_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15302474879/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15302474879/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm3.staticflickr.com/2950/15302474879_e188b8a8cd_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:11 -0700</pubDate><dc:date.Taken>2014-08-19T09:33:54-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15302474879</guid><media:content url="https://farm3.staticflickr.com/2950/15302474879_e188b8a8cd_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm3.staticflickr.com/2950/15302474879_e188b8a8cd_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15466250996/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15466250996/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm3.staticflickr.com/2950/15466250996_d3c5f33f38_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:10 -0700</pubDate><dc:date.Taken>2014-08-19T09:30:41-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15466250996</guid><media:content url="https://farm3.staticflickr.com/2950/15466250996_d3c5f33f38_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm3.staticflickr.com/2950/15466250996_d3c5f33f38_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15466251136/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15466251136/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm3.staticflickr.com/2947/15466251136_936ed01443_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:09 -0700</pubDate><dc:date.Taken>2014-08-19T09:27:47-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15466251136</guid><media:content url="https://farm3.staticflickr.com/2947/15466251136_936ed01443_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm3.staticflickr.com/2947/15466251136_936ed01443_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15489376285/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15489376285/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3949/15489376285_9a2ac07b63_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:08 -0700</pubDate><dc:date.Taken>2014-08-19T09:26:57-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15489376285</guid><media:content url="https://farm4.staticflickr.com/3949/15489376285_9a2ac07b63_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3949/15489376285_9a2ac07b63_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15302474909/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15302474909/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3935/15302474909_066394b177_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:08 -0700</pubDate><dc:date.Taken>2014-08-19T09:25:27-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15302474909</guid><media:content url="https://farm4.staticflickr.com/3935/15302474909_066394b177_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3935/15302474909_066394b177_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15489376065/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15489376065/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3951/15489376065_62657140c8_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:07 -0700</pubDate><dc:date.Taken>2014-08-19T09:25:21-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15489376065</guid><media:content url="https://farm4.staticflickr.com/3951/15489376065_62657140c8_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3951/15489376065_62657140c8_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15302662360/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15302662360/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3956/15302662360_6f73e4e0e5_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:07 -0700</pubDate><dc:date.Taken>2014-08-19T09:14:41-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15302662360</guid><media:content url="https://farm4.staticflickr.com/3956/15302662360_6f73e4e0e5_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3956/15302662360_6f73e4e0e5_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>Fall Welcome 2014</title><link>https://www.flickr.com/photos/asu-gios/15302797247/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/15302797247/&quot; title=&quot;Fall Welcome 2014&quot;&gt;&lt;img src=&quot;https://farm4.staticflickr.com/3944/15302797247_8f920edabb_m.jpg&quot; width=&quot;240&quot; height=&quot;180&quot; alt=&quot;Fall Welcome 2014&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Thu, 09 Oct 2014 11:51:06 -0700</pubDate><dc:date.Taken>2014-08-19T12:58:34-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/15302797247</guid><media:content url="https://farm4.staticflickr.com/3944/15302797247_8f920edabb_b.jpg" 
                   type="image/jpeg"
                   height="768"
                   width="1024"/><media:title>Fall Welcome 2014</media:title><media:thumbnail url="https://farm4.staticflickr.com/3944/15302797247_8f920edabb_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item><item><title>2014-Spring-convocation-5</title><link>https://www.flickr.com/photos/asu-gios/14200630645/</link><description>     &lt;p&gt;&lt;a href=&quot;https://www.flickr.com/people/asu-gios/&quot;&gt;Global Institute of Sustainability&lt;/a&gt; posted a photo:&lt;/p&gt;
  
&lt;p&gt;&lt;a href=&quot;https://www.flickr.com/photos/asu-gios/14200630645/&quot; title=&quot;2014-Spring-convocation-5&quot;&gt;&lt;img src=&quot;https://farm3.staticflickr.com/2917/14200630645_d74e28902e_m.jpg&quot; width=&quot;240&quot; height=&quot;160&quot; alt=&quot;2014-Spring-convocation-5&quot; /&gt;&lt;/a&gt;&lt;/p&gt;

</description><pubDate>Fri, 16 May 2014 13:02:59 -0700</pubDate><dc:date.Taken>2014-05-12T21:41:15-08:00</dc:date.Taken><author flickr:profile="https://www.flickr.com/people/asu-gios/">nobody@flickr.com (Global Institute of Sustainability)</author><guid isPermaLink="false">tag:flickr.com,2004:/photo/14200630645</guid><media:content url="https://farm3.staticflickr.com/2917/14200630645_d74e28902e_b.jpg" 
                   type="image/jpeg"
                   height="683"
                   width="1024"/><media:title>2014-Spring-convocation-5</media:title><media:thumbnail url="https://farm3.staticflickr.com/2917/14200630645_d74e28902e_s.jpg" height="75" width="75" /><media:credit role="photographer">Global Institute of Sustainability</media:credit></item></channel></rss>
XML;
    $feed = new SimplePie();
    $feed->set_raw_data( $sample_json );
    $feed->init();
    $feed->handle_content_type();
    return $feed;
  }
}