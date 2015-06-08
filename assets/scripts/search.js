/*
TODO: borrar este archivo
$.Search = {
	init: function($form) {
		this.$page   = $('.cr-page-home');
		this.$form   = $form;
		this.$input  = this.$form.find('[name=q]');
		
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

				cloneReader.changeFilters({ 'search': $form.find('[name=q]').val().trim() } );
				return false;
			}
		);
		
		this.$form.find('.fa-times').parent()
			.css( { 'cursor': 'pointer', 'color': '#555555' } )
			.click($.proxy(
				function (event){
					this.$form.find('input[name=q]').val('');
					this.$form.submit();
				}
			, this));
/*			
		if ($.isMobile() == false) {
			var v = this.$input.val(); 
			this.$input.focus().val('').val(v);
		}*/

		this.populateForm();
	},
	
	populateForm: function() {
		var search = $.url().param('q');
		
		this.$input.val('');
		if (search !== undefined) {
			this.$input.val(decodeURIComponent(search));
		}
	},
	
	clearForm: function() {
		this.$input.val('');
	}
};*/
