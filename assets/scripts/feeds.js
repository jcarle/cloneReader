$.Feeds = {
	init: function() { },
	
	resetAndScanFeed: function(feedId) {
		$.ajax({
			'url': 		base_url + 'feeds/resetAndScanFeed/' + feedId + '/true',
			'data': 	{ },
			'success': 	
				function (result) {	
					$.showWaiting(true);
					location.reload();
				}
		});
	},
	
	saveFeedIcon: function(feedId) {
		$.ajax({
			'url': 		base_url + 'feeds/saveFeedIcon/' + feedId,
			'data': 	{ },
			'success': 	
				function (result) {	
					$.showWaiting(true);
					location.reload();
				}
		});
	},
	
	deleteOldEntriesByFeedId: function(feedId) {
		$(document).crAlert( {
			'msg': 			_msg['Are you sure?'],
			'isConfirm': 	true,
			'callback': 	function() {
				$.ajax({
					'url': 		base_url + 'feeds/deleteOldEntriesByFeedId/' + feedId,
					'data': 	{ },
					'success':	
						function (result) {
							$(document).crAlert( {
								'msg': 			result.result,
								'callback': 	function() {
									$.showWaiting(true);
									location.reload();
								}
							});
						}
				});
			}
		});
	}
};
