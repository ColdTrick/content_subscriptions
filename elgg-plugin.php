<?php

require_once(dirname(__FILE__) . '/lib/functions.php');

use ColdTrick\ContentSubscriptions\Bootstrap;

return [
	'bootstrap' => Bootstrap::class,
	'actions' => [
		'content_subscriptions/subscribe' => [],
	],
	'settings' => [
		'likes_autosubscribe' => 'no',
	],
];
