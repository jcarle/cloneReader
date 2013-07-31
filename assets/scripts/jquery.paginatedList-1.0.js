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
	
	paginatedList = function($content, options) {
		this.$content	= $content;
		this.$form 		= this.$content.find('form');
		this.$table		= this.$content.find('table');
		this.options 	= $.extend({}, options );
		
		this.$table.find('tbody .date, tbody .datetime').each(
			function() {
				$.formatDate($(this));
			}
		);
		
		this.$content.find('.btnAdd').click(
			function (event) {
				$.goToUrl($(this).attr('href') + '?urlList=' + $.base64Encode(location.href));
				event.preventDefault;
				return false;
			}
		);

		this.$content.find('.btnDelete').click($.proxy(
			function() { 
				var aDelete = [];
				var $input = this.$table.find('tr.info input');
				for (var i=0; i<$input.length; i++) {
					aDelete.push($($input[i]).val());
				}

				if (aDelete.length == 0) { return;  }
				
				$(document).jAlert( {
					'msg': 			'Está seguro?',
					'isConfirm': 	true,
					'callback': 	function() {}
				});
			}
		, this));
		
		this.$form.on('submit', function() {
			$.showWaiting(true);
		});
		this.$filter = this.$form.find('input[name=filter]');
		
		this.$form.find('.icon-remove')
			.click($.proxy(
				function (event){
					if (this.$filter.val().trim() == '') {
						return;
					}
					this.$filter.val('');
					this.$form.submit();
				}
			, this));

		this.$table.find('tbody tr').click(
				function (event) {
					$.goToUrl($(this).data('controller') + '?urlList=' + $.base64Encode(location.href));
				}
		);
		
		this.$table.find('tbody tr td.checkbox').click(
			function(event) {
				event.stopPropagation();
			}
		);
		
		this.$table.find('tbody input[type=checkbox]').change( $.proxy(
			function(event) {
				this.checkedRow($(event.target).parent().parent());
			}
		, this));
		
		this.$table.find('thead input[type=checkbox]').change( $.proxy(
			function() {
				this.checkAll();
			}
		, this));
		
		this.$table.find('tbody tr').each($.proxy(
			function (event, tr){
				this.checkedRow(tr);
			}
		, this));
	}
	
	paginatedList.prototype = {
		checkedRow: function(row) {
			$(row).removeClass('info');
			
			if ($('input[type=checkbox]', row).is(':checked')) {
				$(row).addClass('info');
			}
			
			
			this.$table.find('thead input[type=checkbox]').removeAttr('checked', 'checked');
			if (!$('tbody input[type=checkbox]:not(:checked)').length) {
				this.$table.find('thead input[type=checkbox]').attr('checked', 'checked');
			}
		},
		
		checkAll: function() {
			this.$table.find('tbody input[type=checkbox]').removeAttr('checked', 'checked');
			
			if (this.$table.find('thead input[type=checkbox]').is(':checked')) {
				this.$table.find('tbody input[type=checkbox]').attr('checked', 'checked');
			}
			
			this.$table.find('tbody input[type=checkbox]').change();
		}
	}
})($);


