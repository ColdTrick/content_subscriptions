<?php

namespace ColdTrick\ContentSubscriptions;

use Elgg\Http\OkResponse;

class UserSettings {
	
	/**
	 * Save the content subscriptions preferences for the user
	 *
	 * @param \Elgg\Hook $hook 'response', 'action:notifications/settings'
	 *
	 * @return void
	 */
	public static function notificationSettingsSaveAction(\Elgg\Hook $hook) {
		
		$response = $hook->getValue();
		if (!$response instanceof OkResponse) {
			// something went wrong in the action, bailout
			return;
		}
		
		$methods = elgg_get_notification_methods();
		if (empty($methods) || !is_array($methods)) {
			return;
		}
		
		$user_guid = (int) get_input('guid');
		if (empty($user_guid)) {
			return;
		}
		
		$user = get_user($user_guid);
		if (empty($user) || !$user->canEdit()) {
			return;
		}
		
		$selected_methods = (array) get_input('content_subscriptions', []);
		$valid_methods = array_intersect($selected_methods, $methods);
		
		if (!empty($valid_methods)) {
			elgg_set_plugin_user_setting('notification_settings', implode(',', $methods), $user->guid, 'content_subscriptions');
		} else {
			elgg_unset_plugin_user_setting('notification_settings', $user->guid, 'content_subscriptions');
		}
		
		// set flag for correct fallback behaviour
		elgg_set_plugin_user_setting('notification_settings_saved', '1', $user->guid, 'content_subscriptions');
	}
}
