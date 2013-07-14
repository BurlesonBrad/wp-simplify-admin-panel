<?php
/*
Plugin Name: Simplify Admin Panel
Plugin URI: https://github.com/johnellmore/wp-simplify-admin-panel
Description: Allows easy removal of menu links, submenu links, and dashboard widgets from the WordPress Admin Panel.
Version: 1.0
Author: John Ellmore
Author URI: http://johnellmore.com


===========================
           USAGE
===========================

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

For more information, please see the README.md file in this directory.

*/

class SimplifyAdminPanel {
	private static $menuFixes = array(
		'plugins' => 'plugins.php',
		'comments' => 'edit-comments.php'
	);
	
	private static $dashFixes = array(
		'recent comments' => 'dashboard_recent_comments',
		'incoming links' => 'dashboard_incoming_links',
		'wordpress blog' => 'dashboard_primary',
		'other wordpress news' => 'dashboard_secondary'
	);
	
	public function __construct() {
		if (!is_admin()) return;
		if (defined('SAP_REMOVE_THESE_MENUS'))
			add_action('admin_menu', array(&$this, 'removeMenuItems'), 9999);
		if (defined('SAP_REMOVE_THESE_SUBMENUS'))
			add_action('admin_menu', array(&$this, 'removeSubmenuItems'), 9999);
		if (defined('SAP_REMOVE_THESE_DASHBOARD_BOXES'))
			add_action('wp_dashboard_setup', array(&$this, 'removeDashboardWidgets'), 999);
	}
	
	public function removeMenuItems() {
		global $menu;
		$toRemove = explode(',', SAP_REMOVE_THESE_MENUS);
		foreach ($toRemove as $r) {
			$menuIndex = $this->findMenuIndex($r);
			if ($menuIndex) unset($menu[$menuIndex]);
		}
	}
	
	public function removeSubmenuItems() {
		global $submenu;
		$toRemove = explode(',', SAP_REMOVE_THESE_SUBMENUS);
		foreach ($toRemove as $r) {
			$r = explode('|', $r);
			if (count($r) > 1) {
				$parent = strtolower(trim($r[0]));
				$sub = $r[1];
			} else {
				$parent = false;
				$sub = $r[0];
			}
			$sub = strtolower(trim($sub));
			
			if ($parent) { // parent entry is given
				if (isset($submenu[$parent])) { // where parent given is a URL
					$this->removeMenuSubmenu($submenu[$parent], $sub);
				} else {
					$menuIndex = $this->findMenuIndex($parent);
					if ($menuIndex !== false) { // where parent given is a search term
						$submenuIndex = $this->getMenuLinkFromIndex($menuIndex);
						$this->removeMenuSubmenu($submenu[$submenuIndex], $sub);
					}
				}
			} else { // no parent is given; remove all instances of the submenu
				foreach ($submenu as &$item) {
					$this->removeMenuSubmenu($item, $sub);
				}
			}
			
		}
	}
	
	private function findMenuIndex($search) {
		global $menu;
		$search = strtolower(trim($search));
		if (empty($search)) continue;
		if (isset(self::$menuFixes[$search])) $search = self::$menuFixes[$search];
		
		foreach ($menu as $i => $item) {
			if ($search == strtolower($item[2]) || $search == strtolower(trim($item[0]))) { // url matches or name matches
				return $i;
			}
		}
		return false;
	}
	
	private function getMenuLinkFromIndex($index) {
		global $menu;
		return $menu[$index][2];
	}
	
	private function removeMenuSubmenu(&$menu, $submenu) {
		foreach ($menu as $i => $item) {
			if ($submenu == strtolower(trim($item[0])) || $submenu == strtolower(trim($item[2]))) {
				unset($menu[$i]);
			}
		}
	}
	
	public function removeDashboardWidgets() {
		global $wp_meta_boxes;
		
		// parse searches and simplify
		$toRemove = explode(',', SAP_REMOVE_THESE_DASHBOARD_BOXES);
		foreach ($toRemove as $i => &$r) {
			$r = strtolower(trim($r));
			if (empty($r)) unset($toRemove[$i]);
			else if (isset(self::$dashFixes[$r])) $r = self::$dashFixes[$r];
		}
		
		// go through meta boxes and remove matching boxes
		foreach ($wp_meta_boxes['dashboard'] as $i => $section) {
			foreach ($section as $j => $context) {
				foreach ($context as $k => $box) {
					if (
						(isset($box['id']) && in_array(strtolower($box['id']), $toRemove)) ||
						(isset($box['title']) && in_array(strtolower(trim($box['title'])), $toRemove))
					) {
						unset($wp_meta_boxes['dashboard'][$i][$j][$k]);
					}
				}
			}
		}
		
	}
}
new SimplifyAdminPanel;