<?php
/**
 * The main file for this plugin
 */

define('CONTENT_SUBSCRIPTIONS_BLOCK', 'content_block_subscription');

require_once(dirname(__FILE__) . '/lib/functions.php');

// register default Elgg events
elgg_register_event_handler('init', 'system', 'content_subscriptions_init');

/**
 * This function is called when the Elgg system gets initialized
 *
 * @return void
 */
function content_subscriptions_init() {
	
	// settings
	elgg_extend_view('notifications/settings/other', 'content_subscriptions/notifications/settings');
	
	// register event handlers
	elgg_register_event_handler('create', 'object', '\ColdTrick\ContentSubscriptions\Comments::createObject');
	elgg_register_event_handler('create', 'annotation', '\ColdTrick\ContentSubscriptions\Likes::create');
	
	// register plugin hooks
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\ContentSubscriptions\EntityMenu::register');
	elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\ContentSubscriptions\Subscriptions::verifySubscribersSettings', 400);
	elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\ContentSubscriptions\Subscriptions::removeUnsubscribedGroupMembers', 999);
	elgg_register_plugin_hook_handler('response', 'action:notifications/settings', '\ColdTrick\ContentSubscriptions\UserSettings::notificationSettingsSaveAction');
}
