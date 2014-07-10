<?php
?>
//<script>

elgg.provide("elgg.content_subscriptions");

elgg.content_subscriptions.init = function() {
	
	$("form.elgg-form-content-subscriptions-subscribe").on("submit", function() {
		var $form = $(this);
		
		elgg.action($form.attr("action"), {
			data: $form.serialize(),
			success: function(data) {
				$form.find(".content-subscription-toggle").toggle();
			}
		});

		return false;
	});

	$(".elgg-menu-item-content-subscription > a").on("click", function() {

		var $link = $(this);

		elgg.action($link.attr("href"), {
			success: function(data) {
				if ($link.html() == elgg.echo("content_subscriptions:subscribe")) {
					$link.html(elgg.echo("content_subscriptions:unsubscribe"));
				} else {
					$link.html(elgg.echo("content_subscriptions:subscribe"));
				}
			}
		});
		
		return false;
	});
}

//register init hook
elgg.register_hook_handler("init", "system", elgg.content_subscriptions.init);