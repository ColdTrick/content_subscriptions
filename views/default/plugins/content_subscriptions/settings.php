<?php

/* @var $plugin ElggPlugin */
$plugin = elgg_extract('entity', $vars);

// like autosubscibe?
echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('content_subscriptions:settings:likes'),
	'#help' => elgg_echo('content_subscriptions:settings:likes:description'),
	'name' => 'params[likes_autosubscribe]',
	'default' => 'no',
	'value' => 'yes',
	'checked' => $plugin->likes_autosubscribe === 'yes',
	'switch' => true,
]);
