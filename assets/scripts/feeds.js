$.Feeds = {
	init: function() { },
	
	scanFeed: function(feedId) {
		$.ajax({
			url: base_url + 'feeds/scan/' + feedId,
			data: { }
		 }).done(function (result) {	
			location.reload();
		});		
	}
};
