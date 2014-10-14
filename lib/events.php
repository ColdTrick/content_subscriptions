<?php
/**
 * All event handler callback functions are bundled in this file
 */

/**
 * Make sure we can autosubscribe the user to further updates
 *
 * @param string     $event  "create"
 * @param string     $type   "object"
 * @param ElggObject $object the created annotation
 *
 * @return void
 */
function content_subscriptions_create_object_handler($event, $type, ElggObject $object) {
	
	if (!empty($object) && (elgg_instanceof($object, "object", "discussion_reply") || elgg_instanceof($object, "object", "comment"))) {
		
		$owner = $object->getOwnerEntity();
		$entity = $object->getContainerEntity();
		
		// add auto subscription for this user
		content_subscriptions_autosubscribe($entity->getGUID(), $owner->getGUID());
	}
}

/**
 * Listen to the upgrade event
 *
 * @param string $event  name of the event
 * @param string $type   type of the event
 * @param null   $object supplied object
 *
 * @return void
 */
function content_subscriptions_upgrade_system_handler($event, $type, $object) {
	
	// Upgrade also possible hidden entities. This feature get run
	// by an administrator so there's no need to ignore access.
	$access_status = access_get_show_hidden_status();
	access_show_hidden_entities(true);
	
	// register an upgrade script
	$options = array(
		"type" => "user",
		"relationship" => CONTENT_SUBSCRIPTIONS_SUBSCRIPTION,
		"inverse_relationship" => true,
		"count" => true
	);
	$count = elgg_get_entities_from_relationship($options);
	if ($count) {
		$path = "admin/upgrades/content_subscriptions";
		$upgrade = new ElggUpgrade();
		if (!$upgrade->getUpgradeFromPath($path)) {
			$upgrade->setPath($path);
			$upgrade->title = "Content Subscription upgrade";
			$upgrade->description = "The way content subscriptions are handled has changed. Run this script to make sure all content subscriptions are migrated.";
			
			$upgrade->save();
		}
	}
	
	access_show_hidden_entities($access_status);
}