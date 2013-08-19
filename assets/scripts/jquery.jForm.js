;(function($) {
	var 
		methods,
		jForm;
		
	methods = {
		init : function( options ) {
			if ($(this).data('jForm') == null) {
				$(this).data('jForm', new jForm($(this), options));
			}
			
			return $(this);
		},
		
		showSubForm: function(controller) {
			$(this).data('jForm').showSubForm(controller);
			return $(this);			
		},

		options: function(){
			return $(this).data('jForm').options;
		}
	};

	$.fn.jForm = function( method ) {
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
		}  
	}
	
	jForm = function($form, options) {
		this.$form 		= $form;
		this.options 	= $.extend({
			sendWithAjax: 	true,
			fields:			[]
		}, options );
		
		this.initFields();
		this.initCallbacks();
		
		this.$form.find('.btn-danger').click($.proxy(
			function(event) {
				event.stopPropagation();
				
				$(document).jAlert( {
					'msg': 			'Está seguro?',
					'isConfirm': 	true,
					'callback': 	$.proxy(
						function() {
							this.options.clickDelete = true;
							this.$form.attr('action', this.options.urlDelete);
							this.sendForm();
						}
					, this)
				});
				
				return false;
			}
		, this));
		
		this.$form.submit($.proxy(
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
		, this));
	}
	
	jForm.prototype = {
		initFields: function() {
			for (var fieldName in this.options.fields){

				var field 		= this.options.fields[fieldName];
				field.name 		= fieldName;
				field.$input	= $('*[name="' + field['name'] + '"]', this.$form);
				
				if (field['type'] != null) {
					field.$input.data( 'field', field);
					
					switch (field['type']) {
						case 'typeahead':
							var $input = field.$input.parent().find('input[name=' + field.fieldId + ']');
							field.$input
								.data( { 'field': field,  '$input': $input, 'map': {} })
								.change(function(event) {
									var map 		= $(event.target).data('typeahead').map;
									var $input		= $(event.target);
									var $inputId	= $(event.target).data('$input');
									if (map != null && map[$input.val()] != null) {
										$inputId.val(map[$input.val()]);
										return;
									}
									$inputId.val('');
								})
								.typeahead({
									source: function (query, process) {
										var $this = this;
										var field = $(this)[0].$element.data('field');
										return $.get(field['source'], { 'query': query},
											function(data){
												var options = [];
												$this['map'] = {}; 
												$.each(data,function (i,val){
													options.push(val.value);
													$this.map[val.value] = val.id; 
												});
												return process(options);
											}
										);
									},
									updater: function (item) {
										this.$element.data('$input').val(this.map[item]);
										//this.$element.parent().find('input[name=' + this.$element.data('field').fieldId + ']').val(this.map[item]);
										return item;
									}
								});
							break;
						case 'date':
						case 'datetime':
							var inputName 	= field.$input.attr('name');
							var format 		= 'dd/mm/yyyy';
							var minView		= 'month';
							if (field['type'] == 'datetime') {
							 	format 	= 'dd/mm/yyyy hh:ii:ss';
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
								.datetimepicker({ 'format': format, 'autoclose': true, 'minView': minView, 'language': 'es', 'pickerPosition': 'bottom-left' });

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
								});						
							break;
						case 'upload':
							this.$form.attr('enctype', 'multipart/form-data');
							this.$form.fileupload( { 
								'autoUpload': 	true,
								'done': 		function (e, data) {
									var result = data.result;
									if (result['code'] == false) {
										return $(document).jAlert(result['result']);
									}
									$(document).jAlert({
										'msg': 		result['result']['msg'],
										'callback': function() {
											$.goToUrl(result['result']['goToUrl']);
										}
									});
								}	 
							});
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
			$.ajax({
				type: 	'post',
				url: 	this.$form.attr('action'),
				data: 	this.$form.serialize()
			})
			.fail(
				function (result) {
					result = $.parseJSON(result.responseText);
					if (result['code'] == false) {
						return $(document).jAlert(result['result']);
					}
				}
			)					
			.done($.proxy(
				function(response) {
					if (response['code'] != true) {
						return $(document).jAlert(response['result']);
					}
					
					if (this.options.isSubForm == true) {
						this.$form.parent().modal('hide');
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
		},
		
		validate: function() {
			for (var i = 0; i<this.options.rules.length; i++){
				var field 	= this.options.rules[i];
				var rules 	= field['rules'].split('|');
				var $input 	= this.options.fields[field.field].$input;
				
				this.$form.find('fieldset').removeClass('error');
				
				for (var z=0; z<rules.length; z++) {
					if (typeof this[rules[z]] === 'function') {
						if (this[rules[z]]($input) == false) {
							$input.parents('fieldset').addClass('error');
							$input.jAlert($.sprintf(this.options.messages[rules[z]], field['label']));
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
			var field = $input.data('field');
			if (field != null) {
				if (field.type == 'typeahead') { // Mejorar esta parte, capaz conviene implementar herencia en los fields, en vez de estar enchastrando todos con IF S
					$input = $input.data('$input');
				}
			}
			
			return ( $input.val().trim() != '');
		},
		
		valid_email: function($input){
			return $.validateEmail($input.val());
		},
		
		initFileupload: function(field) {	
			this.fileupload = field;	
			var $gallery = $('#gallery');
			this.reloadGallery();
			
			$('#fileupload').data( { 'jForm': this } )
			
			$('.btnEditPhotos', $gallery).click( $.proxy(
				function () {
					if (this.$fileupload == null) {
						this.$fileupload = $('#fileupload');

						this.$fileupload.on('hidden', 
							function() {
								var jForm = $(this).data('jForm');
								jForm.reloadGallery();
							}
						);
					}
					
					this.$fileupload.modal( { backdrop: true, keyboard: false });
					
					$(document).off('focusin.modal');
					
					var zIndex = $.topZIndex('body > *');
					this.$fileupload.css( { 'z-index': zIndex + 2 });
					
					$('.modal-backdrop').hide();
			
					$('.modal-backdrop:last')
						.css( {'opacity': 0.3,  'z-index': zIndex + 1 } )
						.unbind()
						.show();
				}
			, this));

			$('#fileupload').fileupload( { autoUpload: true });
		},
		
		reloadGallery: function() {
			var $gallery = $('#gallery');

			$gallery.children('a').remove();
			$('tbody', '#fileupload').children().remove();
			
			$.ajax({
				url: this.fileupload.urlGet,
				data: { }
			 }).done(function (result) {	
				$('#fileupload')
					.fileupload('option', 'done')
					.call($('#fileupload'), null, {result: result});

				for (var i=0; i<result.files.length; i++) {
					var photo = result.files[i];
					
					$('<a rel="gallery" data-gallery="gallery"/>')
						.append($('<img>').prop('src', photo.thumbnailUrl))
						.prop('href', photo.url)
						.prop('title', ''  /*photo.title*/)
						.appendTo($gallery);
				}

				$('img', $gallery).imgCenter( {
					show: false,
					createFrame: true,
				});
			});
		},
		
		loadSubForm: function(field) {
			$.ajax( {
				type: 		'get', 
				url:		field.controller,
				data:		{ 'frmParent': field.frmParent }
			})
			.fail(
				function (result) {
					result = $.parseJSON(result.responseText);
					if (result['code'] == false) {
						return $(document).jAlert(result['result']);
					}
				}
			)
			.done( $.proxy( 
				function (result) {
					if (result['code'] != true) {
						return $(document).jAlert(result['result']);
					}
					
					result = $(result['result']);
					field.$input.children().remove();
					field.$input.html(result);
					
					$('a', field.$input)
						.data( { jForm: this })
						.click(
							function() {
								$(this).data('jForm').showSubForm($(this).attr('href'), field); 
								return false;
							}
						);
					
					$('table tbody tr', field.$input)
						.data( { jForm: this })
						.each(
							function (i, tr) {
								$(tr).click(
									function() {
										if ($(this).attr('href') == null) {
											return;
										}
										$(this).data('jForm').showSubForm($(this).attr('href'), field); 
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
				}
			, this));
		},
		
		showSubForm: function(controller, field) {
			$.ajax( {
				type: 		'get', 
				url:		controller
			})
			.fail(
				function (result) {
					result = $.parseJSON(result.responseText);
					if (result['code'] == false) {
						return $(document).jAlert(result['result']);
					}
				}
			)			
			.done( $.proxy( 
				function (result) {
					$(result['result']).appendTo($('body'));
					
					var frmId 		= $(result['result']).attr('id');
					var $subform 	= $('#' + frmId);
					var options	 	= $subform.jForm('options');
					options.frmParentId = this;
					
					var $modal 			= $('<div class="modal" tabindex="-1" role="dialog" />');
					var $modalBody 		= $('<div class="modal-body" />')
					var $modalFooter 	= $('<div class="modal-footer" />')

					$subform.addClass('row-fluid').appendTo($modal);

					$modalFooter
						.append($('<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">Cerrar</button>'))
						.append($subform.find('.btn-danger'))
						.append($subform.find('.btn-primary'));
					
					$subform.find('.form-actions').remove();
					$subform.children().appendTo($modalBody);
					
					$subform
						.append('\
							<div class="modal-header"> \
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="icon-remove"></i></button> \
								<h3 id="myModalLabel"> <i class="icon-edit"></i> ' + options.title + '</h3> \
							</div> \
						')
						.append($modalBody)
						.append($modalFooter);

					$modal.modal( { backdrop: true, keyboard: false });
					$modal.on('hidden', function() {
						var jForm = $(this).find('form').data('jForm');
						jForm.options.frmParentId.loadSubForm(field);
						$(this).remove();
						
						$('.modal-backdrop').last().show();
					});
					$modal.find('select, input[type=text]').first().focus();

					$(document).off('focusin.modal');
					
					var zIndex = $.topZIndex('body > *');
					$modal.css( { 'z-index': zIndex + 2 });
					
					$('.modal-backdrop').hide();
			
					$('.modal-backdrop:last')
						.css( {'opacity': 0.3,  'z-index': zIndex + 1 } )
						.unbind()
						.show();
				}
			, this));
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
					.autoNumeric('init', { aSep: '.', aDec: ',',  aSign: $currency.find('option:selected').text() +' ' } )
					.change( function(event) {
						$(event.target).next().val($(event.target).autoNumeric('get') ).change();
					});
					
				$maskExchange = $exchange.clone();
				$exchange.hide();
				$maskExchange
					.removeAttr('name')
					.insertBefore($exchange)
					.autoNumeric('init', { aSep: '.', aDec: ',',  aSign: '' } )
					.change( function(event) {
						$(event.target).next().val($(event.target).autoNumeric('get') ).change();;
					});
				
				$total.autoNumeric('init', { vMax: 999999999999, aSep: '.', aDec: ',',  aSign: 'AR$ ' } ) // TODO: desharckodear!

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
				$total.autoNumeric('init', { vMax: 999999999999, aSep: '.', aDec: ',',  aSign: 'AR$ ' } ) // TODO: desharckodear!
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
				type: 	'get', 
				url:	controller
			})
			.done( $.proxy( 
				function (result) {
					$field.children().remove();
					for (var i=0; i<result.length; i++) {
						$('<option />').attr('value', result[i]['id']).text(result[i]['value']).appendTo($field);
					}
					$field.sort();
					$field.val(this.options.fields[$field.attr('name')].value);
				}
			, this));			
		},
		
		checkGroupCheckBox: function($input) { 
			$input.parent().css('background-color', ($input.is(':checked') ? '#D9EDF7' : 'white'));
		}
	}
})($);
