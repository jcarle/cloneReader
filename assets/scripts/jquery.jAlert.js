;(function($) {
	var 
		methods,
		jAlert;
		
	methods = {
		init : function( options ) {
			if ($(this).data('jAlert') == null) {
				$(this).data('jAlert', new jAlert($(this), options));
			}
			$(this).data('jAlert').show($(this), options);			
			
			return $(this);
		},

		hide: function() {
			$(this).data('jAlert').hide();
			return $(this);
		}		
	};

	$.fn.jAlert = function( method ) {
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'string' || typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
		}  
	}
	
	jAlert = function() {

	}
					
	jAlert.prototype = {
		/*
		 * input indica a que elemento se le pasara el foco cuando el jAlert se cierre
		 * options puede ser un object cons las propiedades {msg, callback }
		 * 			tambien puede ser un DomNode o un String, es este caso el pluggin se encarga de mergear las options 
		 */
		show: function($input, options) {
			this.$input		= $input;
			this.options 	= $.extend(
				{
					msg:			'',
					callback:		null,
					isConfirm:		false
				},
				(typeof options === 'string' ? { msg: options } :
					($(options).get(0).tagName != null ? { msg: options } : options ) )
			);

			this.$modal		= $('<div role="dialog" class="modal jAlert" />');
			this.$body 		= $('<div />').html(this.options.msg).addClass('modal-body').appendTo(this.$modal);
			this.$footer 	= $('<div />').addClass('modal-footer').appendTo(this.$modal);
			this.$btn 		= $('<button data-dismiss="modal" class="btn" />').text(this.options.isConfirm == true ? 'Cancelar' : 'Cerrar').appendTo(this.$footer);
			
			if (this.options.isConfirm == true) {
				$('<button data-dismiss="modal" class="btn btn-primary" />')
					.text('Ok')
					.on('click', $.proxy(
						function(event) {
							this.options.callback();
							this.$modal.modal('hide');
						}
					, this))
					.appendTo(this.$footer);
			}
			
			// para evitar que se vaya el foco a otro elemento de la pagina con tab
			$(document).bind('keydown.jAlertKeydown', ($.proxy(
				function(event) {
					event.preventDefault();
					event.stopPropagation();
					
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
				.css({ 'top': 200 })
				.on('hidden', $.proxy(
					function(event) {
						$(this).remove();
						$('.modal-backdrop').show();
						$(document).unbind('keydown.jAlertKeydown');
						
						if (this.options.isConfirm == false) {
							if(this.options.callback instanceof Function) {
								this.options.callback();
							}
							this.$input.focus();
						}
					}
				, this));
			
			this.$btn.focus();
			//$(document).focus();
			$(document).off('focusin.modal');
			
			var zIndex = parseInt(this.$modal.css('z-index'));
			this.$modal.css('z-index', zIndex + 1);
				
			$('.modal-backdrop').hide();
			
			$('.modal-backdrop:last')
				.css( {'opacity': 0.3, 'z-index': parseInt(this.$modal.css('z-index')) } )
				.unbind()
				.show();
		}
	}
})($);