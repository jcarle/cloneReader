$.Profile = {
	init: function() {
		this.$page	= $('.cr-page-profile');
		this.$page.data('notRefresh', true);
		
		this.aTabs 	= this.$page.find('.userProfile li');
		for (var i=0; i<this.aTabs.length; i++) {
			var tab = this.aTabs[i];

			this.$page.find(tab).find('a').click($.proxy(
				function(event) {
					var $link 		= $(event.target);
					var $content 	= this.$page.find($link.attr('href'));
					this.loadTab($content.data('controller'), $content);
				}
			, this));
		}
		
		this.$page.find(this.aTabs[0]).find('a').click();
	},
	
	loadTab: function(controller, $content) {
		if ($content.children().length > 0) {
			this.$page.find('.pageTitle h2').text( $content.find('.panel-heading').text() );
			return;
		}
		
		$.ajax( {
			'type': 		'get', 
			'url':			controller,
			'success':		
				$.proxy(
					function (response) {
						if ($.hasAjaxDefaultAction(response) == true) { return; }

						$content.children().remove();
						var $form  = $(document).crForm('renderAjaxForm', response['result']['form'], $content);
						this.$page.find('.pageTitle h2').text( $content.find('.panel-heading').text() );
					}
				, this),
		});
	}
};
