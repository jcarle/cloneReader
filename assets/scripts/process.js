$.process = {
	init: function() { },
	
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
