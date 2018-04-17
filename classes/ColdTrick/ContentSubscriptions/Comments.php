<?php

namespace ColdTrick\ContentSubscriptions;

class Comments {
	
	/**
	 * Make sure we can autosubscribe the user to further updates
	 *
	 * @param \Elgg\Event $event 'create', 'object'
	 *
	 * @return void
	 */
	public static function createObject(\Elgg\Event $event) {
		
		$object = $event->getObject();
		if (!$object instanceof \ElggComment) {
			return;
		}
		
		// add auto subscription for this user
		content_subscriptions_autosubscribe($object->container_guid, $object->owner_guid);
	}
}
