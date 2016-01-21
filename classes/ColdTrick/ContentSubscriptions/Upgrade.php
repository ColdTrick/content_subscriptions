<?php

namespace ColdTrick\ContentSubscriptions;

class Upgrade {
	
	/**
	 * Listen to the upgrade event, to register a script
	 *
	 * @param string $event  name of the event
	 * @param string $type   type of the event
	 * @param null   $object supplied object
	 *
	 * @return void
	 */
	public static function registerScript($event, $type, $object) {
		
		// Upgrade also possible hidden entities. This feature get run
		// by an administrator so there's no need to ignore access.
		$access_status = access_get_show_hidden_status();
		access_show_hidden_entities(true);
		
		// register an upgrade script
		$options = array(
			'type' => 'user',
			'relationship' => CONTENT_SUBSCRIPTIONS_SUBSCRIPTION,
			'inverse_relationship' => true,
			'count' => true
		);
		$count = elgg_get_entities_from_relationship($options);
		if ($count) {
			$path = 'admin/upgrades/content_subscriptions';
			$upgrade = new \ElggUpgrade();
			if (!$upgrade->getUpgradeFromPath($path)) {
				$upgrade->setPath($path);
				$upgrade->title = 'Content Subscription upgrade';
				$upgrade->description = 'The way content subscriptions are handled has changed.
					Run this script to make sure all content subscriptions are migrated.';
					
				$upgrade->save();
			}
		}
		
		access_show_hidden_entities($access_status);
	}
}
