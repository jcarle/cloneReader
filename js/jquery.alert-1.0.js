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
		show: function(input, options) {
			this.input 		= input;
			
			this.options 	= $.extend(
				{
					msg:			'',
					callback:		null
				},
				(typeof options === 'string' ? { msg: options } :
					($(options).get(0).tagName != null ? { msg: options } : options ) )
			);						 
		/*	
			// para evitar que se vaya el foco a otro elemento de la pagina con tab
			$(document).bind('keydown.alertKeydown', ($.proxy(
				function(event) {
					if (event.keyCode == 27 ) { // esc!
//event.keyCode == 13) { // esc!						
						this.hide(); //btnClose.click();
						return false;
					}
					if (event.keyCode == 13 && event.target == this.btnClose) { // enter en el button close
						return true;
					}
				//	return false;
				}
			, this)));
	
	*/
			if (typeof(this.options.msg) =='string') {
				this.options.msg = $.createElement('div', null, this.options.msg, 'alert', null, null, true);
			}
			$('.btnContainer', this.options.msg).remove();
			
			/*
			if (this.options.showBtnClose == true) {
				this.btnClose	= $.createElement('button', $.createElement('div', this.options.msg, null, 'btnContainer' ), 'cerrar' ); /* el btnClose tiene un contenedor por un bug en chrome http://www.punkchip.com/2011/03/chrome-wont-center-button-using-auto-margins/  * /
				$(this.btnClose).click($.proxy(
					function() {
						this.hide();
					}
				, this));
			}	*/		
	
			$(this.options.msg).dialog( {
				position:	['center', 250],
				draggable: 	false, 
				width:		'300', 
				height: 	'auto',
				minHeight:	20,
				modal: 		true, 
				resizable: 	false, 
				buttons:	{ 'cerrar': function(){ $(this).dialog("close"); } }, 
				close:		$.proxy(
					function() {
						$(document).unbind('keydown.alertKeydown');
						if(this.options.callback instanceof Function) {
							this.options.callback();
						}
						$(this.input).focus();
						$(this.options.msg).detach();
					}
				, this)
			})
			$('.ui-dialog-titlebar', $(this.options.msg).parent()).hide();
			$('button', $(this.options.msg).parent()).focus();
			$(this.options.msg).parent().css({position:"fixed"}); //.end().dialog('open');

							
							/*			
				.popupWindow({
					showBtnClose:	false,
					isModal:		true,
					isAutoHidden:	false,
					onHidden:		$.proxy(
						function() {
							$(document).unbind('keydown.alertKeydown');
							if(this.options.callback instanceof Function) {
								this.options.callback();
							}
							$(this.input).focus();
							$(this.options.msg).detach();
						}
					, this)
				})
				.show()
				.css('position', 'fixed');
				*/
			/*				
			if (this.options.showBtnClose == true) {
				$('button', this.options.msg).focus();
			}*/
		}
		/*,
	
		hide: function() {
			$(this.options.msg).dialog('close');
		}*/
	}
})($);