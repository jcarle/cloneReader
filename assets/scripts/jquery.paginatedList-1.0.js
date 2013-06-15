;(function($) {
	var 
		methods,
		paginatedList;
		
	methods = {
		init : function( options ) {
			if ($(this).data('paginatedList') == null) {
				$(this).data('paginatedList', new paginatedList($(this), options));
			}
			
			return $(this);
		}		
	};

	$.fn.paginatedList = function( method ) {
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
		}  
	}
	
	paginatedList = function($form, options) {
		this.$form 		= $form;
		this.options 	= $.extend({}, options );
		
		this.$form.find('.btnAdd').click(
			function (event) {
				$.goToUrl($(this).attr('href') + '?urlList=' + $.base64Encode(location.href));
				event.preventDefault;
				return false;
			}
		);

		this.$form.find('.btnDelete').click($.proxy(
			function() { 
				var aDelete = [];
				var $input = this.$form.find('tr.selected input');
				for (var i=0; i<$input.length; i++) {
					aDelete.push($($input[i]).val());
				}

				if (aDelete.length == 0) { return;  }
				if (!confirm('Está seguro?')) { return; }

cn(aDelete);				
			}
		, this));
		
		this.$form.find('.filterClear')
			.click($.proxy(
				function (event){
					if ($(event.target).prev().val().trim() == '') {
						return;
					}
					$(event.target).prev().val('');
					this.$form.submit();
				}
			, this))
			.css('background', 'url(' + base_url + '/assets/images/iconset.png) no-repeat scroll -174px -48px transparent');

		this.$form.find('tbody tr').click(
				function (event) {
					$.goToUrl($('a', $(this)).attr('href') + '?urlList=' + $.base64Encode(location.href));
				}
		);
		
		this.$form.find('tbody tr td:nth-child(1)').click(
			function(event) {
				event.stopPropagation();
			}
		);
		
		this.$form.find('tbody tr td input[type=checkbox]').change( $.proxy(
			function(event) {
				this.checkedRow($(event.target).parent().parent());
			}
		, this));
		
		this.$form.find('thead tr td input[type=checkbox]').change( $.proxy(
			function() {
				this.checkAll();
			}
		, this));
		
		this.$form.find('tbody tr').each($.proxy(
			function (event, tr){
				this.checkedRow(tr);
			}
		, this));
	}
	
	paginatedList.prototype = {
		checkedRow: function(row) {
			$(row).removeClass('selected');
			
			if ($('input[type=checkbox]', row).is(':checked')) {
				$(row).addClass('selected');
			}
			
			
			this.$form.find('thead tr td input[type=checkbox]').removeAttr('checked', 'checked');
			if (!$('tbody tr td input[type=checkbox]:not(:checked)').length) {
				this.$form.find('thead tr td input[type=checkbox]').attr('checked', 'checked');
			}
		},
		
		checkAll: function() {
			this.$form.find('tbody tr td input[type=checkbox]').removeAttr('checked', 'checked');
			
			if (this.$form.find('thead tr td input[type=checkbox]').is(':checked')) {
				this.$form.find('tbody tr td input[type=checkbox]').attr('checked', 'checked');
			}
			
			this.$form.find('tbody tr td input[type=checkbox]').change();
		}
	}
})($);


