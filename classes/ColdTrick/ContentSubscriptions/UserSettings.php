<?php

namespace ColdTrick\ContentSubscriptions;

class UserSettings {
	
	/**
	 * Save the content subscriptions preferences for the user
	 *
	 * @param string $hook         the name of the hook
	 * @param stirng $type         the type of the hook
	 * @param array  $return_value the current return value
	 * @param array  $params       supplied values
	 *
	 * @return void
	 */
	public static function notificationSettingsSaveAction($hook, $type, $return_value, $params) {
		
		$NOTIFICATION_HANDLERS = _elgg_services()->notifications->getMethods();
		if (empty($NOTIFICATION_HANDLERS) || !is_array($NOTIFICATION_HANDLERS)) {
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
		
		$methods = [];
		
		foreach ($NOTIFICATION_HANDLERS as $method) {
			$setting = get_input("content_subscriptions_{$method}");
			
			if (!empty($setting)) {
				$methods[] = $method;
			}
		}
		
		if (!empty($methods)) {
			elgg_set_plugin_user_setting('notification_settings', implode(',', $methods), $user->getGUID(), 'content_subscriptions');
		} else {
			elgg_unset_plugin_user_setting('notification_settings', $user->getGUID(), 'content_subscriptions');
		}
		
		// set flag for correct fallback behaviour
		elgg_set_plugin_user_setting('notification_settings_saved', '1', $user->getGUID(), 'content_subscriptions');
	}
}
