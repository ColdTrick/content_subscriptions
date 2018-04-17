<?php

namespace ColdTrick\ContentSubscriptions;

class Likes {
	
	/**
	 * @var bool Automaticly subscribe on like
	 */
	protected static $autosubscribe;
	
	/**
	 * Listen to the creation of an annotation, if Like check auto subscribe
	 *
	 * @param \Elgg\Event $event 'create', 'annotation'
	 *
	 * @return void
	 */
	public static function create(\Elgg\Event $event) {
		
		if (!self::autoSubscribe()) {
			// auto subscribe isn't enabled
			return;
		}
		
		$annotation = $event->getObject();
		if (!$annotation instanceof \ElggAnnotation) {
			// not an annotation
			return;
		}
		
		if ($annotation->name !== 'likes') {
			// not likes
			return;
		}
		
		$entity = $annotation->getEntity();
		if (!$entity instanceof \ElggEntity) {
			return;
		}
		
		$user = $annotation->getOwnerEntity();
		if (!$user instanceof \ElggUser) {
			return;
		}
		
		if (!content_subscriptions_can_subscribe($entity, $user->guid)) {
			// subscribing isn't allowed for this entity type/subtype
			return;
		}
		
		// auto subscribe to this entity
		content_subscriptions_autosubscribe($entity->guid, $user->guid);
	}
	
	/**
	 * Is auto subscribe enabled for Likes
	 *
	 * @return bool
	 */
	protected static function autoSubscribe() {
		
		if (isset(self::$autosubscribe)) {
			return self::$autosubscribe;
		}
		
		self::$autosubscribe = false;
		$setting = elgg_get_plugin_setting('likes_autosubscribe', 'content_subscriptions');
		if ($setting === 'yes') {
			self::$autosubscribe = true;
		}
		
		return self::$autosubscribe;
	}
}
