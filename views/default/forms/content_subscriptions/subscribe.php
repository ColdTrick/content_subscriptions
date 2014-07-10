<?php
/**
 * The form content to (un)subscribe to content
 */

$user = elgg_extract("user", $vars);
$entity = elgg_extract("entity", $vars);

$subscribe_class = "content-subscription-toggle";
$unsubscribe_class = "content-subscription-toggle";

if (content_subscriptions_check_subscription($entity->getGUID(), $user->getGUID())) {
	$subscribe_class .= " hidden";
} else {
	$unsubscribe_class .= " hidden";
}

echo elgg_view("output/longtext", array("value" => elgg_echo("content_subscriptions:subscribe:description"), "class" => $subscribe_class));
echo elgg_view("output/longtext", array("value" => elgg_echo("content_subscriptions:unsubscribe:description"), "class" => $unsubscribe_class));

echo "<div class='elgg-foot center'>";
echo elgg_view("input/hidden", array("name" => "user_guid", "value" => $user->getGUID()));
echo elgg_view("input/hidden", array("name" => "entity_guid", "value" => $entity->getGUID()));
echo elgg_view("input/submit", array("value" => elgg_echo("content_subscriptions:subscribe"), "class" => "elgg-button-submit " . $subscribe_class));
echo elgg_view("input/submit", array("value" => elgg_echo("content_subscriptions:unsubscribe"), "class" => "elgg-button-submit " . $unsubscribe_class));
echo "</div>";
