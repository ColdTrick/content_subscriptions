<?php
/**
 * Display subscription options to the user
 */
global $CONTENT_SUBSCRIPTIONS_GUID;
if (empty($CONTENT_SUBSCRIPTIONS_GUID)) {
	return true;
}

$entity = get_entity($CONTENT_SUBSCRIPTIONS_GUID);
if (empty($entity)) {
	return true;
}

$user = elgg_get_logged_in_user_entity();

// create title
$title = elgg_echo("content_subscriptions:sidebar:title");

// create content
$content = "";
// check if the user is the owner
if (!empty($user)) {
	if ($user->getGUID() == $entity->getOwnerGUID()) {
		// user can't (un)subscribe because is owner
		$content .= "<div>" . elgg_echo("content_subscriptions:sidebar:owner") . "</div>";
	} elseif (content_subscriptions_check_notification_settings($entity->getContainerEntity(), $user->getGUID())) {
		// user gets notifications based on group settings
		$content .= "<div>" . elgg_echo("content_subscriptions:sidebar:notifications") . "</div>";
	} else {
		// user can subscribe
		$body_vars = array(
			"user" => $user,
			"entity" => $entity
		);
		$content .= elgg_view_form("content_subscriptions/subscribe", array(), $body_vars);
	}
}

// show a counter of the subscriptions
$counter = $entity->countEntitiesFromRelationship(CONTENT_SUBCRIPTIONS_SUBSCRIPTION, true);
if ($counter > 0) {
	$content .= "<div class='mtm'>" . elgg_echo("content_subscriptions:sidebar:counter", array($counter)) . "</div>";
} else {
	$content .= "<div class='mtm'>" . elgg_echo("content_subscriptions:sidebar:no_subscriptions") . "</div>";
}

// show all information
echo elgg_view_module("aside", $title, $content);