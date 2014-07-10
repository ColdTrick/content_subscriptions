<?php
/**
 * All helper functions form this plugin are bundled here
 */

/**
 * Check if the user has a subscription with the content
 *
 * @param int $entity_guid the content entity to check
 * @param int $user_guid   the user to check (defaults to current user)
 *
 * @return bool
 */
function content_subscriptions_check_subscription($entity_guid, $user_guid = 0) {
	$result = false;
	
	$entity_guid = sanitise_int($entity_guid, false);
	$user_guid = sanitise_int($user_guid, false);
	
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if (!empty($user_guid) && !empty($entity_guid)) {
		$result = check_entity_relationship($user_guid, CONTENT_SUBCRIPTIONS_SUBSCRIPTION, $entity_guid);
	}
	
	return $result;
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
	
	if (!content_subscriptions_check_subscription($entity_guid, $user_guid)) {
		// remove the block flag
		remove_entity_relationship($user_guid, CONTENT_SUBCRIPTIONS_BLOCK, $entity_guid);
		
		// add subscription
		$result = add_entity_relationship($user_guid, CONTENT_SUBCRIPTIONS_SUBSCRIPTION, $entity_guid);
	} else {
		// already subscribed
		$result = true;
	}
	
	return $result;
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
	if (!check_entity_relationship($user_guid, CONTENT_SUBCRIPTIONS_BLOCK, $entity_guid)) {
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
	$result = false;
	
	$entity_guid = sanitise_int($entity_guid, false);
	$user_guid = sanitise_int($user_guid, false);
	
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if (!empty($user_guid) && !empty($entity_guid)) {
		// remove subscription
		$result = remove_entity_relationship($user_guid, CONTENT_SUBCRIPTIONS_SUBSCRIPTION, $entity_guid);
		
		// add block subscription
		$result = $result && add_entity_relationship($user_guid, CONTENT_SUBCRIPTIONS_BLOCK, $entity_guid);
	}
	
	return $result;
}

/**
 * Check if the user gets notifications from the group, based on notification settings
 *
 * @param ElggEntity $container the container to check (only act on ElggGroups)
 * @param int        $user_guid the user to check (defaults to current user)
 *
 * @return bool
 */
function content_subscriptions_check_notification_settings(ElggEntity $container, $user_guid = 0) {
	static $NOTIFICATION_HANDLERS;
	static $user_cache;
	
	if (!isset($NOTIFICATION_HANDLERS)) {
		$NOTIFICATION_HANDLERS = _elgg_services()->notifications->getMethods();
	}
	
	$user_guid = sanitise_int($user_guid, false);
	
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	// only check groups
	if (!empty($container) && elgg_instanceof($container, "group") && !empty($user_guid)) {
		
		if (!isset($user_cache[$container->getGUID()])) {
			$user_cache[$container->getGUID()] = array();
		}
		
		if (!isset($user_cache[$container->getGUID()][$user_guid])) {
			$user_cache[$container->getGUID()][$user_guid] = false;
			
			// check all notification handlers, if one is selected return true
			foreach ($NOTIFICATION_HANDLERS as $method => $foo) {
				if (check_entity_relationship($user_guid, "notify" . $method, $container->getGUID())) {
					$user_cache[$container->getGUID()][$user_guid] = true;
					break;
				}
			}
		}
		
		return $user_cache[$container->getGUID()][$user_guid];
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
		$container = $entity->getContainerEntity();
		if (($entity->getOwnerGUID() != $user_guid) && (empty($container) || !content_subscriptions_check_notification_settings($container, $user_guid))) {
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
