<?php

namespace ColdTrick\ContentSubscriptions;

class EntityMenu {
	
	/**
	 * Add a subscribe/unsubscribe link to the supported entity types
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:entity'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function register(\Elgg\Hook $hook) {
		
		if (!elgg_is_logged_in()) {
			return;
		}
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggEntity || !content_subscriptions_can_subscribe($entity)) {
			return;
		}
		
		$return_value = $hook->getValue();
		$subscribed = content_subscriptions_check_subscription($entity->guid);
		
		$methods = content_subscriptions_get_notification_settings();
		if (!empty($methods)) {
			$return_value[] = \ElggMenuItem::factory([
				'name' => 'content_subscription_subscribe',
				'text' => elgg_echo('content_subscriptions:subscribe'),
				'href' => elgg_generate_action_url('content_subscriptions/subscribe', [
					'entity_guid' => $entity->guid,
				]),
				'icon' => 'bell-o',
				'priority' => 100,
				'item_class' => $subscribed ? 'hidden' : '',
				'data-toggle' => 'content-subscription-unsubscribe',
			]);
		}
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'content_subscription_unsubscribe',
			'text' => elgg_echo('content_subscriptions:unsubscribe'),
			'href' => elgg_generate_action_url('content_subscriptions/subscribe', [
				'entity_guid' => $entity->guid,
			]),
			'icon' => 'bell-slash-o',
			'priority' => 101,
			'item_class' => $subscribed ? '' : 'hidden',
			'data-toggle' => 'content-subscription-subscribe',
		]);
		
		return $return_value;
	}
}
