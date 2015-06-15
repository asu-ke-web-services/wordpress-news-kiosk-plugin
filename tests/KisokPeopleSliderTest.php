<?php

class KioskPeopleSliderTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk people slider can be called with limit attribute
   * [kiosk-people-slider gios_url ='url']
   */
  function test_kiosk_people_slider_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-people-slider' ) );

    $people_slider_helper_stub = $this->getMock(
        'Kiosk_WP\People_Slider_Helper',
        array(
            'get_keywords',
            'get_people',
        )
    );

    $people_slider_helper_stub->expects( $this->any() )
                              ->method( 'get_keywords' )
                              ->will(
                                  $this->returnValue(
                                      $this->return_unit_test_data(
                                          'get_keywords'
                                      )
                                  )
                              );

    $people_slider_helper_stub->expects( $this->any() )
                              ->method( 'get_people' )
                              ->will(
                                  $this->returnValue(
                                      $this->return_unit_test_data( 'get_people' )
                                  )
                              );

    $stub = $this->getMockBuilder( 'Kiosk_WP\Kiosk_People_Slider_Shortcodes' )
          ->setConstructorArgs( array( $people_slider_helper_stub ) )
          ->setMethods( null )
          ->getMock();

    $content = $stub->kiosk_people_slider( '' );
    $this->assertContains( 'kiosk-people-slider__layout', $content, 'Should return carousel slider' );
    $content = $stub->kiosk_people_slider( '' );
    $this->assertContains( 'kiosk-people-slider__layout', $content, 'Should return carousel slider' );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertEquals( 4, $numberOfEvents, 'There should 4 slider items' );

    $test_with_content = <<<HTML
    [
  {
    "keyword":"Biodiversity and preservation of natural environment",
    "person-slug":"jeffrey-englin",
    "quote":"Live for today Hope... for tomorrow&copy;"
   },
 {
    "keyword":"Energy",
    "person-slug":"jeffrey-englin"

   },
 {
    "keyword":"food systems",

    "quote":"Live for today Hope... for tomorrow&copy;"
   }

]
HTML;
    $content = $stub->kiosk_people_slider( array(), $test_with_content );
    $this->assertContains( 'kiosk-people-slider__layout', $content, 'Should return carousel slider' );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertEquals( 4, $numberOfEvents, 'There should 4 slider items' );
    $this->assertContains( 'gdawson', $content, 'Should return content person-slug image' );
     $this->assertContains( 'Live for today Hope... for tomorrow', $content, 'Should return content quote' );

  }
  /**
    * return_unit_test_data() creates a mock up data
    * @return mixed
    */
  function return_unit_test_data( $method_name ){
    $person                 = new \Gios_Api\Person();
    $keyword                = new \Gios_Api\Keyword();
    $person->slug           = 'gregory-dawson';
    $person->person_id      = '15998';
    $person->person_values = array( 'photo_path' => '/people/gdawson.jpg' );
    $keyword->keyword       = 'Biodiversity and preservation of natural environment';
    if ( 'get_keywords' == $method_name ) {
      return array( $keyword, $keyword, $keyword, $keyword );
    } else if ( 'get_people' == $method_name ) {
      return array( $person, $person, $person, $person );
    }
    return null;
  }
}