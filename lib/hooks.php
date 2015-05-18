<?php
/**
 * All plugin hook callback functions are bundled in this file
 */

/**
 * Add a subscribe/unsubscribe link to the supported entity types
 *
 * @param string         $hook         "register"
 * @param string         $type         "menu:entity"
 * @param ElggMenuItem[] $return_value the current menu items
 * @param array          $params       supplied params
 *
 * @return ElggMenuItem[]
 */
function content_subscriptions_register_entity_menu_hook($hook, $type, $return_value, $params) {
	
	if (!elgg_is_logged_in()) {
		return $return_value;
	}
	
	if (empty($params) || !is_array($params)) {
		return $return_value;
	}
	
	$entity = elgg_extract("entity", $params);
	if (empty($entity) || !content_subscriptions_can_subscribe($entity)) {
		return $return_value;
	}
	
	$subscribed = false;
	if (content_subscriptions_check_subscription($entity->getGUID())) {
		$subscribed = true;
	}
	
	$methods = content_subscriptions_get_notification_settings();
	if (!empty($methods)) {
		$return_value[] = ElggMenuItem::factory(array(
			"name" => "content_subscription_subscribe",
			"text" => elgg_echo("content_subscriptions:subscribe"),
			"href" => "action/content_subscriptions/subscribe?entity_guid=" . $entity->getGUID(),
			"is_action" => true,
			"priority" => 100,
			"item_class" => $subscribed ? "hidden" : ""
		));
	}
	
	$return_value[] = ElggMenuItem::factory(array(
		"name" => "content_subscription_unsubscribe",
		"text" => elgg_echo("content_subscriptions:unsubscribe"),
		"href" => "action/content_subscriptions/subscribe?entity_guid=" . $entity->getGUID(),
		"is_action" => true,
		"priority" => 101,
		"item_class" => $subscribed ? "" : "hidden"
	));
	
	return $return_value;
}

/**
 * Change the default notification message for comments
 *
 * @param string                          $hook         the name of the hook
 * @param stirng                          $type         the type of the hook
 * @param Elgg_Notifications_Notification $return_value the current return value
 * @param array                           $params       supplied values
 *
 * @return Elgg_Notifications_Notification
 */
function content_subscriptions_prepare_comment_notification($hook, $type, $return_value, $params) {
	
	if (empty($return_value) || !($return_value instanceof \Elgg\Notifications\Notification)) {
		return $return_value;
	}
	
	if (empty($params) || !is_array($params)) {
		return $return_value;
	}
	
	$event = elgg_extract("event", $params);
	if (empty($event) || !($event instanceof \Elgg\Notifications\Event)) {
		return $return_value;
	}
	
	// ignore access for now
	$ia = elgg_set_ignore_access(true);
	
	$comment = $event->getObject();
	$actor = $event->getActor();
	$object = $comment->getContainerEntity();
	$language = elgg_extract("language", $params, get_current_language());
	$recipient = elgg_extract("recipient", $params);
	
	$return_value->subject = elgg_echo("content_subscriptions:create:comment:subject", array($object->title), $language);
	$return_value->body = elgg_echo("content_subscriptions:create:comment:message", array(
		$recipient->name,
		$actor->name,
		$object->title,
		$comment->description,
		$object->getURL(),
	), $language);
	$return_value->summary = elgg_echo("content_subscriptions:create:comment:summary", array($object->title), $language);
	
	// restore access
	elgg_set_ignore_access($ia);
	
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
 * @return array
 */
function content_subscriptions_get_subscriptions_verify_hook($hook, $type, $return_value, $params) {
	
	if (empty($params) || !is_array($params)) {
		return $return_value;
	}
	
	if (empty($return_value)) {
		// no subscribers to check
		return $return_value;
	}
	
	$event = elgg_extract("event", $params);
	if (empty($event) || !($event instanceof \Elgg\Notifications\Event)) {
		return $return_value;
	}
	
	$object = $event->getObject();
	if (empty($object) || (!elgg_instanceof($object, "object", "discussion_reply") && !elgg_instanceof($object, "object", "comment"))) {
		return $return_value;
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

/**
 * Make sure unsubscribed users don't get notifications based on their group-subscriptions
 *
 * @param string $hook         the name of the hook
 * @param stirng $type         the type of the hook
 * @param array  $return_value the current return value
 * @param array  $params       supplied values
 *
 * @return array
 */
function content_subscriptions_get_subscriptions_group_check_hook($hook, $type, $return_value, $params) {
	
	if (empty($params) || !is_array($params)) {
		return $return_value;
	}
	
	if (empty($return_value)) {
		// no subscribers to check
		return $return_value;
	}
	
	$event = elgg_extract("event", $params);
	if (empty($event) || !($event instanceof \Elgg\Notifications\Event)) {
		return $return_value;
	}
	
	$object = $event->getObject();
	if (empty($object) || (!elgg_instanceof($object, "object", "discussion_reply") && !elgg_instanceof($object, "object", "comment"))) {
		return $return_value;
	}
	
	$options = array(
		"type" => "user",
		"limit" => false,
		"relationship" => CONTENT_SUBSCRIPTIONS_BLOCK,
		"relationship_guid" => $object->getContainerGUID(),
		"inverse_relationship" => true
	);
	$batch = new ElggBatch("elgg_get_entities_from_relationship", $options);
	foreach ($batch as $user) {
		unset($return_value[$user->getGUID()]);
	}
	
	return $return_value;
}

/**
 * Save the content subscriptions preferences for the user
 *
 * @param string $hook         the name of the hook
 * @param stirng $type         the type of the hook
 * @param array  $return_value the current return value
 * @param array  $params       supplied values
 *
 * @return void
 */
function content_subscriptions_notifications_settings_save_hook($hook, $type, $return_value, $params) {
	
	$NOTIFICATION_HANDLERS = _elgg_services()->notifications->getMethods();
	if (empty($NOTIFICATION_HANDLERS) || !is_array($NOTIFICATION_HANDLERS)) {
		return;
	}
	
	$user_guid = (int) get_input("guid");
	if (empty($user_guid)) {
		return;
	}
	
	$user = get_user($user_guid);
	if (empty($user) || !$user->canEdit()) {
		return;
	}
	
	$methods = array();
	
	foreach ($NOTIFICATION_HANDLERS as $method) {
		$setting = get_input("content_subscriptions_" . $method);
		
		if (!empty($setting)) {
			$methods[] = $method;
		}
	}
	
	if (!empty($methods)) {
		elgg_set_plugin_user_setting("notification_settings", implode(",", $methods), $user->getGUID(), "content_subscriptions");
	} else {
		elgg_unset_plugin_user_setting("notification_settings", $user->getGUID(), "content_subscriptions");
	}
	
	// set flag for correct fallback behaviour
	elgg_set_plugin_user_setting("notification_settings_saved", "1", $user->getGUID(), "content_subscriptions");
	
}