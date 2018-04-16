<?php
/**
 * Toggle the subscription for a user
 */

$user_guid = (int) get_input("user_guid", elgg_get_logged_in_user_guid());
$entity_guid = (int) get_input("entity_guid");

if (empty($user_guid) || empty($entity_guid)) {
	register_error(elgg_echo("InvalidParameterException:MissingParameter"));
	forward(REFERER);
}

$user = get_user($user_guid);
$entity = get_entity($entity_guid);
	
if (empty($user) || empty($entity)) {
	register_error(elgg_echo("InvalidParameterException:NoEntityFound"));
	forward(REFERER);
}

// user cant be the owner
if ($entity->getOwnerGUID() == $user->getGUID()) {
	register_error(elgg_echo("content_subscriptions:action:subscribe:error:owner"));
	forward(REFERER);
}

// subscribe or unsubscribe
if (content_subscriptions_check_subscription($entity->getGUID(), $user->getGUID())) {
	// unsubscribe
	if (content_subscriptions_unsubscribe($entity->getGUID(), $user->getGUID())) {
		system_message(elgg_echo("content_subscriptions:action:subscribe:success:unsubscribe"));
	} else {
		register_error(elgg_echo("content_subscriptions:action:subscribe:error:unsubscribe"));
	}
} else {
	// subscribe
	if (content_subscriptions_subscribe($entity->getGUID(), $user->getGUID())) {
		system_message(elgg_echo("content_subscriptions:action:subscribe:success:subscribe"));
	} else {
		register_error(elgg_echo("content_subscriptions:action:subscribe:error:subscribe"));
	}
}

forward(REFERER);