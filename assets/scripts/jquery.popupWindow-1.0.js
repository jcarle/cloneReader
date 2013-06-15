;(function($) {
	var 
		methods,
		popupWindow;
		
	methods = {
		init : function( options ) {
			if ($(this).data('popupWindow') == null) {
				$(this).data('popupWindow', new popupWindow($(this), options));
			}
			$(this).data('popupWindow').show($(this), options);			
			
			return $(this);
		},

		getDivModal : function( ) {
			return $(this).data('popupWindow').getDivModal();
		},

		hide: function() {
			if ($(this).data('popupWindow') != null) {
				$(this).data('popupWindow').hide();
			}
			return $(this);
		}		
	};

	$.fn.popupWindow = function( method ) {
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
		}  
	}
	
	popupWindow = function(childDiv, options) {
		this.childDiv 	= childDiv;		
		this.divModal	= $.createElement('div', null, null, 'popupWindowDivModal');
		
		$(this.divModal).mousedown($.proxy(function(e) {
			if (this.options.isAutoHidden == true) {
				if ((this.options.onAutoHidden) && (typeof this.options.onAutoHidden === 'function')) {
					this.options.onAutoHidden();
				}
				
				this.hide();
			}
		}, this));
	}

	popupWindow.prototype = {
		show: function(childDiv, options) {
			this.childDiv 	= $(childDiv);
			this.options 	= $.extend({
				x:				'center',
				y:				'center',
				isAutoHidden:	false,
				isModal:		true,
				showBtnClose:	true,
				onHidden:		null,
				onAutoHidden:	null,
				modal:			{
									bgColor:	'#000000',
									opacity:	.4
								}
			}, options);			
	
	
			$(this.childDiv)
				.addClass('popupWindow')
				.appendTo($('body'))
				.css( {
					'position':	'absolute', 
					'z-index': 	Math.max($.topZIndex('body > *') + 1, 2) // para que el z-index no sea 0 en el divModal 
				});
			
			this.setPosition();
			
			$(window).bind('resize.resizePopupWindow', $.proxy(
				function() { 
					$('body > .popupWindowDivModal').hide();
					this.setPosition();
					this.fixedDivModal();
				}
			, this));			
			
			if (this.options.isAutoHidden == true || this.options.isModal == true) {
				$(this.divModal)
					.appendTo($('body'))
					.css({
						'background': 	'url(' + base_url + 'assets/styles/img/transparent.gif) repeat ' + this.options.modal.bgColor,
						'position': 	'absolute',
						'width': 		$(document).width() + 'px',
						'height': 		$(document).height() + 'px',
						'top': 			'0px',
						'left': 		'0px',
						'z-index': 		$(this.childDiv).css('z-index') - 1
					})
					.fadeTo(0, this.options.modal.opacity);	
				this.fixedDivModal();
			}
	
	
			if (this.options.showBtnClose == true && this.btnClose == null) {
				this.btnClose	= $.createElement('span', $(this.childDiv)[0], 'x', 'btnClose');
				$(this.btnClose).click(
					$.proxy(function() { 
						this.hide();
					}
				, this));				
			}
		},

		hide: function() {
			if ((this.options.onHidden) && (typeof this.options.onHidden === 'function')) {
				if (this.options.onHidden() === false) {
					return;
				}
			}
			
			$(window).unbind('resize.resizePopupWindow');
			$(this.childDiv).hide();
			$(this.divModal).detach();
			this.fixedDivModal();
		},
		
		setPosition: function() {
			if (this.options.x == 'center') {
				$(this.childDiv).css({
					left: ($(window).width() - $(this.childDiv).outerWidth())/2
				});
			}
			else if (this.options.x != null) {
				$(this.childDiv).css('left', this.options.x + 'px');
			}
			
			if (this.options.y == 'center') {
				$(this.childDiv).css( {
					top: 		($(window).height() - $(this.childDiv).outerHeight())/2,
					position: 	'fixed'
				});
			}
			else if (this.options.y != null) { 
				$(this.childDiv).css('top', this.options.y + 'px'); 
			}
		},
		
		fixedDivModal: function() {
			$('body > .popupWindowDivModal').hide();
			
			$(this.divModal)
				.css({
					'width': 		$(document).width() + 'px',
					'height': 		$(document).height() + 'px'
			});
									
			$('body > .popupWindowDivModal:last').show();
		},
		
		getDivModal: function() {
			return $(this.divModal); 
		}
	}
})($);