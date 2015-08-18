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
