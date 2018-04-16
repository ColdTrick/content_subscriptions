<?php
/**
 * Convert content subscriptions to the new elgg subscription
 *
 * Run for 5 seconds per request as set by $batch_run_time_in_secs. This includes
 * the engine loading time.
 */

// from engine/start.php
global $START_MICROTIME;
$batch_run_time_in_secs = 5;

if (get_input('upgrade_completed')) {
	// set the upgrade as completed
	$factory = new ElggUpgrade();
	$upgrade = $factory->getUpgradeFromPath('admin/upgrades/content_subscriptions');
	if ($upgrade instanceof ElggUpgrade) {
		$upgrade->setCompleted();
	}

	return true;
}

// Offset is the total amount of errors so far. We skip these
// annotations to prevent them from possibly repeating the same error.
$offset = (int) get_input('offset', 0);
$limit = 25;

$access_status = access_get_show_hidden_status();
access_show_hidden_entities(true);

// don't want any event or plugin hook handlers from plugins to run
$original_events = _elgg_services()->events;
$original_hooks = _elgg_services()->hooks;
_elgg_services()->events = new Elgg\EventsService();
_elgg_services()->hooks = new Elgg\PluginHooksService();

elgg_register_plugin_hook_handler('permissions_check', 'all', 'elgg_override_permissions');
elgg_register_plugin_hook_handler('container_permissions_check', 'all', 'elgg_override_permissions');
_elgg_services()->db->disableQueryCache();

$success_count = 0;
$error_count = 0;

while ((microtime(true) - $START_MICROTIME) < $batch_run_time_in_secs) {
	
	$options = [
		'type' => 'user',
		'relationship' => CONTENT_SUBSCRIPTIONS_SUBSCRIPTION,
		'inverse_relationship' => true,
		'count' => true,
	];
	$count = elgg_get_entities_from_relationship($options);
	
	if (!$count) {
		// no old subscriptions left
		$factory = new ElggUpgrade();
		$upgrade = $factory->getUpgradeFromPath('admin/upgrades/content_subscriptions');
		if ($upgrade instanceof ElggUpgrade) {
			$upgrade->setCompleted();
		}
		
		break;
	}
	
	$options['count'] = false;
	$options['offset'] = $offset;
	$options['limit'] = $limit;
	
	$users = elgg_get_entities_from_relationship($options);
	foreach ($users as $user) {
		$error_counter = 0;
		
		$subscription_options = [
			'relationship' => CONTENT_SUBSCRIPTIONS_SUBSCRIPTION,
			'relationship_guid' => $user->getGUID(),
			'limit' => false,
		];
		
		$batch = new ElggBatch('elgg_get_entities_from_relationship', $subscription_options);
		$batch->setIncrementOffset(false);
		foreach ($batch as $entity) {
			
			// for some reason you can't subscribe
			if (!content_subscriptions_can_subscribe($entity, $user->getGUID())) {
				if (!remove_entity_relationship($user->getGUID(), CONTENT_SUBSCRIPTIONS_SUBSCRIPTION, $entity->getGUID())) {
					$error_counter++;
				}
				
				continue;
			}
			
			// subscribe the new way
			content_subscriptions_subscribe($entity->getGUID(), $user->getGUID());
			
			// remove old link
			if (!remove_entity_relationship($user->getGUID(), CONTENT_SUBSCRIPTIONS_SUBSCRIPTION, $entity->getGUID())) {
				$error_counter++;
			}
		}
		
		if ($error_counter > 0) {
			$error_count++;
		} else {
			$success_count++;
		}
	}
}

access_show_hidden_entities($access_status);

// replace events and hooks
_elgg_services()->events = $original_events;
_elgg_services()->hooks = $original_hooks;
_elgg_services()->db->enableQueryCache();

// Give some feedback for the UI
echo json_encode([
	'numSuccess' => $success_count,
	'numErrors' => $error_count
]);
