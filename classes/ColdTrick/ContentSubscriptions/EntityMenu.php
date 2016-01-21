<?php

namespace ColdTrick\ContentSubscriptions;

class EntityMenu {
	
	/**
	 * Add a subscribe/unsubscribe link to the supported entity types
	 *
	 * @param string          $hook         the name of the hook
	 * @param string          $type         the type of the hook
	 * @param \ElggMenuItem[] $return_value the current menu items
	 * @param array           $params       supplied params
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function register($hook, $type, $return_value, $params) {
		
		if (!elgg_is_logged_in()) {
			return;
		}
		
		if (empty($params) || !is_array($params)) {
			return;
		}
		
		$entity = elgg_extract('entity', $params);
		if (empty($entity) || !content_subscriptions_can_subscribe($entity)) {
			return;
		}
		
		$subscribed = false;
		if (content_subscriptions_check_subscription($entity->getGUID())) {
			$subscribed = true;
		}
		
		$methods = content_subscriptions_get_notification_settings();
		if (!empty($methods)) {
			$return_value[] = \ElggMenuItem::factory([
				'name' => 'content_subscription_subscribe',
				'text' => elgg_echo('content_subscriptions:subscribe'),
				'href' => "action/content_subscriptions/subscribe?entity_guid={$entity->getGUID()}",
				'is_action' => true,
				'priority' => 100,
				'item_class' => $subscribed ? 'hidden' : '',
			]);
		}
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'content_subscription_unsubscribe',
			'text' => elgg_echo('content_subscriptions:unsubscribe'),
			'href' => "action/content_subscriptions/subscribe?entity_guid={$entity->getGUID()}",
			'is_action' => true,
			'priority' => 101,
			'item_class' => $subscribed ? '' : 'hidden',
		]);
		
		return $return_value;
	}
}
