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

/*kisok-people-slider
 */
+function ($) {
  'use strict';
  /**
   * Resizes People Slider by calculating space occupied by logo and People slider
   * header and the padding of 1st column so the height and width accomdate to
   * aspect ratio of width: height :: 7:5
   * If window width is smaller then height use width for calculating aspect ratio
   * as when resized to width of max 1200px each column occupies full width slider
   * layout can still fit in available height.
   * Note: Default setting height will take care of aspect ratio which is handled
   * in CSS when width > height for other case calculate the ratio of width and
   * set height.
   */
  function resizePeopleSlider () {
    var logoHeight   = $('.kiosk-third-floor__logo').outerHeight(true);
    var peopleSliderheaderHeight = $('.kiosk-people-slider__layout__header').outerHeight(true);
    var remainHeight = peopleSliderheaderHeight + logoHeight + 30;
    $('.kiosk-people-slider__aspect-ratio').css('height', 'calc( 100vh - ' + remainHeight + 'px)');
    if ($(window).width() < $(window).height()) {
      $('.kiosk-people-slider__aspect-ratio').css('height', $(window).width() * 5 / 7);
    }
  }

  $(window).resize(function () {
    resizePeopleSlider();
  });
  resizePeopleSlider();

}(jQuery)

/*kisok-time
 */
+function ($) {
  'use strict';
  /**
   * Invokes itself with 5 seconds interval and updates Kiosk widget date and time
   *
   */
  +function updateKioskTime(timeSelector, calMonthSelector, calDateSelector) {
    var date         = new Date();
    var hours        = date.getHours();
    var minutes      = date.getMinutes();
    var dateString   = date.toString();
    var month        = dateString.substring(4, 7);
    var currentDate  = dateString.substring(8, 10);
    var ampm         = hours >= 12 ? 'PM' : 'AM';
    hours            = hours % 12;
    hours            = hours ? hours : 12; // the hour '0' should be '12'
    hours            = hours < 10 ? '0' + hours : hours;
    minutes          = minutes < 10 ? '0' + minutes : minutes;
    var strTime      = hours + ':' + minutes + ' ' + ampm;
    $(timeSelector).html(strTime);
    $(calMonthSelector).html(month);
    $(calDateSelector).html(currentDate);
    setTimeout(function () {
      updateKioskTime(timeSelector, calMonthSelector, calDateSelector);
    }, 5000 /* 5 seconds */ );
  }('#kiosk_display_time', '.kiosk-date-time__calendar-icon strong', '.kiosk-date-time__calendar-icon span')
}(jQuery)

/*kisok-tweets
 */
+function ($) {
  'use strict';
  /**
   * To update tweet time relative to current time every 10 seconds
   * For each tweet read the actual time got from server and update the tweet time
   * relative to the current time
   */
  +function setRelativeTweetTime(oldTimeSelector) {
    var $formatedTime = $(oldTimeSelector);
    $formatedTime.each(function (_, tweetTime) {
      var oldTime = $(tweetTime).html();
      // Ignore the case there the date is already formatted
      // as a month
      if (oldTime.length <= 3) {
        var actualTime = $(tweetTime).data('actualTime');
        $(tweetTime).html(calculateRelativeTime(actualTime));
      }
    });
    setTimeout(function () {
      setRelativeTweetTime(oldTimeSelector);
    }, 10000 /* 10 seconds */ );
  }('.kiosk-tweets__tweet__details__tweet-time');

  /**
   * If less than 1 second show as now
   * If less than minute display as seconds ago
   * If less than hours display as minutes ago
   * If less than 24 hours display as hours ago
   * Otherwise return in days. This is duplicate functionality of php to update relative time
   * @param tweetTime is Unix timestamp (the number of seconds since January 1 1970 00:00:00 GMT).
   * @see Kiosk_WP::Twitter_Api_Helper::time_short_form
   */
  function calculateRelativeTime (tweetTime) {
    var time = Math.round(+new Date() / 1000);
    var elapsedTime = time - tweetTime;
    var temp = '0h';
    if (elapsedTime < 1) {
      return 'now';
    }
    var timeConversion = [{
      secs: 1,
      unit: 's'
    }, {
      secs: 60,
      unit: 'm'
    }, {
      secs: 60 * 60,
      unit: 'h'
    }, {
      secs: 24 * 60 * 60,
      unit: 'd'
    }];

    for (var i = 0; i < timeConversion.length; i++) {
      var ratioElapsedToUnit = elapsedTime / timeConversion[i].secs;
      var roundedTime = Math.round(ratioElapsedToUnit);
      temp = roundedTime + timeConversion[i].unit;
      var notAFractionalUnit = ratioElapsedToUnit >= 1;
      var lessThanOneDay = timeConversion[i].unit == 'h' && ratioElapsedToUnit < 24;
      var lessThanOneHour = timeConversion[i].unit == 'm' && ratioElapsedToUnit < 60;
      var lessThanOneMinute = timeConversion[i].unit == 's' && ratioElapsedToUnit < 60;
      if (notAFractionalUnit && (lessThanOneDay || lessThanOneHour || lessThanOneMinute)) {
        return temp;
      }
    }
    return temp;
  }

  /**
   * Invokes ajax call to server every 5 minutes and
   * replaces tweets block
   * Updates tweets widgets only when servers returns valid data
   */
  var siteUrl = $(location).attr('href');
  var $tweetsTitle = $('.kiosk-tweets__timeline__title');
  var tweetsLimit = $tweetsTitle.data('limit');
  var tweetsQuery = $tweetsTitle.data('query');
  var tweetsUrl = siteUrl + 'kiosk/twitter/limit=' + tweetsLimit + '/query=' + tweetsQuery;
  setInterval(function () {
    $.ajax({
      url: tweetsUrl,
      success: function (response) {
        $('.kiosk-tweets').replaceWith(response);
      }
    });
  }, 300000 /* 5 minutes */ );

  /**
   * every 10 seconds, move
   * the first tweet up. After animation, reset list,
   * and move first tweet to the end of the list
   */
  var $tweetList = $('#kiosk-tweets__tweets');
  setInterval(function tweetAnimate() {
    var $firstTweet = $tweetList.find('li:first');
    $tweetList.find('li').animate({
      top: (-1 * $firstTweet.outerHeight(true)) + 'px' // calculate height of first element
    }, 2000, function () {
      $firstTweet.appendTo($tweetList);
      $firstTweet.show();
      $tweetList.find('li').css({
        top: '0'
      });
    });
  }, 10000 /* 10 seconds */ );

}(jQuery)

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
