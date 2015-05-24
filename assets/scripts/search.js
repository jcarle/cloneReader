$.Search = {
	init: function($form) {
		this.$page      = $('.cr-page-home');
		this.$form      = $form;
		var search      = $.url().param('q');

		this.$form.find('input').keyup(function(event) {
			event.stopPropagation();
		});

		this.$form
			.unbind('submit')
			.on('submit', 
			function() {
				var $form  = $(this);
				var $input = $form.find('[name=q]');
				if ($input.val().trim() == '') {
					return false;
				}
				$.hideMobileNavbar();
				//$.goToUrl($form.attr('action') + '?' + $form.serialize());
				cloneReader.changeFilters({ 'search': $input.val().trim() } );
				return false;
			}
		);
			
		if ($.isMobile() == false) { 
			var v = $('.cr-page-search input[name=q]').val();
			$('.cr-page-search input[name=q]').focus().val('').val(v);
		}

		if (search !== undefined) {
			$form.find('[name=q]').val(decodeURIComponent(search));
		}
	},
	
	clearForm: function() {
		this.$form.find('[name=q]').val('');
	}
};
