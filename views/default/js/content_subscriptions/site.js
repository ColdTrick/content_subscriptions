// Content subscriptions JS
elgg.provide('elgg.content_subscriptions');

elgg.content_subscriptions.init = function() {

	elgg.ui.registerTogglableMenuItems('content-subscription-subscribe', 'content-subscription-unsubscribe');
	
	$('form.elgg-form-content-subscriptions-subscribe').on('submit', function() {
		var $form = $(this);
		
		elgg.action($form.attr('action'), {
			data: $form.serialize(),
			success: function(data) {
				$form.find('.content-subscription-toggle').toggle();
			}
		});

		return false;
	});
};

//register init hook
elgg.register_hook_handler('init', 'system', elgg.content_subscriptions.init);
