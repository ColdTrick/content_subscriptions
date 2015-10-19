<?php
/**
 * The main file for this plugin
 */

define("CONTENT_SUBSCRIPTIONS_SUBSCRIPTION", "content_subscription");
define("CONTENT_SUBSCRIPTIONS_BLOCK", "content_block_subscription");

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
	
	// JS
	elgg_extend_view("js/elgg", "js/content_subscriptions/site");
	
	// settings
	elgg_extend_view("notifications/subscriptions/personal", "content_subscriptions/notifications/settings");
	
	// register event handlers
	elgg_register_event_handler("create", "object", "content_subscriptions_create_object_handler");
	elgg_register_event_handler("create", "annotation", array('ColdTrick\ContentSubscriptions\Likes', 'create'));
	elgg_register_event_handler("upgrade", "system", "content_subscriptions_upgrade_system_handler");
	
	elgg_register_notification_event("object", "comment");
	elgg_register_plugin_hook_handler("prepare", "notification:create:object:comment", "content_subscriptions_prepare_comment_notification");
	
	// register plugin hooks
	elgg_register_plugin_hook_handler("register", "menu:entity", "content_subscriptions_register_entity_menu_hook");
	elgg_register_plugin_hook_handler("get", "subscriptions", "content_subscriptions_get_subscriptions_verify_hook", 400);
	elgg_register_plugin_hook_handler("get", "subscriptions", array('ColdTrick\ContentSubscriptions\Subscriptions', 'addDiscussionOwner'));
	elgg_register_plugin_hook_handler("get", "subscriptions", "content_subscriptions_get_subscriptions_group_check_hook", 999);
	elgg_register_plugin_hook_handler("action", "notificationsettings/save", "content_subscriptions_notifications_settings_save_hook");
	
	// register actions
	elgg_register_action("content_subscriptions/subscribe", dirname(__FILE__) . "/actions/subscribe.php");
	elgg_register_action("content_subscriptions/upgrade", dirname(__FILE__) . "/actions/upgrade.php", "admin");
	
}
