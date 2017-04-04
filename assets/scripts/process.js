$.process = {
	init: function() {
		$('.cr-page-process [data-toggle="popover"]').popover();
	},

	submit: function(url) {
		this.ajax = $.ajax({
			'url':    url,
			'async':  true,
			'success':
				function(response) {
					$.hasAjaxDefaultAction(response);
				}
		});
	}
};

