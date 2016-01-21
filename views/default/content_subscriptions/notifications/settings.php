<?php

elgg_require_js('content_subscriptions/personal_notifications');

$user = elgg_extract('user', $vars);

$NOTIFICATION_HANDLERS = _elgg_services()->notifications->getMethods();

$notification_settings = content_subscriptions_get_notification_settings($user->getGUID());

$content .= elgg_format_element('td', ['class' => 'namefield'], elgg_view('output/longtext', [
	'value' => elgg_echo('content_subscriptions:settings:description'),
]));

$i = 0;
foreach ($NOTIFICATION_HANDLERS as $method) {
	
	$checkbox_settings = [
		'id' => "content_subscriptions_{$method}_checkbox",
		'name' => "content_subscriptions_{$method}",
		'value' => $method,
		'onclick' => "adjust{$method}('content_subscriptions_{$method}')",
	];
	
	if (in_array($method, $notification_settings)) {
		$checkbox_settings['checked'] = true;
	}
	
	if ($i > 0) {
		$content .= elgg_format_element('td', ['class' => 'spacercolumn'], '&nbsp;');
	}
	
	$content .= elgg_format_element('td', ['class' => "{$method}togglefield"], elgg_view('output/url', [
		'border' => '0',
		'id' => "content_subscriptions_{$method}",
		'class' => "{$method}toggleOff",
		'onclick' => "adjust{$method}_alt('content_subscriptions_{$method}')",
		'text' => elgg_view('input/checkbox', $checkbox_settings),
	]));
	
	$i++;
}

$content .= elgg_format_element('td', [], '&nbsp;');
$content .= elgg_format_element('tr', [], $content);

echo elgg_format_element('table', ['id' => 'content-subscriptions-notification-settings', 'class' => 'hidden'], $content);
