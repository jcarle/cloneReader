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

			
		
			this.$modal		= $('<div role="dialog" />').addClass('modal');
			this.$body 		= $('<div />').html(this.options.msg).addClass('modal-body').appendTo(this.$modal);
			this.$footer 	= $('<div />').addClass('modal-footer').appendTo(this.$modal);
			this.$btn 		= $('<button data-dismiss="modal" class="btn" />').text('Cerrar').appendTo(this.$footer);
			
			// para evitar que se vaya el foco a otro elemento de la pagina con tab
			$(document).bind('keydown.alertKeydown', ($.proxy(
				function(event) {
					if (event.keyCode == 27) { // esc!
						this.$modal.modal('hide');
						return false;
					}
					if ($.contains(this.$modal[0], event.target)) {
						return true;
					}
					return false;
				}
			, this)));
			
			this.$modal
				.modal( {
					backdrop: true,
					keyboard: true
				})
				.css({ 'top': 200, })
				.on('hidden', $.proxy(
					function(event) {
						$(document).unbind('keydown.alertKeydown');
						
						if(this.options.callback instanceof Function) {
							this.options.callback();
						}
						this.$input.focus();
						//this.$body.parent().detach();
					}
				, this));
				
				$('.modal-backdrop')
					.css('opacity', 0.3)
					.unbind();
		}
	}
})($);