=== Kalendas ===
Contributors: sebaxtian
Tags: google calendar, calendar, events
Requires at least: 2.9
Tested up to: 2.9.2
Stable tag: 0.1.3.1

Kalendas gets your events from multiple Google Calendars and displays them in your page using a time range.

== Description ==

Kalendas is a plugin that requires the data from your Google Calendar and display one or all your calendars in an event list.

You can use the tag [kalendas:title,feed] to put an event list in a pages or posts.

See the screenshots for an infogram explaining how to set public a calendar where to get the XML feed.

Screenshots are in spanish because it's my native language. As you should know yet I __spe'k__ english, and the plugin use it by default.

== Installation ==

1. Install __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__.
2. Decompress kalendas.zip and upload `/kalendas/` to the `/wp-content/plugins/` directory.
3. Activate the plugin through the __Plugins__ menu in WordPress
4. Configure Kalendas to set your time range.
5. Add the widget into your side bar, each with your Calendar RSS or XML descriptor.
6. Add the tag [kalendas:title,source] in a page or post where you want an event list.

== Frequently Asked Questions ==

= Is this plugin bug free? =

I don't think so. So far it works with my configuration, but i didn't test it 
with other. Feedbacks would be appreciated.

= It shows events outside the time range I set =

Maybe your Google Calendar and your server have a diferent 'timezone' reference. Check this in your WordPress configuration and your Calendar settings. 

= It says something about minimax. What's this? =

This plugin requires __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__ in order to work.

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
