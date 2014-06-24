<?php
/*
 * English translation for this plugin
 */

$english = array(
	'content_subscriptions:subscribe' => "Subscribe",
	'content_subscriptions:unsubscribe' => "Unsubscribe",
	
	'content_subscriptions:sidebar:title' => "Stay up-to-date",
	'content_subscriptions:sidebar:owner' => "You're the owner of this content you'll always receive updates.",
	'content_subscriptions:sidebar:notifications' => "You'll already receive updates based on your group notification settings.",
	'content_subscriptions:sidebar:counter' => "%s members receive updates.",
	'content_subscriptions:sidebar:no_subscriptions' => "Nobody will receive updates about this.",
	
	'content_subscriptions:subscribe:description' => "If you wish to receive updates about this content, click Subscribe",
	'content_subscriptions:unsubscribe:description' => "If you no longer wish to receive updates about this content, click Unsubscribe",
	
	'content_subscriptions:generic_comment:subject' => 'New comment on: %s',
	'content_subscriptions:generic_comment:body' => '%s commented on %s:

%s
	
View and comment on the content:
%s',
	
	// actions
	'content_subscriptions:action:subscribe:error:owner' => "You're the owner of the content and can't (un)subscribe for updates",
	'content_subscriptions:action:subscribe:error:subscribe' => "An unknown error occured while subscribing, please try again",
	'content_subscriptions:action:subscribe:error:unsubscribe' => "An unknown error occured while unsubscribing, please try again",
	'content_subscriptions:action:subscribe:success:subscribe' => "You've successfully subscribed to updates of this item",
	'content_subscriptions:action:subscribe:success:unsubscribe' => "You've successfully unsubscribed from updates of this item",
	
);

add_translation("en", $english);
