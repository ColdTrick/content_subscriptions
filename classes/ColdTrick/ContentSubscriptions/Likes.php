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
	 * @param string          $event  the name of the event
	 * @param string          $type   the type of the event
	 * @param \ElggAnnotation $annotation the created annotation
	 *
	 * @return void
	 */
	public static function create($event, $type, $annotation) {
		
		if (!self::autoSubscribe()) {
			// auto subscribe isn't enabled
			return;
		}
		
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
		
		if (!content_subscriptions_can_subscribe($entity, $user->getGUID())) {
			// subscribing isn't allowed for this entity type/subtype
			return;
		}
		
		// auto subscribe to this entity
		content_subscriptions_autosubscribe($entity->getGUID(), $user->getGUID());
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
