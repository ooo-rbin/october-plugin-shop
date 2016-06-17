'use strict';

jQuery(function ($) {
	$.request('onChartInformer', {
		success: function (data) {
			data[data.selector] = data.content;
			this.success(data);
		},
		error: function (jqXHR) {
			if (jqXHR.responseJSON && jqXHR.status == 404) {
				var data = jqXHR.responseJSON;
				data[data.selector] = data.content;
				this.success(data);
			} else {
				this.error(jqXHR);
			}
		}
	});
	$(window).on('ajaxUpdate', function (event, context, data) {
		var selector = (data.selector) ? $(data.selector) : false;
		var message = (data.message) ? data.message : false;
		var icon = (data.icon) ? data.icon : false;
		if (message && selector) {
			selector.tooltip({
				html: true,
				placement: 'bottom',
				trigger: 'manual',
				title: function () {
					return ((icon) ? '<i class="fa fa-' + icon + '" aria-hidden="true"></i> ' : '') + message;
				}
			}).tooltip('show');
			window.setTimeout(function () {
				selector.tooltip('hide');
			}, 3000);
		}
		if (selector) {
			selector.find('.popover-handler').popover({
				html: true,
				trigger: 'hover',
				placement: 'bottom',
				container: 'body>.bg-white',
				content: function () {
					return selector.find('.popover-content').clone().removeClass('hidden');
				}
			});
		}
	});
	$('[data-hover="popover"]').each(function () {
		var $this = $(this);
		$this.popover({
			html: true,
			trigger: 'hover',
			container: 'body>.bg-white',
			template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content nopad"></div></div>',
			content: function () {
				return $this.find('.popover-content').clone().removeClass('hidden');
			}
		})
	});
});