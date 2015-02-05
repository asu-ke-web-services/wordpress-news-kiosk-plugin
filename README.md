# Wordpress News Kiosk Plugin

Plugin for creating kiosk (T.V. displayed web pages) pages from posts and categories in a Wordpress Blog.

This is a migration of our current RiseVision system to use WordPress.

Kiosk items will be displayed by pulling blog posts from categories that have the following:

* A Category.  The category will be "kiosk-sos", "kiosk-sss-lounge", or "kiosk-all".  "kiosk-sos" and "kiosk-sss-lounge" are child categories of "kiosk-sos-all".
* A start date.  The post will have a custom field "start_date" with a parsible date in it.
* An end date.  The post will have a custom field "end_date" with a parsible date in it.
* An featured image.  We will try to enforce the use of the featured image for these news items.

We will have a WordPress page for each display that will only have 1 shortcode as the content of the page.  The following pages will be created to start us off:

* Kiosk SOS - Content: `[tv-slider categories="kiosk-sos"]`
* Kiosk SSS Lounge - Content: `[tv-slider categories="kiosk-sss-lounge"]`

The Pages will use a special page template created by the plugin.

# Phase 1
Create a shortcode `[tv-slider categories="comma,delimited,list,of,categories"]` that will generate as output:

```html
<ul class="slider">
  <li><img src="..." /></li>
  <li><img src="..." /></li>
  <li><img src="..." /></li>
  <li><img src="..." /></li>
  <!-- ... etc ... -->
</ul>
```

The images will come from the unique posts that belong to the categories given in the shortcode.  Expect posts to have the following criteria:

1. Each post will have at least one `<img>` tag in it.
2. Each post will have meta data (i.e. custom fields).
3. Each post's meta data will have an entry for `start_date` and an entry for `end_date`.
4. The start datetime and end datetime are parsible by `strtotime`.  See [the PHP documentation for details on how strtotime works](http://php.net/manual/en/function.strtotime.php).

In order to not hit any memory limits, grab posts by 20 at a time from the list of categories, and check to make sure
that the current date lies between the posts' start date and end date.  If it does, keep it in memory, otherwise
dereference it so that the garbage collector can free up the space.

After you have an array of unique posts (make sure to not accidently get duplicates!) that belong to the given categories and have a date range where the current date lies within it, you will need to find the first image's src in each post.  Take that src and create a list item that you will print out.

Print out the full list!

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

# Phase 2

The images will need to actually be in a slideshow.  We have some slideshow code in our current codebase that we can show you.
It mainly uses jQuery to fade images in and out in order.  Nothing special.

The plugin will register a new page template that will have the HTML and the JavaScript for the slider.


