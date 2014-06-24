<?php
/**
 * All plugin hook callback functions are bundled in this file
 */

/**
 * Route hook to make sure we can extend the correct sidebar and have some more information
 *
 * @param string $hook         'route'
 * @param string $type         the supported 'correct' page handlers
 * @param array  $return_value supplied params
 * @param null   $params       null
 *
 * @return void
 */
function content_subscriptions_default_route_hook($hook, $type, $return_value, $params) {
	global $CONTENT_SUBSCRIPTIONS_GUID;
	
	if (!empty($return_value) && is_array($return_value)) {
		$page = elgg_extract("segments", $return_value);
		
		switch ($page[0]) {
			case "view":
				if (isset($page[1]) && is_numeric($page[1])) {
					// store the guid of the entity
					$CONTENT_SUBSCRIPTIONS_GUID = $page[1];
					
					// extend the sidebar so we can display info
					elgg_extend_view("page/elements/sidebar", "content_subscriptions/sidebar");
				}
				break;
		}
	}
}

/**
 * Add a subscribe/unsubscribe link to the supported entity types
 *
 * @param string         $hook         'register'
 * @param string         $type         'menu:entity'
 * @param ElggMenuItem[] $return_value the current menu items
 * @param array          $params       supplied params
 *
 * @return ElggMenuItem[]
 */
function content_subscriptions_register_entity_menu_hook($hook, $type, $return_value, $params) {
	
	if (!empty($params) && is_array($params)) {
		$entity = elgg_extract("entity", $params);
		
		if (!empty($entity) && content_subscriptions_can_subscribe($entity)) {
			$text = elgg_echo("content_subscriptions:subscribe");
			if (content_subscriptions_check_subscription($entity->getGUID())) {
				$text = elgg_echo("content_subscriptions:unsubscribe");
			}
			
			$return_value[] = ElggMenuItem::factory(array(
				"name" => "content_subscription",
				"text" => $text,
				"href" => "action/content_subscriptions/subscribe?entity_guid=" . $entity->getGUID(),
				"is_action" => true,
				"priority" => 100
			));
		}
	}
	
	return $return_value;
}