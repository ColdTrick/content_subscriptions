<?php
/**
 * All plugin hook callback functions are bundled in this file
 */

/**
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