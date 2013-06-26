=== Simplify Admin Panel ===
Contributors: johnellmore
Donate link: http://johnellmore.com/
Tags: menu, cleanup, dashboard, multisite, network
Requires at least: 3.3
Tested up to: 3.5.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows easy removal of menu links, submenu links, and dashboard widgets from the WordPress Admin Panel.


== Description ==

This plugin allows the website administrator to remove the following from the WordPress Administration Panel:

* menu links (like "Tools", "Appearance", or annoying menus from third-party plugin)
* submenu links (like "Users > Your Profile", "Tools > Available Tools", or annoying menus from third-party plugins)
* and dashboard meta boxes (like "QuickPress", "Other WordPress News", or annoying meta boxes from third-party plugins)

It relies on hardcoded settings in wp-config.php (instead of having yet another admin settings page to deal with). Since the settings are all hardcoded, it can be easily set up by a website administrator and left in place without any users messing with it. It works on both single and multisite installations.


== Installation ==

To use this plugin, install it activate it like you would any other plugin. Then open up your wp-config.php file. Right above the line that says `That's all, stop editing! Happy blogging.` put in lines like the following:

	define('SAP_REMOVE_THESE_MENUS', 'Dashboard, Posts, Media, Pages, Comments, Appearance, plugins, users, tools, settings');
	define('SAP_REMOVE_THESE_SUBMENUS', 'Tools|Available Tools, users|all users, Add New');
	define('SAP_REMOVE_THESE_DASHBOARD_BOXES', 'Right Now, Recent Comments, Incoming Links, Plugins, quickpress, recent drafts, wordpress blog, other wordpress news');

Change the values in the comma-separated list to match the exact menu links, submenu links, and dashboard boxes that you'd like to remove. Note that the list is not case-sensitive, and spacing around the commas is ignored.

If you don't need some of the functionality, you can just omit that line entirely. For instance, if you don't need to remove any menu links, just remove the `define('SAP_REMOVE_THESE_SUBMENUS', ... )` line.

You can also remove menu and submenu items by their URL, and dashboard boxes by their ID. For example, these lines:

	define('SAP_REMOVE_THESE_MENUS', 'Pages, Tools');
	define('SAP_REMOVE_THESE_SUBMENUS', 'Plugins|Editor, Posts|Categories');
	define('SAP_REMOVE_THESE_DASHBOARD_BOXES', 'Recent Comments, Plugins');

are equivalent to

	define('SAP_REMOVE_THESE_MENUS', 'edit.php?post_type=page, tools.php');
	define('SAP_REMOVE_THESE_SUBMENUS', 'plugin-editor.php, edit-tags.php?taxonomy=category');
	define('SAP_REMOVE_THESE_DASHBOARD_BOXES', 'dashboard_recent_comments, dashboard_plugins');


== Frequently Asked Questions ==

= Why was this plugin developed? =

I got tired of the WordPress admin panel being cluttered up with tons of menu items and dashboard widgets that were unnecessary to the type of site that I was setting up. For example, I would setup a pages-only site, but I still had the posts menu in the administration panel. This kind of stuff just makes it more confusing for website editors who aren't very familiar with WordPress. I also despise plugins that would add useless extra meta boxes on the WordPress Dashboard, confusing end users. So I developed this plugin to let me remove those boxes and menu links that were out of place.

= When I try to remove the Dashboard menu link, I get PHP notices. Why? =

These notices will only appear when WordPress Debug mode is turned on. Just turn off debug mode to get rid of them.

= I'm trying to remove a menu/submenu/dashboard widget, but it's not working. Why? =

The plugin might be using a hidden element in the title of the thing you're trying to remove, and thus this plugin won't match the name correctly to remove it. Instead, try removing the menu/submenu items by their linked URL, and remove dashboard widgets by their id (see the Usage section above for details and the following FAQs for how find the URLs/IDs).

If, after doing that, it's still not working, post about it in this plugin's discussion forum and I'll try to help you.

= How do I find the URL of a menu/submenu item? =

If you want to remove an item by its URL rather than by its name, click on its link and note the URL of the page that you're now on. The URL that you want is the portion immediately following the "/wp-admin/" in the page URL.

= How do I find the ID of a dashboard box? =

This can be slightly tricky to non-technical users. In most modern browsers, right-click the title of the box and select "Inspect Element". When the developer console opens, you'll see a list of elements with the title element highlighted. Find the <div> element with a class of "postbox" that is an ancestor of the currently selected element. The value in its id attrite is the ID of the dashboard box.


== Screenshots ==

*None yet!*


== Changelog ==

= 1.0 =
* Initial release.


== Upgrade Notice ==

= 1.0 =
Initial release.
