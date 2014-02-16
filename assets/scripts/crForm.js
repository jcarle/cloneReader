;(function($) {
	var 
		methods,
		crForm;
		
	methods = {
		init : function( options ) {
			if ($(this).data('crForm') == null) {
				$(this).data('crForm', new crForm($(this), options));
			}
			
			return $(this);
		},
		
		showSubForm: function(controller) {
			$(this).data('crForm').showSubForm(controller);
			return $(this);			
		},

		options: function(){
			return $(this).data('crForm').options;
		}
	};

	$.fn.crForm = function( method ) {
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
		}
	}
	
	crForm = function($form, options) {
		this.$form 		= $form;
		this.$btnSubmit	= this.$form.find('button[type=submit]');
		this.options 	= $.extend({
			sendWithAjax: 	true,
			fields:			[],
			rules: 			[]
		}, options );
		
		this.initFields();
		this.initCallbacks();
		this.resizeWindow();
		
		this.$form.find('.btn-danger').click($.proxy(
			function(event) {
				event.stopPropagation();
				
				$(document).crAlert( {
					'msg': 			_msg['Are you sure?'],
					'isConfirm': 	true,
					'callback': 	$.proxy(
						function() {
							this.$form.attr('action', this.options.urlDelete);
							this.sendForm();
						}
					, this)
				});
				
				return false;
			}
		, this));
		
		this.$form
			.submit($.proxy(
				function() {
					if ( !this.validate() ) {
						return false;
					}
					
					if (this.options.sendWithAjax == true) {
						this.sendForm();
						return false;
					}
					
					return true; 
				}
			, this))
			.change($.proxy(
				function() {
					this.changeField();
				}
			, this));
			
		$(window).resize($.proxy(
			function() {
				this.resizeWindow();
			}
		, this));
	}
	
	crForm.prototype = {
		initFields: function() {
			for (var fieldName in this.options.fields){

				var field 		= this.options.fields[fieldName];
				field.name 		= fieldName;
				field.$input	= $('*[name="' + field['name'] + '"]', this.$form);
				
				if (field['type'] != null) {
					field.$input.data( 'field', field);
					
					switch (field['type']) {
						case 'dropdown':
							field.$input.select2();
							break;
						case 'typeahead':
							if (field.multiple == null) {
								field.multiple = false;
							}

							if (field.placeholder != null) {
								field.$input.attr('placeholder', field.placeholder);
							}

							field.$input
								.select2({
									multiple: field.multiple,
	//								openOnEnter: false,
									minimumInputLength: 1,
									ajax: {
										url: 		field['source'],
										dataType: 	'json',
										data: 		function (term, page) {
											return { 'query': term };
										},
										results: 	function (data, page) {
											return {results: data};
										}
									}
								})
								.on('select2-open', function(event) {
									$('a > .select2-input').addClass('form-control');
								})
								.on('select2-close', function(event) {
									//$(event.target).parent().find('.form-control').css('border-radius', '4px');
								})								

								if (field.multiple == false) {
									if (field.value.id != null && field.value.id != false) { 
										field.$input.select2('data', field.value);
									}
								}
								else {
									field.$input.select2('data', field.value);
								}

							break;
						case 'date':
						case 'datetime':
							if ($.inArray(field.$input.val(), ['0000-00-00', '0000-00-00 00:00:00']) != -1) {
								field.$input.val('');
							}

							var inputName 	= field.$input.attr('name');
							var format 		= _msg['DATE_FORMAT'];
							var minView		= 'month';
							if (field['type'] == 'datetime') {
							 	format 	= _msg['DATE_FORMAT'] + ' hh:ii:ss';
								minView	= 'hour';
							}

							field.$input
								.data('inputName', inputName)
								.removeAttr('name')
								.on('change', 
									function(event){
										var $input 	= $(event.target);
										
										if ($input.val() == '') {
											$input.parent().parent().find('input[name=' +  $input.data('inputName') + ']').val('');
											return;
										}
										
										var datetimepicker 	= $input.parent().data('datetimepicker');
										datetimepicker.date.setSeconds(0);
										$input.parent().parent().find('input[name=' +  $input.data('inputName') + ']').val($.ISODateString(datetimepicker.date));
									}
								);			
								
 							field.$input.parent()
								.addClass('date form_datetime')
								.datetimepicker({ 'format': format, 'autoclose': true, 'minView': minView, 'language': langId, 'pickerPosition': 'bottom-left' });

							$('<input type="hidden" name="' + inputName + '" />').appendTo(field.$input.parent().parent());
							field.$input.parent().datetimepicker('show').datetimepicker('hide');
							field.$input.change();
							break;
						case 'gallery':
							this.initFileupload(field);
							break;
						case 'subform':
							this.loadSubForm(field);
							break;
						case 'raty':
							field.$input.raty( {
								score: 		field['value'],
								scoreName: 	field['name'],
								path: 		base_url + 'assets/images/',
								click:		$.proxy(function() {
									this.changeField();
								}, this)
							});
							break;
						case 'upload':
							this.$form.attr('enctype', 'multipart/form-data');
							this.$form.fileupload( { 
								'autoUpload': 	true,
								'done': 		function (e, data) {
									var result = data.result;
									if (result['code'] == false) {
										return $(document).crAlert(result['result']);
									}
									$(document).crAlert({
										'msg': 		result['result']['msg'],
										'callback': function() {
											$.goToUrl(result['result']['goToUrl']);
										}
									});
								},
								'fail': 		function (jqXHR, textStatus) {
									var result = $.parseJSON(textStatus.jqXHR.responseText);
									if (result['code'] == false) {
										return $(document).crAlert(result['result']);
									}
								}
							});
						case 'numeric':
							$maskNumeric = field.$input.clone();
							field.$input.hide();
							$maskNumeric
								.removeAttr('name')
								.insertBefore(field.$input)
								.autoNumeric('init', { aSep: _msg['NUMBER_THOUSANDS_SEP'], aDec: _msg['NUMBER_DEC_SEP'],  aSign: '', mDec: field.mDec } )
								.change( function(event) {
									$(event.target).next().val($(event.target).autoNumeric('get') ).change();
								});
											
							break;
					}
				}
			}
			
			this.$form.find('.groupCheckBox input[type=checkbox]')
				.click($.proxy(
					function(event) {
						this.checkGroupCheckBox($(event.target));
						//$(event.target).parent().css('background-color', ($(event.target).is(':checked') ? '#D9EDF7' : 'white'));
					}
				, this))
				.each($.proxy(
					function (i, input) {
						this.checkGroupCheckBox($(input));
					}
				, this));				
		},

		initCallbacks: function(){
			for (var fieldName in this.options.fields){
				var field = this.options.fields[fieldName];
				
				if (field.subscribe != null) {
					for (var i = 0; i<field.subscribe.length; i++) {
						var subscribe = field.subscribe[i];
						$(this.getFieldByName(subscribe.field)).bind(
							subscribe.event,
							{ $input: field.$input, callback: subscribe.callback, arguments: subscribe.arguments, applyCallback: subscribe.applyCallback }, 
								$.proxy( 
									function(event) {
										if (event.data.applyCallback) {
											if (eval(event.data.applyCallback) == false) { return; }
										}
										
										var arguments = [event.data.$input];
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
						
						if (subscribe.runOnInit == true) {
							$(this.getFieldByName(subscribe.field)).trigger(subscribe.event);
						}
					}
				}
			}
		},
		
		sendForm: function() {
			if (this.options.modalHideOnSubmit == true) {
				this.$form.parents('.modal').first().modal('hide');
			}
			
			$.ajax({
				'type': 	'post',
				'url': 		this.$form.attr('action'),
				'data': 	this.$form.serialize(),
				'success': 	$.proxy(
					function(response) {
						if (this.options.callback != null) {
							if (typeof this.options.callback == 'string') {
								eval('this.options.callback = ' + this.options.callback);
							}
							this.options.callback(response);
							return;
						}
						
						if (response['code'] != true) {
							return $(document).crAlert(response['result']);
						}
						
						if (response['result']['msg'] != null && response['result']['goToUrl'] != null) {
							$(document).crAlert({
								'msg': 		response['result']['msg'],
								'callback': function() {
									$.goToUrl(response['result']['goToUrl']);
								}
							});
							return;
						}
	
						if (response['result']['notification'] != null) {
							$.showNotification(response['result']['notification']); 
						}
						if (this.options.isSubForm == true) {
							this.$form.parents('.modal').first().modal('hide');
							return;
						}							
						if ($.url().param('urlList') != null) {
							$.goToUrl($.base64Decode($.url().param('urlList')));
						}
						if (response['result']['goToUrl'] != null) {
							$.goToUrl(response['result']['goToUrl']);
						}
	
					}
				, this)
			})
			.fail(
				function (result) {
					result = $.parseJSON(result.responseText);
					if (result['code'] == false) {
						return $(document).crAlert(result['result']);
					}
				}
			);
			
		},
		
		validate: function() {
			for (var i = 0; i<this.options.rules.length; i++){
				var field 	= this.options.rules[i];
				var rules 	= field['rules'].split('|');
				var $input 	= this.options.fields[field.field].$input;
				
				this.$form.find('fieldset').removeClass('has-error');
				
				for (var z=0; z<rules.length; z++) {
					if (typeof this[rules[z]] === 'function') {
						if (this[rules[z]]($input) == false) {
							$input.parents('fieldset').addClass('has-error');
							$input.crAlert($.sprintf(this.options.messages[rules[z]], field['label']));
							return false;
						}
					}
				}
			};
			
			return true;
		},
		
		numeric: function($input) {
			return !isNaN($input.val());
		},
		
		required: function($input) {
			return ( $input.val().trim() != '');
		},
		
		valid_email: function($input){
			return $.validateEmail($input.val());
		},
		
		initFileupload: function(field) {	
			this.fileupload 	= field;	
			var $gallery 		= $('.gallery');
			this.reloadGallery();
			
			$('#fileupload').data( { 'crForm': this } )
			
			$('.btnEditPhotos', $gallery).click( $.proxy(
				function () {
					if (this.$fileupload == null) {
						this.$fileupload = $('#fileupload');

						this.$fileupload.on('hidden.bs.modal', 
							function() {
								var crForm = $(this).data('crForm');
								crForm.reloadGallery();
							}
						);
					}
					
					$.showModal(this.$fileupload, false);
				}
			, this));

			$('#fileupload').fileupload( { autoUpload: true });
		},
		
		reloadGallery: function() {
			var $gallery = $('.gallery');
			
			if ($gallery.data('initGallery') != true) {
				$gallery.find('.thumbnails').click(function(event) {
					var target 	= event.target;
					var link 	= target.src ? $(target).parents('a').get(0) : target;
					var options = {index: link, event: event, startSlideshow: true, slideshowInterval: 5000, stretchImages: false},
					links 		= this.getElementsByTagName('a');
					blueimp.Gallery(links, options);
				});
				
				$gallery.data('initGallery', true);
			}

			$gallery.find('a').remove();
			$('tbody', '#fileupload').children().remove();
			
			$.ajax({
				'url': 		this.fileupload.urlGet,
				'data': 	{ },
				'success': 	function (result) {	
					$('#fileupload')
						.fileupload('option', 'done')
						.call($('#fileupload'), null, {result: result});
	
					for (var i=0; i<result.files.length; i++) {
						var photo = result.files[i];
						
						$('<a class="thumbnail " />')
							.append($('<img />').prop('src', photo.thumbnailUrl))
							.prop('href', photo.url)
							.prop('title', ''  /*photo.title*/)
							.appendTo($gallery.find('.thumbnails'));
					}
	
					$('img', $gallery).imgCenter( { show: false, createFrame: true } );
				}
			});
		},
		
		loadSubForm: function(field) {
			$.ajax( {
				'type': 		'get', 
				'url':			field.controller,
				'data':			{ 'frmParent': field.frmParent },
				'success': 		$.proxy( 
					function (result) {
						if (result['code'] != true) {
							return $(document).crAlert(result['result']);
						}
						
						result = $(result['result']);
						field.$input.children().remove();
						field.$input.html(result);
						
						$('a', field.$input)
							.data( { crForm: this })
							.click(
								function() {
									$(this).data('crForm').showSubForm($(this).attr('href'), field); 
									return false;
								}
							);
						
						$('table tbody tr', field.$input)
							.data( { crForm: this })
							.each(
								function (i, tr) {
									$(tr).click(
										function() {
											if ($(this).attr('href') == null) {
												return;
											}
											$(this).data('crForm').showSubForm($(this).attr('href'), field); 
										}
									);
								}
							);
						
						field.$input.find('tbody .date, tbody .datetime').each(
							function() {
								$.formatDate($(this));
							}
						);
	
						field.$input.change();
						this.resizeWindow();
					}
				, this)
			})
			.fail(
				function (result) {
					result = $.parseJSON(result.responseText);
					if (result['code'] == false) {
						return $(document).crAlert(result['result']);
					}
				}
			);
		},
		
		showSubForm: function(controller, field) {
			$.ajax( {
				'type': 		'get', 
				'url':			controller,
				'success': 		$.proxy( 
					function (result) {
						$(result['result']).appendTo($('body'));
						
						var frmId 			= $(result['result']).find('form').attr('id');
						var $subform 		= $('#' + frmId);
						var options	 		= $subform.crForm('options');
						var $modal			= $subform.parents('.modal');
						options.frmParentId = this;
	
						$.showModal($modal, false);
						$modal.on('hidden.bs.modal', function() {
							var crForm = $(this).find('form').data('crForm');
							crForm.options.frmParentId.loadSubForm(field);
													
							$(this).remove();
						});
	
						$subform.find('select, input[type=text]').first().focus();
					}
				, this)
			})
			.fail(
				function (result) {
					result = $.parseJSON(result.responseText);
					if (result['code'] == false) {
						return $(document).crAlert(result['result']);
					}
				}
			);
		},
		
		getFieldByName: function(fieldName){
			return $('*[name="' + fieldName + '"]', this.$form);
		},
		
		toogleField: function($field, value) { // TODO: implementar los otros metodos! ( show, hide, etc)
			$field.parent().toggle(value);
		},
		
		calculatePrice: function($field, $price, $currency, $exchange, $total) {
			if ($total.data('init-price') == null) {
				$maskPrice = $price.clone();
				$price.hide();
				$maskPrice
					.removeAttr('name')
					.insertBefore($price)
					.autoNumeric('init', { aSep: _msg['NUMBER_THOUSANDS_SEP'], aDec: _msg['NUMBER_DEC_SEP'],  aSign: $currency.find('option:selected').text() +' ' } )
					.change( function(event) {
						$(event.target).next().val($(event.target).autoNumeric('get') ).change();
					});
					
				$maskExchange = $exchange.clone();
				$exchange.hide();
				$maskExchange
					.removeAttr('name')
					.insertBefore($exchange)
					.autoNumeric('init', { aSep: _msg['NUMBER_THOUSANDS_SEP'], aDec: _msg['NUMBER_DEC_SEP'],  aSign: '' } )
					.change( function(event) {
						$(event.target).next().val($(event.target).autoNumeric('get') ).change();;
					});
				
				$total.autoNumeric('init', { vMax: 999999999999, aSep: _msg['NUMBER_THOUSANDS_SEP'], aDec: _msg['NUMBER_DEC_SEP'],  aSign: 'AR$ ' } ) // TODO: desharckodear!

				this.$form.bind('submit', $.proxy(
					function($maskPrice, $maskExchange, event) {
						$maskPrice.change();
						$maskExchange.change();
					}
				, this, $maskPrice, $maskExchange));
				
								
				$total.data('init-price', true);
			}
			
			if ($currency.val() == 1) { // TODO: desharckodear!
				$exchange.val(1);
				$exchange.prev().autoNumeric('set', 1);
			}

			$price.prev().autoNumeric('update', { aSign: $currency.find('option:selected').text() +' ' } )
			$total.autoNumeric('set', $price.val() * $exchange.val());
		},
		
		sumValues: function($total, aFieldName) {
			if ($total.data('init-price') == null) {
				$total.autoNumeric('init', { vMax: 999999999999, aSep: _msg['NUMBER_THOUSANDS_SEP'], aDec: _msg['NUMBER_DEC_SEP'],  aSign: 'AR$ ' } ) // TODO: desharckodear!
			}
			
			var total = 0;
			for (var i=0; i<aFieldName.length; i++) {
				var field = $('*[name="' + aFieldName[i] + '"]').data('field');
				if (field.type == 'subform') {
					var value = field.$input.find('input').val();
				}
				else {
					var value = field.$input.val();
				}
				if (isNaN(value) == false) {
					total += Number(value);
				}
			}
			
			$total.autoNumeric('set', total);
		},
		
		loadDropdown: function($field, value) {
			var controller = this.options.fields[$field.attr('name')].controller;
			if (value != null) {
				controller += '/' + value;
			}
			
			$.ajax( {
				'type': 	'get', 
				'url':		controller,
				'success': 	$.proxy( 
					function (result) {
						$field.children().remove();
						for (var i=0; i<result.length; i++) {
							$('<option />').attr('value', result[i]['id']).text(result[i]['value']).appendTo($field);
						}
						$field.val(this.options.fields[$field.attr('name')].value);
						$field.select2();
					}
				, this)
			});
		},
		
		checkGroupCheckBox: function($input) { 
			var $li = $input.parents('li');
			$li.removeClass('active');
			if ($input.is(':checked') == true) {
				$li.addClass('active');
			}
		},
		
		changeField: function() {
			this.$btnSubmit.removeAttr('disabled');
		},
		
		resizeWindow: function() {
			var width = this.$form.width();
			this.$form.find('.table-responsive').css('max-width', width - 30 );
		}
	}
})($);
