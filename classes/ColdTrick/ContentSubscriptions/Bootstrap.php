<?php

namespace ColdTrick\ContentSubscriptions;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 */
	public function init() {
		
		// settings
		elgg_extend_view('notifications/settings/other', 'content_subscriptions/notifications/settings');
		
		// register event handlers
		$events = $this->elgg()->events;
		$events->registerHandler('create', 'object', __NAMESPACE__ . '\Comments::createObject');
		$events->registerHandler('create', 'annotation', __NAMESPACE__ . '\Likes::create');
		
		// register plugin hooks
		$hooks = $this->elgg()->hooks;
		$hooks->registerHandler('register', 'menu:entity', __NAMESPACE__ . '\EntityMenu::register');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\Subscriptions::verifySubscribersSettings', 400);
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\Subscriptions::removeUnsubscribedGroupMembers', 999);
		$hooks->registerHandler('response', 'action:notifications/settings', __NAMESPACE__ . '\UserSettings::notificationSettingsSaveAction');
		$hooks->registerHandler('register', 'menu:page', __NAMESPACE__ . '\PageMenu::register');
	}
}
