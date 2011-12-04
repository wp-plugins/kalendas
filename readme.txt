=== Kalendas ===
Contributors: sebaxtian
Tags: google calendar, calendar, events
Requires at least: 2.9
Tested up to: 3.1
Stable tag: 0.2.6.1

Kalendas gets your events from multiple Google Calendars and displays them in your page using a time range.

== Description ==

Kalendas is a plugin that requires the data from your Google Calendar and display one or all your calendars in an event list.

You can use the tag [kalendas:title,feed] to put an event list in a pages or posts.

See the screenshots for an infogram explaining how to set public a calendar where to get the XML feed.

Kalendas has been translated french by the __[InMotion Hosting Team](http://www.inmotionhosting.com/)__. Thanks for your time guys!

Screenshots are in spanish because it's my native language. As you should know yet I __spe'k__ english, and the plugin use it by default.

== Installation ==

1. Decompress kalendas.zip and upload `/kalendas/` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the __Plugins__ menu in WordPress
3. Configure Kalendas to set your time range.
4. Add the widget into your side bar, each with your Calendar RSS or XML descriptor.
5. Add the tag [kalendas:title,source] in a page or post where you want an event list.

== Frequently Asked Questions ==

= Is this plugin bug free? =

I don't think so. So far it works with my configuration, but i didn't test it 
with other. Feedbacks would be appreciated.

= It shows events outside the time range I set =

Maybe your Google Calendar and your server have a diferent 'timezone' reference. Check this in your WordPress configuration and your Calendar settings. 

= Can I set my own CSS? =

Yes. Copy the file kalendas.css to your theme folder. The plugin will check for it.

= Can I set my own template to show the events? =

Yes. Copy the file templates/kalendas_event.tpl into your theme directory and edit it as you want.

= Where can I get the feed to my calendar? =

The calndar must be public for Kalendas to get it. One of the screenshots in the gallery is an infogram explaining how to set a calendar as public and where to get the XML feed.

== Screenshots ==

1. Events in sidebar
2. Event popup window
3. Kalendas' options
4. Sidebar widget options
5. Infogram to set a Google Calendar as public data and where to get the XML.

== Changelog ==

= 0.2.6.1 =
* Solved bug with old PHP versions.

= 0.2.6 =
* Solved problem with the excerpt.
* Checked for WP 3.1

= 0.2.5.1 =
* Solved a bug with text filters.

= 0.2.5 =
* Modified TinyMCE call to solve bugs with wp-cache.

= 0.2.4.4 =
* Solved bug with 'canceled' events.

= 0.2.4.3 =
* Solved bug with save-day time zones.

= 0.2.4.2 =
* Added all day events date format.
* Solved bug with all day events.

= 0.2.4.1 =
* Solved bug with old PHP versions.

= 0.2.4 =
* Solved bug with reccurent meeings.

= 0.2.3 =
* Solved configuration rights bug.

= 0.2.2 =
* Solved minor bugs.

= 0.2.1 =
* Using WP functions to add safely scripts and css.

= 0.2 =
* Solved bug with 'max numbers'.
* Added time frame.
* WP filter applied into description text.

= 0.1.4.3 =
* Solved other bug in the readfile function.

= 0.1.4.2 =
* Solved bug in the readfile function.

= 0.1.4.1 =
* Solved XHTML bug.

= 0.1.4 =
* First release to not use minimax.

= 0.1.3.1 =
* Oops, another bug with time zone. Bug resolved.

= 0.1.3 =
* Oops, another bug with time zone. Bug resolved.
* Added a tag to put an event list in a page/post.

= 0.1.2 =
* Solved a bug where kalendas updated events in GMT midnight, not in server zone midnight.

= 0.1.1 =
* Solving a 'class name' bug.
* First release in SVN

= 0.1 =
* First stable release.
