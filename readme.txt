=== WP Flickr Background ===
Contributors: Myatu
Tags: flickr, photo, theme, background
Requires at least: 2.9
Tested up to: 3.3
Stable tag: 1.2

WP Flickr Background allows you to display a random photo from Flickr or another source as the website background, without editing the theme.

== Description ==

NOTE: WP Flickr Background has been superseded by [Background Manager](http://wordpress.org/extend/plugins/background-manager/).

WP Flickr Background is a simple to use WordPress plugin that allows you to 
display a photo from Flickr and other sources as the theme background, without 
the need to modify any files.

All you need to do is create one or more galleries within the plugin's settings, 
each containing a collection of photos that you have chosen, and WP Flickr Background
will randomly select a photo from the active gallery to display as the
theme background.

You can also customise a gallery by adding CSS styling code that will be loaded 
along with the photo, allowing you to color match the Wordpress theme to 
the particular photo displayed or for other use.

= Features =

* __New as of 1.1!__ Use photos from the Media Library, your own computer or websites
  other than Flickr as the background
* Decide how often the background image should change (ie., every day, once
  per browser session, etc.)
* Stretch a background photo horizontally and/or vertically
* Align background photos according to the visitor's screen layout
* Optionally disable the original theme's background
* Optional Javascript compression
* Support for WordPress caches such as WP Super Cache
* Multiple galleries
* Custom CSS style sheet per gallery, loaded with the theme if it's active

= Browser Compatibility =

WP Flickr Background has been tested and known to work with the following browsers:

* Google Chrome 3+
* Opera 10+
* Firefox 3+
* Microsoft Internet Explorer 7+
* Safari 4+
* Lynx

= License =

[GNU GPL version 3](http://www.gnu.org/licenses/)

This product uses the Flickr API but is not endorsed or certified by Flickr.


== Installation ==

1. Upload the contents of the ZIP file to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Access the plugin's configuration through the 'Settings/WP Flickr Background' menu to:
    * Configure options
    * Add galleries and photos
    * Read more detailed help

= Requirements =

* WordPress version 2.9 or better
* PHP version 4.2.3 or better
* A browser with Javascript support enabled

	
== Screenshots ==

1. Editing a gallery
2. Overview of available galleries
3. Main configuration screen,
4. An example of a Flickr photo used as a WordPress theme background (original photo used as the
 background by [prakhar](http://www.flickr.com/photos/prakhar/2176045485/), released under the CC Attribution license).


== Frequently Asked Questions ==

= How do I create a new gallery? =

1. Access 'Settings/WP Flickr Background' from the WordPress Administration 
1. Select the 'Galleries' submenu
1. Click the 'Add New Gallery' button

= How do I add Flickr photos to a gallery? =

After you create or edit a gallery, visit [Flickr](http://www.flickr.com/ "Flickr") and
start searching for photos that interest you. Once you have found the desired photo, click
the _Share This_ button above that photo and select _Grab the link_. Then just copy & paste
the link into the provided field and click the _Add Flickr Photo_ button.

Examples of valid links are:

* `http://www.flickr.com/photos/bees/155761353/` 
* `http://www.flickr.com/photos/bees/155761353/sizes/o/`
* `http://farm1.static.flickr.com/2/1418878_1e92283336_m.jpg`

After you have added photos, click the 'Add Gallery' or 'Save Gallery' button found at
the bottom of the page.

_Tip!_ Many web browsers allow you to drag a photo directly onto the field next to the
'Add Photo' button, making it very simple to add photos!

= How do I add regular photos to the gallery? =

Adding a regular photo works the same way as adding a photo to a blog post. While editing a
gallery, simply click the _Add Media Library Photo_ button. You can now upload a new photo
from your computer, use a photo from another website or use an existing photo in your Wordpress
Media Library.

You may want to ensure that the _Size_ is set to _Full Size_ to maintain the best quality for
the background. If you wish to add information about the author for the selected photo, you
can add this to the _Alternate Text_ field.

= The background image looks very 'jagged', why? =

If the photo is from the Wordpress Media Library, you should ensure that the _Size_ is set to
_Full Size_ before adding. Simply delete and then re-add the image with those settings.

Although the plugin automatically selects the largest available size, some Flickr photos may 
have a resolution less than 1024x768. Some end users use a screen resolution larger than this, so
when stretching these photos to fit their screen, they may appear of poor quality. Unfortunately, 
the only direct solution to this is to find a Flickr photo with larger available resolutions.

= My website seems slower now, why? =

There are several ways to speed up WP Flickr Background:

1. Access 'Settings/WP Flickr Background' from the WordPress Administration
1. Under the 'Advanced Configuration' heading:
    * Disable the theme background by checking/ticking 'Theme Backround'
    * Enable 'Javascript Compression'
    * Enable 'Cacheable'
	
Or you create a file named '.confighash' in the `/wp-content/plugins/wp-flickr-background/`
directory and make it writable. On Linux or similar operating systems this can be
done with:

1. `touch .confighash`
1. `chmod 666 .confighash`

= The browser has become sluggish, why? =

A few browsers have difficulty rendering large images overlaid by other content. There
are a few options you could try:

* Disable both horizontal and/or vertical image stretching
* Choose images which list the largest available size as smaller than 2048 x 1536

= Some visitors complain about Javascript errors, what can I do? =

You may wish to turn off the Javascript compression used by the plugin:

1. Access 'Settings/WP Flickr Background' from the WordPress Administration
1. Uncheck/untick the 'Javascript Compression' under the 'Advanced Configuration' heading
1. Save the changes

== Changelog ==

= 1.2 =
* Updated plugin for Wordpress 3.3 compatibility
* Bug fix: Fixed Flickr URL checking (staticflickr.com)

= 1.1.1 =
* Updated plugin for Wordpress 3.1.1 compatibility
* Changed: Cleaned HTML to pass W3C validation / XHTML 1.0 Transitional compliance.
* Bug fix: Main configurations are not saving in MSIE due to malformed DIV/FORM tags.

= 1.1 =
* Added: Ability to add local (or remote) hosted images.
* Added: Advanced option specifying the location of the license and attribution.
* Bug fix: A minor regression in the cookie handling, which was attributed to the introduction of additional change frequencies.
* Changed: Main plugin class has been changed to upgrade database options on version change, rather than (re)activation.
* Changed: Updated CSS for increased compatibility with themes.

= 1.0.4 =
* Updated plugin for WordPress 3.1 compatibility
* Added additional change frequencies: 5 minutes and on every page (re)load
* Bug fix (MAJOR): Per-user stored variables could cause PHP to crash with certain object caches (500 Server Error)
* Bug fix: Background images appeared on login screen since WordPress 3.1 release
* Bug fix: Some plugins sent output when they were not supposed to, causing errors with the JavaScript/Stylesheets

= 1.0.3 =
* A new option has been introduced to hide the license and attribution informationin the footer.
* Bug fix: Config hash file was never initialized
* Bug fix: Enabling "cacheable" option resulted in fatal PHP errors in some cases
* Bug fix: Preview option did not work when "cacheable" option was enabled
* Bug fix: "All Rights Reseved" licenses had no URLs

= 1.0.2 =
* Moved plugin to beta stage
* Corrected description regarding chmod and the .confighash file

= 1.0.1 =
* Initial alpha release

== Upgrade Notice ==

= 1.1.1 =
This version adds WordPress 3.1.1 compatibility and fixes a minor bug.

= 1.1 =
This version adds the ability to use your own photos/images outside of the Flickr service, and fixes a few bugs.

= 1.0.4 =
This version adds WordPress 3.1 compatibility, fixes minor bugs, a possible PHP crash bug and adds additional change frequencies.

= 1.0.3 =
This version fixes a few minor bugs and introduces the ability to hide the license/attribution information in the footer.

== Credits ==

A special thank you to the OVH UK customers and employees who helped me debug the very first 
version of WP Flickr Background (in no particular order):

* Razakel
* gregoryfenton
* Andy
* NickW
* Jonlewi5
* Fozle
* Ashley 
* Neil
* monkey56657
* Marko

And of course YOU!
