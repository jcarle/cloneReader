;(function($) {
	var 
		methods,
		alert;
		
	methods = {
		init : function( options ) {
			if ($(this).data('alert') == null) {
				$(this).data('alert', new alert($(this), options));
			}
			$(this).data('alert').show($(this), options);			
			
			return $(this);
		},

		hide: function() {
			$(this).data('alert').hide();
			return $(this);
		}		
	};

	$.fn.alert = function( method ) {
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'string' || typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
		}  
	}
	
	alert = function() {

	}
					
	alert.prototype = {
		/*
		 * input indica a que elemento se le pasara el foco cuando el alert se cierre
		 * options puede ser un object cons las propiedades {msg, callback }
		 * 			tambien puede ser un DomNode o un String, es este caso el pluggin se encarga de mergear las options 
		 */
		show: function($input, options) {
			this.$input		= $input;
			this.options 	= $.extend(
				{
					msg:			'',
					callback:		null
				},
				(typeof options === 'string' ? { msg: options } :
					($(options).get(0).tagName != null ? { msg: options } : options ) )
			);						 

			this.$div = $('<div />').html(this.options.msg).addClass('alert');
			
			this.$div.dialog( {
				position:	['center', 250],
				draggable: 	false, 
				width:		'300', 
				height: 	'auto',
				minHeight:	20,
				modal: 		true, 
				resizable: 	false, 
				buttons:	{ 'cerrar': function(){ } }, 
				close:		$.proxy(
					function(event) {
						if(this.options.callback instanceof Function) {
							this.options.callback();
						}
						this.$input.focus();
						this.$div.parent().detach();
					}
				, this)
			});
			$('.ui-dialog-titlebar', this.$div.parent()).hide();
			this.$div.parent().find('button')
				.removeAttr('class')
				.unbind()
				.click(function(event){ 
					event.stopPropagation(); 
					$(this).parents('div[role=dialog]').find('.alert').dialog('close') 
				}) 
				.focus();
			this.$div.parent().css({position: 'fixed'}); //.end().dialog('open');
		}
	}
})($);