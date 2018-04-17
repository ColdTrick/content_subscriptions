<?php
/**
 * All helper functions for this plugin are bundled here
 */

/**
 * Check if the user has a subscription with the content
 *
 * @param int  $entity_guid         the content entity to check
 * @param int  $user_guid           the user to check (defaults to current user)
 * @param bool $return_subscription return the subscription settings
 *
 * @return false|array
 */
function content_subscriptions_check_subscription($entity_guid, $user_guid = 0, $return_subscription = false) {
	
	$entity_guid = (int) $entity_guid;
	$user_guid = (int) $user_guid;
	$return_subscription = (bool) $return_subscription;
	
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if ($entity_guid < 1 || $user_guid < 1) {
		return false;
	}
	
	// check if we didn't block subscription
	if (content_subscriptions_check_block_subscription($entity_guid, $user_guid)) {
		return false;
	}
	
	// special case for discussions
	$group_sub = elgg_call(ELGG_IGNORE_ACCESS, function() use($entity_guid, $user_guid, $return_subscription) {
		$entity = get_entity($entity_guid);
		if (!$entity instanceof ElggDiscussion) {
			return false;
		}
		
		return content_subscriptions_check_notification_settings($entity->getContainerEntity(), $user_guid, $return_subscription);
	});
	if ($group_sub !== false) {
		return $group_sub;
	}
	
	// check entity subscription
	$subs = elgg_get_subscriptions_for_container($entity_guid);
	if (empty($subs)) {
		return false;
	}
	
	if (!isset($subs[$user_guid])) {
		return false;
	}
	
	return $return_subscription ? $subs[$user_guid] : true;
}

/**
 * Subscribe a user to the updates of an entity
 *
 * @param int $entity_guid the content entity to subscribe to
 * @param int $user_guid   the user to subscribe (defaults to current user)
 *
 * @return bool
 */
function content_subscriptions_subscribe($entity_guid, $user_guid = 0) {
	
	$entity_guid = (int) $entity_guid;
	$user_guid = (int) $user_guid;
	
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if ($entity_guid < 1 || $user_guid < 1) {
		return false;
	}
	
	// remove autosubscription block
	remove_entity_relationship($user_guid, CONTENT_SUBSCRIPTIONS_BLOCK, $entity_guid);
	
	$methods = elgg_get_notification_methods();
	if (empty($methods)) {
		return false;
	}
	
	foreach ($methods as $method) {
		elgg_add_subscription($user_guid, $method, $entity_guid);
	}
	
	return true;
}

/**
 * Automaticly subscribe to the updates of an entity if the user didn't block this
 *
 * @param int $entity_guid the content entity to subscribe to
 * @param int $user_guid   the user to subscribe (defaults to current user)
 *
 * @return bool
 */
function content_subscriptions_autosubscribe($entity_guid, $user_guid = 0) {
	
	$entity_guid = (int) $entity_guid;
	$user_guid = (int) $user_guid;
	
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if ($entity_guid < 1 || $user_guid < 1) {
		return false;
	}
	
	// check if the user blocked the subscription
	if (content_subscriptions_check_block_subscription($entity_guid, $user_guid)) {
		return false;
	}
	
	$entity = get_entity($entity_guid);
	if (!$entity instanceof ElggEntity) {
		return false;
	}
	
	// check if this is not the content owner
	if ($entity->owner_guid === $user_guid) {
		return false;
	}
	
	// no, so subscribe
	return content_subscriptions_subscribe($entity_guid, $user_guid);
}

/**
 * Unsubscribe a user from updates and set a flag so auto updates don't recreate the updates
 *
 * @param int $entity_guid the content entity to unsubscribe from
 * @param int $user_guid   the user to unsubscribe (defaults to current user)
 *
 * @return bool
 */
function content_subscriptions_unsubscribe($entity_guid, $user_guid = 0) {
	
	$entity_guid = (int) $entity_guid;
	$user_guid = (int) $user_guid;
	
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if ($entity_guid < 1 || $user_guid < 1) {
		return false;
	}
	
	// check if we have a subscription
	$sub = content_subscriptions_check_subscription($entity_guid, $user_guid, true);
	
	// make sure we can't autosubscribe
	add_entity_relationship($user_guid, CONTENT_SUBSCRIPTIONS_BLOCK, $entity_guid);
	
	// quick return if no subscriptions
	if (empty($sub)) {
		return true;
	}
	
	// remove subscriptions
	foreach ($sub as $method) {
		elgg_remove_subscription($user_guid, $method, $entity_guid);
	}
	
	return true;
}

/**
 * Check if the user gets notifications from the group, based on notification settings
 *
 * @param ElggEntity $container           the container to check (only act on ElggGroups)
 * @param int        $user_guid           the user to check (defaults to current user)
 * @param bool       $return_subscription return the subscription settings
 *
 * @return bool|array
 */
