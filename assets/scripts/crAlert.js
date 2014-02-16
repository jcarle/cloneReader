;(function($) {
	var 
		methods,
		crAlert;
		
	methods = {
		init : function( options ) {
			if ($(this).data('crAlert') == null) {
				$(this).data('crAlert', new crAlert($(this), options));
			}
			$(this).data('crAlert').show($(this), options);
			
			return $(this);
		},

		hide: function() {
			$(this).data('crAlert').hide();
			return $(this);
		}		
	};

	$.fn.crAlert = function( method ) {
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'string' || typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
		}  
	}
	
	crAlert = function() {

	}
					
	crAlert.prototype = {
		/*
		 * input indica a que elemento se le pasara el foco cuando el crAlert se cierre
		 * options puede ser un object cons las propiedades {msg, callback }
		 * 			tambien puede ser un DomNode o un String, es este caso el pluggin se encarga de mergear las options 
		 */
		show: function($input, options) {
			this.$input		= $input;
			this.options 	= $.extend(
				{
					msg:			'',
					callback:		null,
					isConfirm:		false,
					confirmText:	_msg['Ok']
				},
				(typeof options === 'string' ? { msg: options } :
					($(options).get(0).tagName != null ? { msg: options } : options ) )
			);

			this.$modal			= $('<div role="dialog" class="modal in crAlert" />');
			this.$modalDialog 	= $('<div class="modal-dialog" />').appendTo(this.$modal);
			this.$modalContent 	= $('<div class="modal-content" />').appendTo(this.$modalDialog);
			this.$body	 		= $('<div />').html(this.options.msg).addClass('modal-body').appendTo(this.$modalContent);
			this.$footer	 	= $('<div />').addClass('modal-footer').appendTo(this.$modalContent);
			this.$btn 			= $('<button data-dismiss="modal" class="btn btn-default" />').text(this.options.isConfirm == true ? _msg['Cancel'] : _msg['Close']).appendTo(this.$footer);
			
			if (this.options.isConfirm == true) {
				$('<button data-dismiss="modal" class="btn btn-primary" />')
					.text(this.options.confirmText)
					.on('click', $.proxy(
						function(event) {
							this.options.callback();
							this.$modal.modal('hide');
						}
					, this))
					.appendTo(this.$footer);
			}
			
			// para evitar que se vaya el foco a otro elemento de la pagina con tab
			$(document).bind('keydown.crAlertKeydown', ($.proxy(
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
			
			
			$.showModal(this.$modal, true);
			this.$modal.on('hidden.bs.modal', $.proxy(
				function(event) {
					$(this).remove();
					$(document).unbind('keydown.crAlertKeydown');
						
					if (this.options.isConfirm == false) {
						if(this.options.callback instanceof Function) {
							this.options.callback();
						}
						this.$input.focus();
					}
				}
			, this));				
			
			this.$btn.focus();
			$(document).focus();
		}
	}
})($);
