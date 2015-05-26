/*! kisok v1.0 
* Scrolls 10 tweets with fade in and fade out effect and a delay of 5 seconds. 
**/
function scrollTweets() {
  var n = 0;
  var size = $('.kiosk_tweets_item').size();
  (function rotateTweets(jq) {
    jq.eq(n).fadeIn(2000).delay(5000).fadeOut(2000, function() {
      n++;
      if (n == 10) {
        n = 0;
        resetFaded(jq);
      };
      rotateTweets(jq);
    });

  })($('.kiosk_tweets_item'));
}

scrollTweets();

/*
* Fades in all the elements passed.
*
*/
function resetFaded(jq) {
  jq.each(function() {
    $(this).fadeIn(0)
  });
}

/*
* Invokes itself with 5 seconds interval and updates the time
*
*/
function kioskTime() {
  var date = new Date();
  var hours = date.getHours();
  var minutes = date.getMinutes();
  var ampm = hours >= 12 ? 'PM' : 'AM'
  hours = hours % 12;
  hours = hours ? hours : 12; // the hour '0' should be '12'
  hours = hours < 10 ? '0' + hours : hours;
  minutes = minutes < 10 ? '0' + minutes : minutes;
  var strTime = hours + ':' + minutes + ' ' + ampm;
  document.getElementById('kiosk_display_time').innerHTML = strTime;
  var t = setTimeout(function() {
    kioskTime()
  }, 5000);

}
kioskTime();

/*
* Invokes ajax call to server every 5 minutes and 
* replaces tweets block and weather block
*
*/
$(document).ready(function() {
  setInterval(function() {
    $.ajax({
      url: $(location).attr("href"),
      success: function(response) {
        result_kiosk_tweets = $(".kiosk_tweets", response);
        $(".kiosk_tweets").replaceWith(result_kiosk_tweets);
        result_kiosk_weather = $(".kiosk_weather", response);
        $(".kiosk_weather").replaceWith(result_kiosk_weather);
      }
    })
  }, 300000);
});


/*
* To update tweet time relative to current time every 10 seconds
* For each tweet read the actual time got from server and update the tweet time
* relative to the current time every 10 seconds interval
* If less than 1 second show as now
* If less than minute display as seconds ago
* If less than hours display as minutes ago
* If less than 24 hours display as hours ago
* Otherwise leave it as set by server.
*/
function kioskTweetTime() {
  var time = Math.round(+new Date() / 1000);
  var formated_time = $('.kiosk_date');
  $('.kiosk_actualtweettime').each(function(index, timeTag) {
    var oldTime = formated_time[index].innerHTML;
    var lastChar = oldTime.charAt(oldTime.length - 1);
    if (oldTime.length <= 3) {
      var actualTime = timeTag.innerHTML;
      var etime = time - actualTime;
      if (etime < 1) {
        formated_time[index].innerHTML = 'now';
      }
      var a = [];
      a.push({
        secs: 1,
        str: 's'
      });
      a.push({
        secs: 60,
        str: 'm',
      });
      a.push({
        secs: 60 * 60,
        str: 'h',
      });

      for (i = 0; i < a.length; i++) {
        var d = etime / Number(a[i].secs);
        if (d >= 1 && (a[i].str == 'h' && d < 24 || a[i].str == 'm' && d < 60 || a[i].str == 's' && d < 60)) {
          r = Math.round(d);
          formated_time[index].innerHTML = r + a[i].str;
        }
      }
    }
  });
  var t = setTimeout(function() {
    kioskTweetTime()
  }, 10000);

}
kioskTweetTime();
