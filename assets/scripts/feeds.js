$.Feeds = {
	init: function() { },
	
	resetAndScanFeed: function(feedId) {
		$.ajax({
			url: base_url + 'feeds/resetAndScanFeed/' + feedId + '/true',
			data: { }
		 }).done(function (result) {	
			$.showWaiting(true);
			location.reload();
		});		
	},
	
	saveFeedIcon: function(feedId) {
		$.ajax({
			url: base_url + 'feeds/saveFeedIcon/' + feedId,
			data: { }
		 }).done(function (result) {	
			$.showWaiting(true);
			location.reload();
		});		
	}	
};
