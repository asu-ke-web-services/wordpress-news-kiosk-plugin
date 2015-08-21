/*kisok-weather
 */
+function ($) {
  'use strict';
  /**
   * Invokes ajax call to server every 5 minutes and
   * replaces weather block
   * Updates weather widget only when servers returns valid data
   */
  var siteUrl          = $(location).attr('href');
  var $weatherLocation = $.trim($('.kiosk-weather__forecast__title').text());
  var weatherUrl       = siteUrl + 'kiosk/weather/location=' + $weatherLocation;
  setInterval(function () {
    $.ajax({
      url: weatherUrl,
      success: function (response) {
        $('.kiosk-weather').replaceWith(response);
      }
    });
  }, 300000 /* 5 minutes */ );

}(jQuery)
