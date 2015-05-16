/*! kisok v1.0 */
function scrollTweets() {
    var n = 0;
    var size = $('.kiosk_tweets_item').size();
    (function rotatetweets(jq) {
        jq.eq(n).fadeIn(2000).delay(5000).fadeOut(2000, function() {
            n++;
            if (n == 10) {
                n = 0;
                resetfaded(jq);
            };
            rotatetweets(jq);
        });

    })($('.kiosk_tweets_item'));
}

scrollTweets();

function resetfaded(jq) {
    jq.each(function() {
        $(this).fadeIn(0)
    });
}

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

//Reload page every 10 minutes
$(document).ready(function() {
    setInterval(function() {
        $("#kiosk").load($(location).attr('href'))
    }, 600000);
});

// To start carousel effect after reload (fix)
function startCarousel() {
    $('.carousel').carousel({
        interval: 3000
    });
}
startCarousel();

//to update tweet time relative to current time
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
    }, 5000);

}
kioskTweetTime();
