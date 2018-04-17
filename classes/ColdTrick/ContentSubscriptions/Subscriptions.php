<?php

namespace ColdTrick\ContentSubscriptions;

class Subscriptions {
	
	/**
	 * Make sure unsubscribed users don't get notifications based on their group-subscriptions
	 *
	 * @param \Elgg\Hook $hook 'get', 'subscriptions'
	 *
	 * @return void|array
	 */
	public static function removeUnsubscribedGroupMembers(\Elgg\Hook $hook) {
		
		$subscribers = $hook->getValue();
		if (empty($subscribers)) {
			// no subscribers to check
			return;
		}
		
		$event = $hook->getParam('event');
		if (!$event instanceof \Elgg\Notifications\NotificationEvent) {
			return;
		}
		
		$object = $event->getObject();
		if (!$object instanceof \ElggComment) {
			return;
		}
		
		/* @var $batch \ElggBatch */
		$batch = elgg_get_entities([
			'type' => 'user',
			'limit' => false,
			'relationship' => CONTENT_SUBSCRIPTIONS_BLOCK,
			'relationship_guid' => $object->container_guid,
			'inverse_relationship' => true,
			'batch' => true,
		]);
		/* @var $user \ElggUser */
		foreach ($batch as $user) {
			if (!isset($subscribers[$user->guid])) {
				continue;
			}
			
			unset($subscribers[$user->guid]);
		}
		
		return $subscribers;
	}
	
	/**
	 * Verify that the subscribed users still have their preferences
	 *
	 * @param \Elgg\Hook $hook 'get', 'subscriptions'
	 *
	 * @return void|array
	 */
	public static function verifySubscribersSettings(\Elgg\Hook $hook) {
		
		$subscribers = $hook->getValue();
		if (empty($subscribers)) {
			// no subscribers to check
			return;
		}
		
		$event = $hook->getParam('event');
		if (!$event instanceof \Elgg\Notifications\NotificationEvent) {
			return;
		}
		
		$object = $event->getObject();
		if (!$object instanceof \ElggComment) {
			return;
		}
		
		foreach ($subscribers as $user_guid => $preferences) {
			$settings = content_subscriptions_get_notification_settings($user_guid);
			if (empty($settings)) {
				unset($subscribers[$user_guid]);
				continue;
			}
			
			$subscribers[$user_guid] = $settings;
		}
		
		return $subscribers;
	}
}
