// Content subscriptions personal JS
define(['jquery'], function($) {
	
	var cs_content = $('#content-subscriptions-notification-settings tr:first').html();

	$('#notificationstable tr:last').after('<tr>' + cs_content + '</tr>');
	$('#content-subscriptions-notification-settings').remove();
	
});
