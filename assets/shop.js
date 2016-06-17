'use strict';

jQuery(function ($) {
	$('.custom-checkbox label,.custom-radio label').each(function () {
		this.innerHTML = this.innerHTML.replace(/&nbsp;/g, '');
	});
	$('[data-source]').each(function () {
		var $this = $(this);
		var update = function () {
			$('[data-receiver=\'' + $this.attr('name') + '\']').each(function () {
				$(this).attr($this.data('source'), $this.val());
			});
		};
		$this.change(update);
		$(window).on('ajaxUpdateComplete', update);
		update();
	});
});
