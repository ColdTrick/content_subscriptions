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
}