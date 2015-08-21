/*! kisok v1.0
 */
+function ($) {
  'use strict';

  /**
   * set carousel effect to 10 seconds
   */
  $('.carousel').carousel({
    interval: 10000
  });

  /**
   * reloads page every night between 12 a.m to 1 a.m
   */
  setTimeout( function () {
    var hour = new Date().getHours();
    if ( 0 <= hour && hour < 1 ) {
      location.reload();
    }
  }, 1 * 60 * 60 * 1000 /* 1 hour */ );

  /**
   * Hide page scroll bars when current page template is set to Kiosk
   *
   */
  if ($('.kiosk-page-template').length) {
    $('html').css({
      'overflow-y': 'hidden',
      'overflow-x': 'hidden'
    });
  }

}(jQuery)
