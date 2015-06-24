<?php
/**
 * All helper functions form this plugin are bundled here
 */

/**
 * Check if the user has a subscription with the content
 *
 * @param int  $entity_guid         the content entity to check
 * @param int  $user_guid           the user to check (defaults to current user)
 * @param bool $return_subscription return the subscription settings
 *
 * @return bool|array
 */
function content_subscriptions_check_subscription($entity_guid, $user_guid = 0, $return_subscription = false) {
	
	$entity_guid = sanitise_int($entity_guid, false);
	$user_guid = sanitise_int($user_guid, false);
	
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if (empty($entity_guid) || empty($user_guid)) {
		return false;
	}
	
	// check if we didn't block subscription
	if (content_subscriptions_check_block_subscription($entity_guid, $user_guid)) {
		return false;
	}
	
	// special case for discussions
	$ia = elgg_set_ignore_access(true);
	$entity = get_entity($entity_guid);
	
	if (elgg_instanceof($entity, "object", "groupforumtopic")) {
		$group_sub = content_subscriptions_check_notification_settings($entity->getContainerEntity(), $user_guid, $return_subscription);
		
		if ($group_sub) {
			elgg_set_ignore_access($ia);
			return $group_sub;
		}
	}
	elgg_set_ignore_access($ia);
	
	// check entity subscription
	$subs = elgg_get_subscriptions_for_container($entity_guid);
	if (empty($subs)) {
		return false;
	}
	
	if (!isset($subs[$user_guid])) {
		return false;
	}
	
	if ($return_subscription) {
		return $subs[$user_guid];
	}
	
	return true;
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
	$entity_guid = sanitise_int($entity_guid, false);
	$user_guid = sanitise_int($user_guid, false);
	
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	// remove autosubscription block
	remove_entity_relationship($user_guid, CONTENT_SUBSCRIPTIONS_BLOCK, $entity_guid);
	
	$notification_services = _elgg_services()->notifications->getMethods();
	if (empty($notification_services)) {
		return false;
	}
	
	foreach ($notification_services as $service) {
		elgg_add_subscription($user_guid, $service, $entity_guid);
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
	$result = false;
	
	$entity_guid = sanitise_int($entity_guid, false);
	$user_guid = sanitise_int($user_guid, false);
	
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	// check if the user blocked the subscription
	if (!content_subscriptions_check_block_subscription($entity_guid, $user_guid)) {
		$entity = get_entity($entity_guid);
		
		// check if this is not the content owner
		if ($entity->getOwnerGUID() != $user_guid) {
			// no, so subscribe
			$result = content_subscriptions_subscribe($entity_guid, $user_guid);
		}
	}
	
	return $result;
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
	
	$entity_guid = sanitise_int($entity_guid, false);
	$user_guid = sanitise_int($user_guid, false);
	
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if (empty($entity_guid) || empty($user_guid)) {
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
	foreach ($sub as $service) {
		elgg_remove_subscription($user_guid, $service, $entity_guid);
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
 * @return bool
 */
function content_subscriptions_check_notification_settings(ElggEntity $container, $user_guid = 0, $return_subscription = false) {
	static $user_cache;
	
	$user_guid = sanitise_int($user_guid, false);
	
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	// only check groups
	if (!empty($container) && elgg_instanceof($container, "group") && !empty($user_guid)) {
		
		if (!isset($user_cache[$container->getGUID()])) {
			$user_cache[$container->getGUID()] = elgg_get_subscriptions_for_container($container->getGUID());
		}
		
		if ($return_subscription) {
			return $user_cache[$container->getGUID()][$user_guid];
		} else {
			return isset($user_cache[$container->getGUID()][$user_guid]);
		}
	}
	
	return false;
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
	$result = false;
	
	$user_guid = sanitise_int($user_guid, false);
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if (!empty($user_guid) && !empty($entity) && elgg_instanceof($entity)) {
		
		if ($entity->getOwnerGUID() != $user_guid) {
			$supported_entity_types = content_subscriptions_get_supported_entity_types();
			
			if (!empty($supported_entity_types)) {
				$type = $entity->getType();
				
				if (isset($supported_entity_types[$type])) {
					$subtype = $entity->getSubtype();
					if (!empty($subtype)) {
						$result = in_array($subtype, $supported_entity_types[$type]);
					} else {
						$result = true;
					}
				}
			}
		}
	}
	
	return $result;
}

/**
 * Get an array of the supported entity types/subtypes for subscriptions
 *
 * @return array
 */
function content_subscriptions_get_supported_entity_types() {
	$result = array(
		"object" => array(
			"groupforumtopic",
			"blog",
			"file",
			"page_top",
			"page",
			"bookmark"
		)
	);
	
	$params = array(
		"defaults" => $result
	);
	
	return elgg_trigger_plugin_hook("entity_types", "content_subscriptions", $params, $result);
}

/**
 * Get the subscription methods of the user
 *
 * @param int $user_guid the user_guid to check (default: current user)
 *
 * @return array
 */
function content_subscriptions_get_notification_settings($user_guid = 0) {
	static $user_cache;
	
	$user_guid = sanitise_int($user_guid, false);
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if (empty($user_guid)) {
		return array();
	}
	
	if (!isset($user_cache)) {
		$user_cache = array();
	}
	
	if (!isset($user_cache[$user_guid])) {
		$user_cache[$user_guid] = array();
		$checked = false;
		
		if (elgg_is_active_plugin("notifications")) {
			
			$saved = elgg_get_plugin_user_setting("notification_settings_saved", $user_guid, "content_subscriptions");
			if (!empty($saved)) {
				$checked = true;
				$settings = elgg_get_plugin_user_setting("notification_settings", $user_guid, "content_subscriptions");
				
				if (!empty($settings)) {
					$user_cache[$user_guid] = string_to_tag_array($settings);
				}
			}
		}
		
		if (!$checked) {
			// default elgg settings
			$settings = get_user_notification_settings($user_guid);
			
			if (!empty($settings)) {
				$settings = (array) $settings;
				
				foreach ($settings as $method => $value) {
					if (!empty($value)) {
						$user_cache[$user_guid][] = $method;
					}
				}
			}
		}
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
	static $user_cache;
	
	$entity_guid = sanitise_int($entity_guid, false);
	$user_guid = sanitise_int($user_guid, false);
	
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if (empty($entity_guid) || empty($user_guid)) {
		return false;
	}
	
	if (!isset($user_cache)) {
		$user_cache = array();
	}
	
	if (!isset($user_cache[$user_guid])) {
		$user_cache[$user_guid] = array();
		
		$relationships = get_entity_relationships($user_guid);
		if (!empty($relationships)) {
			foreach ($relationships as $relationship) {
				if ($relationship->relationship === CONTENT_SUBSCRIPTIONS_BLOCK) {
					$user_cache[$user_guid][] = (int) $relationship->guid_two;
				}
			}
		}
	}
	
	return in_array($entity_guid, $user_cache[$user_guid]);
}
