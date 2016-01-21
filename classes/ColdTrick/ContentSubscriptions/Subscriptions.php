<?php

namespace ColdTrick\ContentSubscriptions;

class Subscriptions {
	
	/**
	 * Add a discussion owner to the notified users
	 *
	 * @param string $hook         the name of the hook
	 * @param stirng $type         the type of the hook
	 * @param array  $return_value the current return value
	 * @param array  $params       supplied values
	 *
	 * @return void|array
	 */
	public static function addDiscussionOwner($hook, $type, $return_value, $params) {
		
		if (empty($params) || !is_array($params)) {
			return;
		}
		
		$event = elgg_extract('event', $params);
		if (!($event instanceof \Elgg\Notifications\Event)) {
			return;
		}
		
		$discussion_reply = $event->getObject();
		if (!($discussion_reply instanceof \ElggDiscussionReply)) {
			return;
		}
		
		$discussion = $discussion_reply->getContainerEntity();
		if (!elgg_instanceof($discussion, 'object', 'groupforumtopic')) {
			return;
		}
		
		$owner = $discussion->getOwnerEntity();
		if (!($owner instanceof \ElggUser)) {
			return;
		}
		
		$user_notification_settings = get_user_notification_settings($owner->getGUID());
		if (empty($user_notification_settings)) {
			// user has no settings, so no notification
			return;
		}
		
		$temp = [];
		foreach ($user_notification_settings as $method => $enabled) {
			if (empty($enabled)) {
				// notification method not enabled
				continue;
			}
			
			$temp[] = $method;
		}
		
		if (empty($temp)) {
			// no enabled notification methods
			return;
		}
		
		$return_value[$owner->getGUID()] = $temp;
		
		return $return_value;
	}
	
	/**
	 * Make sure unsubscribed users don't get notifications based on their group-subscriptions
	 *
	 * @param string $hook         the name of the hook
	 * @param stirng $type         the type of the hook
	 * @param array  $return_value the current return value
	 * @param array  $params       supplied values
	 *
	 * @return void|array
	 */
	public static function removeUnsubscribedGroupMembers($hook, $type, $return_value, $params) {
		
		if (empty($params) || !is_array($params)) {
			return;
		}
		
		if (empty($return_value)) {
			// no subscribers to check
			return;
		}
		
		$event = elgg_extract('event', $params);
		if (!($event instanceof \Elgg\Notifications\Event)) {
			return;
		}
		
		$object = $event->getObject();
		if (!($object instanceof \ElggComment)) {
			return;
		}
		
		$options = [
			'type' => 'user',
			'limit' => false,
			'relationship' => CONTENT_SUBSCRIPTIONS_BLOCK,
			'relationship_guid' => $object->getContainerGUID(),
			'inverse_relationship' => true,
		];
		$batch = new \ElggBatch('elgg_get_entities_from_relationship', $options);
		foreach ($batch as $user) {
			if (!isset($return_value[$user->getGUID()])) {
				continue;
			}
			
			unset($return_value[$user->getGUID()]);
		}
		
		return $return_value;
	}
	
	/**
	 * Verify that the subscribed users still have their preferences
	 *
	 * @param string $hook         the name of the hook
	 * @param stirng $type         the type of the hook
	 * @param array  $return_value the current return value
	 * @param array  $params       supplied values
	 *
	 * @return void|array
	 */
	public static function verifySubscribersSettings($hook, $type, $return_value, $params) {
		
		if (empty($params) || !is_array($params)) {
			return;
		}
		
		if (empty($return_value)) {
			// no subscribers to check
			return;
		}
		
		$event = elgg_extract("event", $params);
		if (!($event instanceof \Elgg\Notifications\Event)) {
			return;
		}
		
		$object = $event->getObject();
		if (!($object instanceof \ElggComment)) {
			return;
		}
		
		foreach ($return_value as $user_guid => $preferences) {
			$settings = content_subscriptions_get_notification_settings($user_guid);
			if (!empty($settings)) {
				$return_value[$user_guid] = $settings;
			} else {
				unset($return_value[$user_guid]);
			}
		}
		
		return $return_value;
	}
}