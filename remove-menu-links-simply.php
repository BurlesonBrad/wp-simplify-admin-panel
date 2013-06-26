<?php
/*
Plugin Name: Simplify WordPress Admin
Plugin URI: https://github.com/johnellmore/wp-remove-admin-menu-links
Description: Allows easy removal of admin menu pages and subpages, dashboard widgets, and meta boxes.
Author: John Ellmore
Author URI: http://johnellmore.com
*/

//define('RAML_REMOVE_THESE_MENUS', 'posts, media, pages, comments, appearance, plugins, users, tools, settings');
//define('RAML_REMOVE_THESE_SUBMENUS', 'Tools|Available Tools, users|all users, Add New');
//define('RAML_REMOVE_THESE_DASHBOARD_BOXES', '');

/* 
===========================
        HOW TO USE
===========================

1. Install the plugin like you would normally.
2. Activate it.
3. Open up wp-config in a text editor.
4. Specify which menu links you'd like removed (if any):

define('RAML_REMOVE_THESE_MENUS', 'posts, media, pages, comments, appearance, plugins, users, tools, settings');
define('RAML_REMOVE_THESE_SUBMENUS', 'edit.php,');

*/

class RemoveAdminMenuLinks {
	private static $menuFixes = array(
		'plugins' => 'plugins.php',
		'comments' => 'edit-comments.php'
	);
	
	private static $dashFixes = array(
		'recent comments' => 'dashboard_recent_comments'
	);
	
	public function __construct() {
		if (!is_admin()) return;
		if (defined('RAML_REMOVE_THESE_MENUS'))
			add_action('admin_menu', array(&$this, 'removeMenuItems'), 9999);
		if (defined('RAML_REMOVE_THESE_SUBMENUS'))
			add_action('admin_menu', array(&$this, 'removeSubmenuItems'), 9999);
		if (defined('RAML_REMOVE_THESE_DASHBOARD_BOXES'))
			add_action('wp_dashboard_setup', array(&$this, 'removeDashboardWidgets'), 999);
	}
	
	public function removeMenuItems() {
		global $menu;
		$toRemove = explode(',', RAML_REMOVE_THESE_MENUS);
		foreach ($toRemove as $r) {
			$menuIndex = $this->findMenuIndex($r);
			if ($menuIndex) unset($menu[$menuIndex]);
		}
	}
	
	public function removeSubmenuItems() {
		global $submenu;
		$toRemove = explode(',', RAML_REMOVE_THESE_SUBMENUS);
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
		$toRemove = explode(',', RAML_REMOVE_THESE_DASHBOARD_BOXES);
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
new RemoveAdminMenuLinks;