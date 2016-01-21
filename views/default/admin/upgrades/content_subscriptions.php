<?php

// Upgrade also possible hidden entities. This feature get run
// by an administrator so there's no need to ignore access.
$access_status = access_get_show_hidden_status();
access_show_hidden_entities(true);

$options = [
	'type' => 'user',
	'relationship' => CONTENT_SUBSCRIPTIONS_SUBSCRIPTION,
	'inverse_relationship' => true,
	'count' => true,
];
$count = elgg_get_entities_from_relationship($options);

echo elgg_view('admin/upgrades/view', [
	'count' => $count,
	'action' => 'action/content_subscriptions/upgrade',
]);

access_show_hidden_entities($access_status);
