<?php

$plugin = elgg_extract('entity', $vars);

$noyes_options = [
	'no' => elgg_echo('option:no'),
	'yes' => elgg_echo('option:yes'),
];

// like autosubscibe?
echo '<div>';
echo '<label>' . elgg_echo('content_subscriptions:settings:likes');
echo elgg_view('input/select', [
	'name' => 'params[likes_autosubscribe]',
	'value' => $plugin->likes_autosubscribe,
	'options_values' => $noyes_options,
	'class' => 'mls',
]);
echo '</label><br />';
echo elgg_format_element('div', ['class' => 'elgg-subtext'], elgg_echo('content_subscriptions:settings:likes:description'));
echo '</div>';
