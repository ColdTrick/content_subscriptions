<?php
/**
 * Toggle the subscription for a user
 */

$user_guid = (int) get_input('user_guid', elgg_get_logged_in_user_guid());
$entity_guid = (int) get_input('entity_guid');

if (empty($user_guid) || empty($entity_guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$user = get_user($user_guid);
$entity = get_entity($entity_guid);
	
if (empty($user) || empty($entity)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

// user can't be the owner
if ($entity->owner_guid === $user->guid) {
	return elgg_error_response(elgg_echo('content_subscriptions:action:subscribe:error:owner'));
}

// subscribe or unsubscribe
if (content_subscriptions_check_subscription($entity->guid, $user->guid)) {
	// unsubscribe
	if (content_subscriptions_unsubscribe($entity->guid, $user->guid)) {
		return elgg_ok_response('', elgg_echo('content_subscriptions:action:subscribe:success:unsubscribe'));
	}
	
	return elgg_error_response(elgg_echo('content_subscriptions:action:subscribe:error:unsubscribe'));
}

// subscribe
if (content_subscriptions_subscribe($entity->guid, $user->guid)) {
	return elgg_ok_response('', elgg_echo('content_subscriptions:action:subscribe:success:subscribe'));
}

return elgg_error_response(elgg_echo('content_subscriptions:action:subscribe:error:subscribe'));
