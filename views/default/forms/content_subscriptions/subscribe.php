<?php
/**
 * The form content to (un)subscribe to content
 */

$user = elgg_extract("user", $vars);
$entity = elgg_extract("entity", $vars);

$text = elgg_echo("content_subscriptions:subscribe:description");
$submit_text = elgg_echo("content_subscriptions:subscribe");
if (content_subscriptions_check_subscription($entity->getGUID(), $user->getGUID())) {
	$text = elgg_echo("content_subscriptions:unsubscribe:description");
	$submit_text = elgg_echo("content_subscriptions:unsubscribe");
}

echo elgg_view("output/longtext", array("value" => $text));

echo "<div class='elgg-foot center'>";
echo elgg_view("input/hidden", array("name" => "user_guid", "value" => $user->getGUID()));
echo elgg_view("input/hidden", array("name" => "entity_guid", "value" => $entity->getGUID()));
echo elgg_view("input/submit", array("value" => $submit_text));
echo "</div>";
