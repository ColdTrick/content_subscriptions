<?php
/**
 * The main file for this plugin
 */

define('CONTENT_SUBSCRIPTIONS_SUBSCRIPTION', 'content_subscription');
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
	
	// JS
	elgg_extend_view('js/elgg', 'js/content_subscriptions/site.js');
	
	// settings
	elgg_extend_view('notifications/subscriptions/personal', 'content_subscriptions/notifications/settings');
	
	// register event handlers
	elgg_register_event_handler('create', 'object', '\ColdTrick\ContentSubscriptions\Comments::createObject');
	elgg_register_event_handler('create', 'annotation', '\ColdTrick\ContentSubscriptions\Likes::create');
	elgg_register_event_handler('upgrade', 'system', '\ColdTrick\ContentSubscriptions\Upgrade::registerScript');
	
	elgg_register_notification_event('object', 'comment');
	elgg_register_plugin_hook_handler('prepare', 'notification:create:object:comment', '\ColdTrick\ContentSubscriptions\Comments::prepareNotification');
	
	// register plugin hooks
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\ContentSubscriptions\EntityMenu::register');
	elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\ContentSubscriptions\Subscriptions::verifySubscribersSettings', 400);
	elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\ContentSubscriptions\Subscriptions::addDiscussionOwner');
	elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\ContentSubscriptions\Subscriptions::removeUnsubscribedGroupMembers', 999);
	elgg_register_plugin_hook_handler('action', 'notificationsettings/save', '\ColdTrick\ContentSubscriptions\UserSettings::notificationSettingsSaveAction');
	
	// register actions
	elgg_register_action('content_subscriptions/subscribe', dirname(__FILE__) . '/actions/subscribe.php');
	elgg_register_action('content_subscriptions/upgrade', dirname(__FILE__) . '/actions/upgrade.php', 'admin');
	
}
