<?php
/**
 * All event handler callback functions are bundled in this file
 */

/**
 * Check this event for the correct annotation, so subscription notifications can be send out
 *
 * @param string         $event      "create"
 * @param string         $type       "object"
 * @param ElggAnnotation $annotation the created annotation
 *
 * @return void
 */
function content_subscriptions_create_object_handler($event, $type, ElggObject $object) {
	$NOTIFICATION_HANDLERS = _elgg_services()->notifications->getMethods();
	
	if (!empty($object) && (($object instanceof ElggDiscussionReply) || ($object instanceof ElggComment))) {
		
		$owner = $object->getOwnerEntity();
		$entity = $object->getContainerEntity();
		$entity_owner = $entity->getOwnerEntity();
		
		// only notify on non private entities
		if ($entity->access_id != ACCESS_PRIVATE) {
			// get interested users
			$options = array(
				"type" => "user",
				"limit" => false,
				"relationship" => CONTENT_SUBCRIPTIONS_SUBSCRIPTION,
				"relationship_guid" => $entity->getGUID(),
				"inverse_relationship" => true,
				"wheres" => array(
					"e.guid <> " . $entity_owner->getGUID(), // owner get notified by other means
					"e.guid <> " . $owner->getGUID() // don't notify yourself
				)
			);
			
			// exclude group notification subscribers
			$methods = array();
			foreach ($NOTIFICATION_HANDLERS as $method => $foo) {
				$methods[] = "notify" . $method;
			}
			
			$notification_where = "e.guid NOT IN (
				SELECT guid_one
				FROM " . elgg_get_config("dbprefix") . "entity_relationships
				WHERE guid_two = " . $entity->getContainerGUID() . "
				AND relationship IN ('" . implode("", $methods) . "')
			)";
			
			$options["wheres"][] = $notification_where;
			
			// check access limitations
			switch ($entity->access_id) {
				case ACCESS_FRIENDS:
					// this shouldn't happen, so do nothing
					break;
				case ACCESS_LOGGED_IN:
				case ACCESS_PUBLIC:
					// all users are allowed
					break;
				default:
					// this is an ACL
					$acl_members = get_members_of_access_collection($entity->access_id, true);
					
					if (!empty($acl_members)) {
						$options["wheres"][] = "(e.guid IN (" . implode(",", $acl_members) . "))";
					}
					break;
			}
			
			// build message
			if ($object instanceof ElggDiscussionReply) {
				$subject = elgg_echo("discussion:reply:notify:subject", array($entity->title));
				$body = elgg_echo("discussion:reply:notify:body", array(
					$owner->name,
					$entity->title,
					$entity->getContainerEntity()->name,
					$object->description,
					$entity->getURL()
				));
			} else {
				$subject = elgg_echo("content_subscriptions:generic_comment:subject", array($entity->title));
				$body = elgg_echo("content_subscriptions:generic_comment:body", array(
					$owner->name,
					$entity->title,
					$object->description,
					$entity->getURL()
				));
			}
			
			// proccess users
			$users = new ElggBatch("elgg_get_entities_from_relationship", $options);
			foreach ($users as $user) {
				// send message
				notify_user($user->getGUID(), $entity->getContainerGUID(), $subject, $body);
			}
		}
		
		// add auto subcription for this user
		content_subscriptions_autosubscribe($entity->getGUID(), $owner->getGUID());
	}
}