Wordpress News Kiosk Plugin
===========================
[![Build Status](https://travis-ci.org/gios-asu/wordpress-news-kiosk-plugin.svg)](https://travis-ci.org/gios-asu/wordpress-news-kiosk-plugin) [![Code Climate](https://codeclimate.com/github/gios-asu/wordpress-news-kiosk-plugin/badges/gpa.svg)](https://codeclimate.com/github/gios-asu/wordpress-news-kiosk-plugin) [![Stories in Ready](https://badge.waffle.io/gios-asu/wordpress-news-kiosk-plugin.svg?label=ready&title=Ready)](http://waffle.io/gios-asu/wordpress-news-kiosk-plugin)

[![Github release](https://img.shields.io/github/release/gios-asu/wordpress-news-kiosk-plugin.svg?style=flat)](https://github.com/gios-asu/wordpress-news-kiosk-plugin/releases)
[![Github issues](https://img.shields.io/github/issues/gios-asu/wordpress-news-kiosk-plugin.svg?style=flat)](https://github.com/gios-asu/wordpress-news-kiosk-plugin/issues)
[![License](http://img.shields.io/:license-mit-blue.svg?style=flat)](https://github.com/gios-asu/wordpress-news-kiosk-plugin/blob/master/LICENSE.md)

This is a WordPress plugin for generating pages that are viewed on the Kiosks around the Wrigely Hall building.

The plugin is for the News Blog and provides shortcodes, a page template, and custom fields for posts.

This plugin is set to replace our current RiseVision system. [Example](http://preview.risevision.com/Viewer.html?type=presentation&id=77cdc8d3-f3a9-4978-9f7c-addf0c366cd5)

# How It Works

1. Create a custom Page with the "Kiosk" Page Template.
2. Add responsive markup and add in the following Shortcodes:
    * `[kiosk-posts tags="t,a,g,s"]`
    * `[kiosk-weather location='tempe,az']`
    * `[kiosk-tweets]`
    * `[kiosk-time]`
    * `[kiosk-slider]`
    * `[kiosk-asu-news]`
    * `[kiosk-logo path='http://www.example.com/image.png']`
    * ```html
    [kiosk_people_slider gios_url='https://sustainability.asu.edu']
        [
          {
            "keyword":"Biodiversity and preservation of natural environment",
            "person-slug":"jeffrey-englin",
            "quote":"Live for today!!! Hopefor tomorrow"
           },
         {
            "keyword":"Energy",
            "person-slug":"mark-edwin"

           },
         {
            "keyword-slug":"food-systems",
            "quote":"Never take a decision in anger"
           }

        ]
      [/kiosk_people_slider]
      ```
3. Set the T.V. to display the WordPress Page
4. In order for Posts to display on the Page, make sure that you have Posts that:
    * Have a featured image set, or have a `<img>` tag in the content of the post.
    * Are either scheduled to be published or are published.
    * Have a `kiosk-end-date` Custom Field with a valid date.
    * The post can have any Category, but it must have Tags.  These are the tags that are referenced in the Shortcode.

# Tags

The following tags can be mixed and matched:

* Kiosk SOS `kiosk-sos`
* Kiosk SSS `kiosk-sss-lounge`

# The Kiosk Posts Shortcode

The `[kiosk-posts tags="t,a,g,s"]` shortcode generates the following as output:

```html
<ul class="kiosk-slider">
  <li><img src="..." /></li>
  <li><img src="..." /></li>
  <li><img src="..." /></li>
  <li><img src="..." /></li>
  <!-- ... etc ... -->
</ul>
```

The images come from the unique posts that belong to the tags given in the shortcode.  Posts are expected to have the following criteria:

1. Each post as at least one `<img>` tag in it or have a featured image.
2. Each post has an `kiosk-end-date` custom field.
3. The `kiosk-end-date` is parsible by `strtotime`.  See [the PHP documentation for details on how strtotime works](http://php.net/manual/en/function.strtotime.php).
4. The posts are published or scheduled to be published.

In order to not hit any memory limits, the method grabs posts by 20 at a time from the list of tags, and checks to make sure
that the current date lies between the posts' start date and end date.  If it does, keep it in memory, otherwise
dereference it so that the garbage collector can free up the space.

**Notes**

We do not have test data for you to use, you will have to create your own. Create around 20 posts with date ranges that do and do not
lie within today's date range; and have categories, try uses two or three different categories.  Have the post bodies have 
image tags randomly placed through some random text. You can use
[this lorem ipsum generator](http://www.lipsum.com/) to make some random text for you.

You should test this by creating some Pages in your local WordPress and add the shortcode to them with different categories to try it out.

Check out [get_posts documentation](http://codex.wordpress.org/Template_Tags/get_posts) on how to get posts that are in categories.  

```php
$args = array(
  'posts_per_page' => 20,
  'offset'=> $offset,
  'category' => "comma,delimited,list,of,categories"
);

$news_posts = get_posts( $args );
```

To get the image from the post, check for the following in the following order:

1. Does the post have a featured image?  Use that if it does.  Note that this might come back as a Media ID that you need to map to.
2. Does the post have a "page_feature_image" in it's metadata?  Use that if it does.  Make sure this is an ABSOLUTE url.
3. Does the post have an image in its text body?  Grab the first one if it does.


# The KioskWeather Shortcode

`[kiosk-weather location='tempe,az']`

Kiosk Weather shortcode pulls the weather forecast from the yahoo weather API. 

Attributes Description:

 * location attribute is optional and defaults to Tempe, AZ.

Note: If the location you are trying doesn't work try without comma
`Example: Use location="paris texas" instead of location ="paris, texas"`

# The Kiosk Tweets Shortcode

`[kiosk_tweets limit="20" query="#sustainabity" handle="asugreen"]`

Kiosk Tweets shorcode displays the tweets requested by the given search parameters.

Attributes Description:

 * limit attribute is optional and defaults to 20.

 * Either one of the query or handle attribute is required if both are specified handle overrides the query for search and displays tweets from user timeline.

Note:

 * Make sure to update oauth_access_token, oauth_access_token_secret, consumer_key,consumer_secret with twitter account details in localsettings.php.
 * handle attribute works only for the current user whose account details are used in localsettings.php file

# The Kiosk Time Shortcode

`[kiosk-time]`

 Displays the current time and date calendar

# The Kiosk Slider Shortcode

`[kiosk_slider limit='20' feed_urls='http:://www.example.com/1,http:://www.example.com/2']`

Displays the images from the Flickr feed data.

Attributes Description:

* limit attribute is optional and defaults to 20.

* feed_urls is optional and defaults to GIOS flickr feed.

* Default feed url: 

`https://api.flickr.com/services/feeds/photos_public.gne?id=55424394@N03&lang=en-us&format=rss_200`

# The Kiosk ASU News Shortcode

`[kiosk_asu_news limit='20' feed_ids='153,178,358,40' content_limit='50']`

Kiosk ASU News Shortcode displays the news from the rss feed.

Attributes Description:
* limit attribute is optional and defaults to `20`.

* feed attribute is optional and defaults to `153,178,358,40`. Each of these numeric values correponds to part of below url.

```html
https://asunews.asu.edu/taxonomy/term/153/all/feed
https://asunews.asu.edu/taxonomy/term/178/all/feed
https://asunews.asu.edu/taxonomy/term/358/all/feed
https://asunews.asu.edu/taxonomy/term/40/all/feed
```
#Kiosk Logo Shortcode

`[kiosk-logo path='http:://www.example.com/image.png']`

Displays a logo by fetching from the given path.

Attributes Description:

* path attribute is optional and defaults to GIOS logo which is located in github 
`assets/images/logo-gios.jpg`.

#Kiosk People Slider Shortcode

```html
[kiosk_people_slider gios_url='https://sustainability.asu.edu']
[
  {
    "keyword":"Biodiversity and preservation of natural environment",
    "person-slug":"jeffrey-englin",
    "quote":"Live for today!!! Hopefor tomorrow"
   },
 {
    "keyword":"Energy",
    "person-slug":"mark-edwin"

   },
 {
    "keyword-slug":"food-systems",
    "quote":"Never take a decision in anger"
   }

]
[/kiosk_people_slider]
```

Attributes Description:
* Kiosk People Slider shortcode displays the random list of people from each category they belong to and scrolls through different keywords available. 
Json style synax should be given as part of content with keyword or keyword-slug, person-slug, quote.

* For the given keyword shortcode displays the person's image identified using person-slug is displayed as featued image for that keyword and given quote is shown next to it.

* Person-slug and quote attributes are optional and by default they are empty.
* Keyword is the category name they belong to 
  `For example Keyword can be: Energy or food systems but not deforestation`

Note: If you are on multi-site you need to access below lines to .htaccess file

```
RewriteRule ^kiosk/twitter/limit/([^/]*)? /wp-content/plugins/wordpress-news-kiosk-plugin/pages/views/kiosk-tweets-presenter.php?limit=$1 [QSA,L]
RewriteRule ^kiosk/weather/location=([^/]*)? /wp-content/plugins/wordpress-news-kiosk-plugin/pages/views/kiosk-weather-presenter.php? [QSA,L]

```



