<?php

namespace ColdTrick\ContentSubscriptions;

class Comments {
	
	/**
	 * Make sure we can autosubscribe the user to further updates
	 *
	 * @param string      $event  the name of the event
	 * @param string      $type   the type of the event
	 * @param \ElggObject $object the created comment
	 *
	 * @return void
	 */
	public static function createObject($event, $type, \ElggObject $object) {
		
		if (!$object instanceof \ElggComment) {
			return;
		}
		
		$owner = $object->getOwnerEntity();
		$entity = $object->getContainerEntity();
	
		// add auto subscription for this user
		content_subscriptions_autosubscribe($entity->getGUID(), $owner->getGUID());
	}
	
	/**
	 * Change the default notification message for comments
	 *
	 * @param string                           $hook         the name of the hook
	 * @param string                           $type         the type of the hook
	 * @param \Elgg\Notifications\Notification $return_value the current return value
	 * @param array                            $params       supplied values
	 *
	 * @return void|\Elgg\Notifications\Notification
	 */
	public static function prepareNotification($hook, $type, $return_value, $params) {
		
		if (!$return_value instanceof \Elgg\Notifications\Notification) {
			return;
		}
		
		$event = elgg_extract('event', $params);
		if (!$event instanceof \Elgg\Notifications\NotificationEvent) {
			return;
		}
		
		// ignore access for now
		$ia = elgg_set_ignore_access(true);
		
		$comment = $event->getObject();
		$actor = $event->getActor();
		$object = $comment->getContainerEntity();
		$language = elgg_extract('language', $params, get_current_language());
		$recipient = elgg_extract('recipient', $params);
		
		$return_value->subject = elgg_echo('content_subscriptions:create:comment:subject', [$object->getDisplayname()], $language);
		$return_value->body = elgg_echo('content_subscriptions:create:comment:message', [
			$recipient->getDisplayname(),
			$actor->getDisplayname(),
			$object->getDisplayname(),
			$comment->description,
			$object->getURL(),
		], $language);
		$return_value->summary = elgg_echo('content_subscriptions:create:comment:summary', [$object->getDisplayname()], $language);
		
		// restore access
		elgg_set_ignore_access($ia);
		
		return $return_value;
	}
}
