// TODO: renombrar esta classe

;(function($) {
	var 
		methods,
		formValidator;
		
	methods = {
		init : function( options ) {
			if ($(this).data('formValidator') == null) {
				$(this).data('formValidator', new formValidator($(this), options));
			}
			
			return $(this);
		},
		
		showSubForm: function(controller) {
			$(this).data('formValidator').showSubForm(controller);
			return $(this);			
		},

		options: function(){
			return $(this).data('formValidator').options;
		}
	};

	$.fn.formValidator = function( method ) {
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
		}  
	}
	
	formValidator = function(form, options) {
		this.form 		= $(form);
		this.options 	= $.extend({
			sendWithAjax: 	true,
			fields:			[],
			showBtnBack:	true
		}, options );
		
		this.initFields();
		this.initCallbacks();
		
		if (this.options.showBtnBack == true) {
			var urlList = $.utf8Decode();
			
			$('<a href="javascript:void(0);" class="btnBack">volver</a>')
				.appendTo($(this.form))
				.click(function() {
					$.goToUrl($.base64Decode($.url().param('urlList')));
				}
			);
		}

		this.form.submit($.proxy(
			function() {
				if ( !this.validate() ) {
					return false;
				}
				
				if (this.options.sendWithAjax == true) {
					$.ajax({
						type: 	'post',
						url: 	$(this.form).attr('action'),
						data: 	$(this.form).serialize()
					}).
					done($.proxy(
						function(response) {
							if (response['code'] != true) {
								return $(document).alert(response['result']);
							}
							
							if (this.options.isSubForm == true) {
								(this.form).dialog('close');
								return;
							}							
							if ($.url().param('urlList') != null) {
								$.goToUrl($.base64Decode($.url().param('urlList')));
							}
							if (response['result']['goToUrl'] != null) {
								$.goToUrl(response['result']['goToUrl']);
							}
						}
					, this));
					
					return false;
				}
				
				return true; 
			}
		, this));
	}
	
	formValidator.prototype = {
		initFields: function() {
			for (var fieldName in this.options.fields){

				var field = this.options.fields[fieldName];
				field.name 	= fieldName;
				field.input	= $('*[name="' + field['name'] + '"]', this.form);
				
				if (field['type'] != null) {
					switch (field['type']) {
						case 'autocomplete':
							$(field.input)
								.data( { 'field': field })
								.autocomplete( {
									source: 	field['source'],
									minLength: 	1,
									select: 	function( event, ui ) {
										$('*[name=' + $(event.target).data('field')['fieldId'] + ']', this.form).
											val(ui.item.id)
											.change();
									}								
							});
							break;
						case 'date':	
							$(field.input).datepicker({ dateFormat: 'yy-mm-dd' });
							break;
						case 'datetime':
							$(field.input).addClass('datetime').datetimepicker({ dateFormat: 'yy-mm-dd' });
							break;						
						case 'gallery':
							this.initFileupload(field);
							break;
						case 'subform':
							this.loadSubForm(field);
					}
				}
			}
		},

		initCallbacks: function(){
			
			for (var fieldName in this.options.fields){
				var field = this.options.fields[fieldName];
				
				if (field.subscribe != null) {
					for (var i = 0; i<field.subscribe.length; i++) {
						var subscribe = field.subscribe[i];
						$(this.getFieldByName(subscribe.field)).bind(
							subscribe.event,
							{ input: field.input, callback: subscribe.callback, arguments: subscribe.arguments, applyCallback: subscribe.applyCallback }, 
								$.proxy( 
									function(event) {
										if (event.data.applyCallback) {
											if (eval(event.data.applyCallback) == false) { return; }
										}
										
										var arguments = [event.data.input];
										if (event.data.arguments) {
											for (var i = 0; i<event.data.arguments.length; i++) {
												arguments.push(eval(event.data.arguments[i]));
											}
										}
												
										var method = event.data.callback;			
										if ( this[method] ) {
											return this[ method ].apply( this, Array.prototype.slice.call( arguments, 0 ));
										} 
										else {
											$.error( 'Method ' +  method + ' does not exist ' );
										}  
									}
								, this)
						);
					}
				}
			}
		},
		
		validate: function() {
			for (var i = 0; i<this.options.rules.length; i++){
				var field = this.options.rules[i];
				var rules = field['rules'].split('|');
				var input = this.options.fields[field.field].input;
				
				for (var z=0; z<rules.length; z++) {
					if (typeof this[rules[z]] === 'function') {
						if (this[rules[z]](input) == false) {
							$(input).alert($.sprintf(this.options.messages[rules[z]], field['label']));
							return false;
						}
					}
				}
			};
			
			return true;
		},
		
		required: function(input) {
			return ( $(input).val().trim() != '');
		},
		
		valid_email: function(input){
			return $.validateEmail($(input).val());
		},
		
		initFileupload: function(field) {	
			this.fileupload = field;	
			var gallery = $('#gallery');
			this.reloadGallery();
			
			$('#fileupload').data( { 'formValidator': this } )
			
			$('.btnEditPhotos', gallery).click(
					function () {
						$('#fileupload').dialog( {
							draggable: 	false, 
							width:		1000, 
							height: 	700, 
							modal: 		true, 
							resizable: 	false, 
							title: 		'Editar fotos',
							close:		function(event, ui) {
											$(this).data('formValidator').reloadGallery();
										}
							})
					}
				);
							

			$('#fileupload').fileupload( { autoUpload: true });
		},
		
		reloadGallery: function() {
			var gallery = $('#gallery');
			
			$(gallery).children('a').remove();
			$('tbody', '#fileupload').children().remove();
			
			$.getJSON(this.fileupload.urlGet, function (result) {
				if (result && result.length) {
					$('#fileupload')
						.fileupload('option', 'done')
						.call($('#fileupload'), null, {result: result});
											
					for (var i=0; i<result.length; i++) {
						var photo = result[i];
						
						$('<a rel="gallery"/>')
							.append($('<img>').prop('src', photo.thumbnail_url))
							.prop('href', photo.url)
							.prop('title', photo.url /*photo.title*/)
							.appendTo(gallery);
					}
	
					$('img', gallery).imgCenter( {
						show: false,
						createFrame: true,
						complete:
							function(img) {
								$(img).fadeIn('slow', function() {
									$(img).parent().css('background', 'none');
								});
							}
					});
				}
			});
		},
		
		loadSubForm: function(field) {
			$.ajax( {
				type: 	'get', 
				url:	field.controller,
				data:	{ 'frmParent': field.frmParent }
				})
				.done( $.proxy( 
				function (result) {
					$(field.input).children().remove();
					$(field.input).html(result);
					
					$('a', $(field.input))
						.data( { formValidator: this })
						.click(
							function() {
								$(this).data('formValidator').showSubForm($(this).attr('href'), field); 
								return false;
							}
						);
					
					$('table tbody tr', $(field.input))
						.data( { formValidator: this })
						.each(
							function (i, tr) {
								$(tr).click(
									function() {
										if ($(this).attr('href') == null) {
											return;
										}
										$(this).data('formValidator').showSubForm($(this).attr('href'), field); 
									}
								);
							}
						)
				}
			, this));	
		},
		
		showSubForm: function(controller, field) {
			$.ajax( {
				type: 	'get', 
				url:	controller
			})
			.done( $.proxy( 
				function (result, a, b) {
					
					var frmId = $(result).attr('id');	
					$(result).appendTo($('body'));
					
					var $subform = $('#' + frmId);
					var options = $subform.formValidator('options');
					options.frmParentId = this;

					$subform.dialog({  
							position:	['center', 150],
							draggable: 	false, 
							width:		'auto', 
							height: 	'auto', 
							modal: 		true, 
							resizable: 	false, 
							title: 		options.title, 
							close:		function(event, ui) {
											options.frmParentId.loadSubForm(field)
											$(this).remove();
										}
							})
				}
			, this));				
		},
		
		getFieldByName: function(fieldName){
			return $('*[name="' + fieldName + '"]', this.form);
		},
		
		toogleField: function(field, value) { // TODO: implementar los otros metodos! ( show, hide, etc)
			$(field).parent().toggle(value);
		}
	}
})($);