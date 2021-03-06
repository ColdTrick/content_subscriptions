<?php

echo elgg_view('advanced_statistics/date_selector');

echo elgg_view('advanced_statistics/elements/chart', [
	'title' => elgg_echo('advanced_statistics:content_subscriptions:subscribers'),
	'id' => 'advanced-statistics-content-subscriptions-subscribers',
	'page' => 'admin_data',
	'section' => 'content_subscriptions',
	'chart' => 'subscribers',
	'date_limited' => true,
]);

echo elgg_view('advanced_statistics/elements/chart', [
	'title' => elgg_echo('advanced_statistics:content_subscriptions:blockers'),
	'id' => 'advanced-statistics-content-subscriptions-blockers',
	'page' => 'admin_data',
	'section' => 'content_subscriptions',
	'chart' => 'blockers',
	'date_limited' => true,
]);
