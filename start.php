<?php
/**
 * The main file for this plugin
 */

define("CONTENT_SUBCRIPTIONS_SUBSCRIPTION", "content_subscription");
define("CONTENT_SUBCRIPTIONS_BLOCK", "content_block_subscription");

require_once(dirname(__FILE__) . "/lib/functions.php");
require_once(dirname(__FILE__) . "/lib/events.php");
require_once(dirname(__FILE__) . "/lib/hooks.php");

// register default Elgg events
elgg_register_event_handler("init", "system", "content_subscriptions_init");

/**
 * This function is called when the Elgg system gets initialized
 *
 * @return void
 */
function content_subscriptions_init() {
	
	// register event handlers
	elgg_register_event_handler("create", "object", "content_subscriptions_create_object_handler");
	
	// register plugin hooks
	elgg_register_plugin_hook_handler("route", "discussion", "content_subscriptions_default_route_hook");
	elgg_register_plugin_hook_handler("register", "menu:entity", "content_subscriptions_register_entity_menu_hook");
	
	// register actions
	elgg_register_action("content_subscriptions/subscribe", dirname(__FILE__) . "/actions/subscribe.php");
}
