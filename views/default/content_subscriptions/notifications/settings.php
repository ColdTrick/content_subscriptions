<?php
/**
 * Extends 'notifications/settings/other' to set content subscriptions
 *
 * @uses $vars['user'] Subscriber
 */

$user = elgg_extract('user', $vars);
if (!$user instanceof ElggUser) {
	return;
}


$methods = elgg_get_notification_methods();
if (empty($methods)) {
	return;
}

$method_options = [];
foreach ($methods as $method) {
	$label = elgg_echo("notification:method:$method");
	$method_options[$label] = $method;
}

$notification_settings = content_subscriptions_get_notification_settings($user->guid);

$desc = elgg_format_element('div', ['class' => 'elgg-subscription-description'], elgg_echo('content_subscriptions:settings:description'));
$field = elgg_view_field([
	'#type' => 'checkboxes',
	'#class' => 'elgg-subscription-methods',
	'name' => 'content_subscriptions',
	'options' => $method_options,
	'default' => false,
	'value' => $notification_settings,
	'align' => 'horizontal',
]);

echo elgg_format_element('div', ['class' => 'elgg-subscription-record'], $desc . $field);
