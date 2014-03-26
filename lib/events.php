<?php
/**
 * All event handler callback functions are bundled in this file
 */

/**
 * Check this event for the correct annotation, so subscription notifications can be send out
 *
 * @param string         $event      "create"
 * @param string         $type       "annotation"
 * @param ElggAnnotation $annotation the created annotation
 *
 * @return void
 */
function content_subscriptions_create_annotation_handler($event, $type, ElggAnnotation $annotation) {
	global $NOTIFICATION_HANDLERS;
	global $CONFIG;
	
	if (!empty($annotation) && ($annotation instanceof ElggAnnotation)) {
		// check for the correct annotations
		switch ($annotation->name) {
			case "group_topic_post":
				$annotation_owner = $annotation->getOwnerEntity();
				$entity = $annotation->getEntity();
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
							"e.guid <> " . $annotation_owner->getGUID() // don't notify yourself
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
							AND relationship IN ('" . implode("", "", $methods) . "')
					)";
					
					$options["wheres"][] = $notification_where;
					
					// proccess users
					$users = new ElggBatch("elgg_get_entities_from_relationship", $options);
					
					foreach ($users as $user) {
						// build message
						$default_subject = $CONFIG->register_objects[$entity->getType()][$entity->getSubtype()];
						$string = $default_subject . ": " . $entity->getURL();
						
						// allow the change of body
						$body = elgg_trigger_plugin_hook("notify:annotation:message", $annotation->getSubtype(), array(
							"annotation" => $annotation,
							"to_entity" => $user,
							"method" => "site"), $string);
						if (empty($body) && ($body !== false)) {
							$body = $string;
						}
						
						// allow the change of subject
						$subject = elgg_trigger_plugin_hook("notify:annotation:subject", $annotation->getSubtype(), array(
							"annotation" => $annotation,
							"to_entity" => $user,
							"method" => "site"), $default_subject);
						if (empty($subject)) {
							$subject = $default_subject;
						}
						
						// send message
						if ($body !== false) {
							notify_user($user->getGUID(), $entity->getContainerGUID(), $subject, $body);
						}
					}
				}
				
				// add auto subcription for this user
				content_subscriptions_autosubscribe($entity->getGUID(), $annotation_owner->getGUID());
				
				break;
		}
	}
}