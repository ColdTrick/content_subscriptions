<?php

namespace ColdTrick\ContentSubscriptions;

class PageMenu {
	
	/**
	 * Add a link to admin statistics menu
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:page'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function register(\Elgg\Hook $hook) {
		
		if (!elgg_is_admin_logged_in() || !elgg_in_context('admin') || !elgg_is_active_plugin('advanced_statistics')) {
			return;
		}
		
		$return_value = $hook->getValue();
	
		$return_value[] = \ElggMenuItem::factory([
			'name' => "information:advanced_statistics:content_subscriptions",
			'href' => "admin/advanced_statistics/content_subscriptions",
			'text' => elgg_echo("admin:advanced_statistics:content_subscriptions"),
			'parent_name' => 'information:advanced_statistics',
			'section' => 'information',
		]);
		
		return $return_value;
	}
}