function content_subscriptions_check_notification_settings(ElggEntity $container, $user_guid = 0, $return_subscription = false) {
	static $user_cache;
	
	$user_guid = (int) $user_guid;
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if ($user_guid < 1) {
		return false;
	}
	
	// only check groups
	if (!$container instanceof ElggGroup) {
		return false;
	}
	
	if (!isset($user_cache[$container->guid])) {
		$user_cache[$container->guid] = elgg_get_subscriptions_for_container($container->guid);
	}
	
	if ($return_subscription) {
		return elgg_extract($user_guid, $user_cache[$container->guid], false);
	}
	
	return isset($user_cache[$container->guid][$user_guid]);
}

/**
 * Checks if a user can subscribe to a content item
 *
 * @param ElggEntity $entity    the entity to check
 * @param int        $user_guid the user to check (default: current user)
 *
 * @return bool
 */
function content_subscriptions_can_subscribe(ElggEntity $entity, $user_guid = 0) {
	
	$user_guid = (int) $user_guid;
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if ($user_guid < 1 || !$entity instanceof ElggEntity) {
		return false;
	}
	
	if ($entity->owner_guid === $user_guid) {
		// owner can't subscribe to own content
		return false;
	}
	
	$supported_entity_types = content_subscriptions_get_supported_entity_types();
	if (empty($supported_entity_types)) {
		return false;
	}
	
	$type = $entity->getType();
	if (!isset($supported_entity_types[$type])) {
		return false;
	}
	
	$subtype = $entity->getSubtype();
	if (!empty($subtype)) {
		return in_array($subtype, $supported_entity_types[$type]);
	}
	
	return true;
}

/**
 * Get an array of the supported entity types/subtypes for subscriptions
 *
 * @return array
 */
function content_subscriptions_get_supported_entity_types() {
	$result = [
		'object' => [
			'blog',
			'bookmark',
			'discussion',
			'file',
			'page',
		],
	];
	
	$params = [
		'defaults' => $result,
	];
	
	return elgg_trigger_plugin_hook('entity_types', 'content_subscriptions', $params, $result);
}

/**
 * Get the subscription methods of the user
 *
 * @param int $user_guid the user_guid to check (default: current user)
 *
 * @return array
 */
function content_subscriptions_get_notification_settings($user_guid = 0) {
	static $user_cache = [];
	
	$user_guid = (int) $user_guid;
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if ($user_guid < 1) {
		return [];
	}
	
	if (isset($user_cache[$user_guid])) {
		return $user_cache[$user_guid];
	}
	
	$user_cache[$user_guid] = [];
	$checked = false;
	
	if (elgg_is_active_plugin('notifications')) {
		
		$saved = elgg_get_plugin_user_setting('notification_settings_saved', $user_guid, 'content_subscriptions');
		if (!empty($saved)) {
			$checked = true;
			$settings = elgg_get_plugin_user_setting('notification_settings', $user_guid, 'content_subscriptions');
			
			if (!empty($settings)) {
				$user_cache[$user_guid] = string_to_tag_array($settings);
			}
		}
	}
	
	if ($checked) {
		return $user_cache[$user_guid];
	}
	
	// default elgg settings
	$user = get_user($user_guid);
	if (empty($user)) {
		return $user_cache[$user_guid];
	}
	
	$settings = $user->getNotificationSettings();
	if (empty($settings)) {
		return $user_cache[$user_guid];
	}
	
	$settings = (array) $settings;
	foreach ($settings as $method => $enabled) {
		if (empty($enabled)) {
			continue;
		}
		
		$user_cache[$user_guid][] = $method;
	}
	
	return $user_cache[$user_guid];
}

/**
 * Check if a user has a block relationship with an entity
 *
 * @param int $entity_guid the entity to check
 * @param int $user_guid   the user to check for (default: current user)
 *
 * @return bool
 */
function content_subscriptions_check_block_subscription($entity_guid, $user_guid = 0) {
	static $user_cache = [];
	
	$entity_guid = (int) $entity_guid;
	$user_guid = (int) $user_guid;
	
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if (($entity_guid < 1) || ($user_guid < 1)) {
		return false;
	}
	
	if (isset($user_cache[$user_guid])) {
		return in_array($entity_guid, $user_cache[$user_guid]);
	}
	
	$user_cache[$user_guid] = [];
	
	$relationships = get_entity_relationships($user_guid);
	if (empty($relationships)) {
		return false;
	}
	
	/* @var $relationship ElggRelationship */
	foreach ($relationships as $relationship) {
		if ($relationship->relationship !== CONTENT_SUBSCRIPTIONS_BLOCK) {
			continue;
		}
		
		$user_cache[$user_guid][] = (int) $relationship->guid_two;
	}
	
	return in_array($entity_guid, $user_cache[$user_guid]);
}
