/**
 * El form tiene que tener este formato:
 * 
$form = array(
	'frmId'		=> 'frmId',
	'action'	=> base_url('entity/save'), // 
	'fields'	=> array(), // fields que va a incluir el formulario
	'rules'		=> array(), // reglas de validacion para cada campo
	'buttons'	=> array(), // los bottones que se van a mostrar 
	'info'		=> array('position' => 'left|right', 'html' => ''), // si incluye info a los costados
	'title'		=> 'title',
	'icon'		=> 'fa fa-edit', // se utiliza en los popup form,
	'urlDelete' => base_url('entity/delete'), // url para borrar 
	'callback'	=> function javascript que se llama al enviar el form 
);
*/

;(function($) {
	var 
		methods,
		crForm;
		
	methods = {
		init : function( options ) {
			var $element = $(this);
			if (options == null) {
				options = {};
			}
			// Para que se autoreenderee: nececita que sea llamado desde NULL $(null) y con las properties autoRender y $parentNode
			// Se utiliza en appAjax
			if ($element.length == 0) { 
				if (options.autoRender == true && options.$parentNode != null) {
					$element = renderCrForm(options, options.$parentNode);
				}
				else { 
					return null;
				}
			}
			
			if ($element.data('crForm') == null) {
				$element.data('crForm', new crForm($element, options));
			}
			
			return $element;
		},
		
		renderCrFormFields: function(fields, $parentNode) { // Para renderear los elementos, en caso de que tengan un container distinto, como crFilterList
			renderCrFormFields(fields, $parentNode);
		},
		
		renderPopupForm: function(data) {
			return renderPopupForm(data);
		},
		
		renderAjaxForm: function(data, $parentNode) {
			return renderAjaxForm(data, $parentNode);
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
		
		this.options.urlSave = this.$form.attr('action');
		
		this.$form.find('.btn-danger').click($.proxy(
			function(event) {
				event.stopPropagation();
				
				$(document).crAlert( {
					'msg': 			crLang.line('Are you sure?'),
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
		
		this.$form.on('submit', $.proxy(
				function() {
					if ( !this.validate() ) {
						return false;
					}
					
					this.$form.attr('action', this.options.urlSave);
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
				field.$input	= this.$form.find('*[name="' + field['name'] + '"]');
				
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
//									openOnEnter: false,
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
							var format 		= crLang.line('DATE_FORMAT');
							var minView		= 'month';
							if (field['type'] == 'datetime') {
							 	format 	= crLang.line('DATE_FORMAT') + ' hh:ii:ss';
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
								.datetimepicker({ 'format': format, 'autoclose': true, 'minView': minView, 'language': $.normalizeLang($.crSettings.langId), 'pickerPosition': 'bottom-left' });

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
								'done': 		
									function (event, data) {
										var response = data.result;
										if ($.hasAjaxDefaultAction(response) == true) { return; }
										$(document).crAlert({
											'msg': 		response['result']['msg'],
											'callback': function() {
												$.goToUrl(response['result']['goToUrl']);
											}
										});
									},
								'fail': 		
									function (event, data) {
										if (data.jqXHR.status === 0) {
											return $(document).crAlert( crLang.line('Not connected. Please verify your network connection') );
										}
										var response = $.parseJSON(data.jqXHR.responseText);
										$.hasAjaxDefaultAction(response);
									}
							});
						case 'numeric':
							$maskNumeric = field.$input.clone();
							field.$input.hide();
							$maskNumeric
								.removeAttr('name')
								.insertBefore(field.$input)
								.autoNumeric('init', { aSep: crLang.line('NUMBER_THOUSANDS_SEP'), aDec: crLang.line('NUMBER_DEC_SEP'),  aSign: '', mDec: field.mDec } )
								.change( function(event) {
									$(event.target).next().val($(event.target).autoNumeric('get') ).change();
								});
							break;
						case 'groupCheckBox':	
							field.$input.removeAttr('name');
							var $field = field.$input.find('input:first').attr('name', field['name']);
							field.$input.data('$field', $field);
						
							field.$input.find('input[type=checkbox]')
								.click($.proxy(
									function(event) {
										this.checkGroupCheckBox($(event.target));
										this.updateGroupCheckBox($(event.target).parents('ul'));
									}
								, this))
								.each($.proxy(
									function (i, checkbox) {
										this.checkGroupCheckBox($(checkbox));
									}
								, this))
								
							this.updateGroupCheckBox(field.$input);
							break;
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
				'success': 	
					$.proxy(
						function(response) {
							if (this.options.callback != null) {
								if (typeof this.options.callback == 'string') {
									eval('this.options.callback = ' + this.options.callback);
								}
								this.options.callback(response);
								return;
							}
							
							if ($.hasAjaxDefaultAction(response) == true) { return; }

							if (this.$form.parents('.modal:first').length == true) {
								this.$form.parents('.modal:first').modal('hide');
								return;
							}
							if ($.url().param('urlList') != null) {
								$.goToUrlList();
								return;
							}
						}
					, this),
			});
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

						this.$fileupload
							.unbind('hidden.bs.modal')
							.on('hidden.bs.modal', 
							function() {
								var crForm = $(this).data('crForm');
								crForm.reloadGallery();
							}
						);
					}

					this.$fileupload.find('input[name=entityName]').val(this.fileupload.entityName);
					this.$fileupload.find('input[name=entityId]').val(this.fileupload.entityId);

					$.showModal(this.$fileupload, false, false);
				}
			, this));

			$('#fileupload').fileupload( { autoUpload: true, getFilesFromResponse: 
				function(data) {
					if ($.isArray(data.result.result.files)) { 
						return data.result.result.files;
					}
					
					$.hasAjaxDefaultAction(data.result);
					return [];
				} 
			});
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
			$('#fileupload tbody').children().remove();

			$.ajax({
				'url': 		this.fileupload.urlGet,
				'data': 	{ },
				'success': 	
					function (response) {
						if ($.hasAjaxDefaultAction(response) == true) { return; }
						
						var result = response['result'];
						
						var files = result.files;
						var fu = $('#fileupload').data('blueimpFileupload');
						fu._renderDownload(files).appendTo($('#fileupload tbody')).addClass('in')

						for (var i=0; i<result.files.length; i++) {
							var photo = result.files[i];
							
							$('<a class="thumbnail " data-skip-app-link="true" />')
								.append($('<img />').prop('src', photo.thumbnailUrl))
								.prop('href', photo.url)
								.prop('title', ''  /*photo.title*/)
								.appendTo($gallery.find('.thumbnails'));
						}
		
						$('img', $gallery).imgCenter( { show: false, createFrame: true } );
					},
			});
		},
		
		loadSubForm: function(field) {
			$.ajax( {
				'type': 		'get', 
				'url':			field.controller,
				'success': 		
					$.proxy( 
						function (response) {
							if ($.hasAjaxDefaultAction(response) == true) { return; }
							
							var result 	= response['result'];
							var list	= result['list'];
							field.$input.children().remove();
							
							var $div 	= $('<div class="table-responsive"/>').appendTo(field.$input);
							var $table 	= $('<table class="table table-hover" />').appendTo($div);
							var $thead 	= $('<thead/>').appendTo($table);
							var $tr 	= $('<tr class="label-primary"/>').appendTo($thead);
							
							for (var columnName in list['columns']) {
								var $th = $(' <th />')
									.text(list['columns'][columnName])
									.appendTo($tr);		
						
								if ($.isPlainObject(list['columns'][columnName])) {
									$th
										.text(list['columns'][columnName]['value'])
										.addClass(list['columns'][columnName]['class']);
								}
							}

							var $tbody = $(' <tbody />').appendTo($table);
							if (list['data'].length == 0) {
								$( '<tr class="warning"><td colspan="' + (Object.keys(list['columns']).length + 1) + '"> ' + crLang.line('No results') + ' </td></tr>').appendTo($tbody);
							}
							for (var i=0; i<list['data'].length; i++) {
								var row = list['data'][i];
								
								if ($.isPlainObject(row) == false) {
									$(row).appendTo($tbody);
								}
								else {
									var id 	= row[Object.keys(row)[0]];
									var $tr	= $( '<tr data-controller="' + base_url + list['controller'] + id +'">').appendTo($tbody);
							
									for (columnName in list['columns']) {
										var $td = $(' <td />')
											.html(row[columnName] || '')
											.appendTo($tr);
										
										if ($.isPlainObject(list['columns'][columnName])) {
											$td.addClass(list['columns'][columnName]['class']);
										}
									}
								}
							}

							$('<a class="btn btn-default btn-sm btnAdd" href="' + base_url + list['controller'] + '0" />') 
								.appendTo(field.$input)
								.append(' <i class="fa fa-plus"> </i> ')
								.append(' ' + crLang.line('Add'))
								.data( { 'crForm': this })
								.click(
									function() {
										$(this).data('crForm').showSubForm($(this).attr('href'), field); 
										return false;
									}
								);

							$tbody.find('.date, .datetime').each(
								function() {
									$.formatDate($(this));
								}
							);
							
							$tbody.find('tr').data( { 'crForm': this });
							$tbody.on('click', 'tr',
								function (event) {
									if ($(this).data('controller') == null) {
										return;
									}
									$(this).data('crForm').showSubForm($(this).data('controller'), field); 
								}
							);
		
							field.$input.change();
							this.resizeWindow();
						}
					, this)
			});
		},
		
		showSubForm: function(controller, field) {
			$.ajax( {
				'type': 		'get', 
				'url':			controller,
				'data': 		{ 'pageJson': true },
				'success': 		
					$.proxy( 
						function (response) {
							if ($.hasAjaxDefaultAction(response) == true) { return; }
							
							var $subform 		= $(document).crForm('renderPopupForm', response['result']['form']);
							var $modal			= $subform.parents('.modal');
							$subform.data('frmParent', this);
		
							$.showModal($modal, false);
							$modal.on('hidden.bs.modal', function() {
								$(this).find('form').data('frmParent').loadSubForm(field);
								$(this).remove();
							});
		
							if ($.isMobile() == false) {
								$subform.find('select, input[type=text]').first().focus();
							}
						}
					, this)
			});
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
					.autoNumeric('init', { aSep: crLang.line('NUMBER_THOUSANDS_SEP'), aDec: crLang.line('NUMBER_DEC_SEP'),  aSign: $currency.find('option:selected').text() +' ' } )
					.change( function(event) {
						$(event.target).next().val($(event.target).autoNumeric('get') ).change();
					});
					
				$maskExchange = $exchange.clone();
				$exchange.hide();
				$maskExchange
					.removeAttr('name')
					.insertBefore($exchange)
					.autoNumeric('init', { aSep: crLang.line('NUMBER_THOUSANDS_SEP'), aDec: crLang.line('NUMBER_DEC_SEP'),  aSign: '' } )
					.change( function(event) {
						$(event.target).next().val($(event.target).autoNumeric('get') ).change();
					});
				
				$total.autoNumeric('init', { vMax: 999999999999, aSep: crLang.line('NUMBER_THOUSANDS_SEP'), aDec: crLang.line('NUMBER_DEC_SEP'),  aSign: $.crSettings.defaultCurrencyName + ' ' } );

				this.$form.bind('submit', $.proxy(
					function($maskPrice, $maskExchange, event) {
						$maskPrice.change();
						$maskExchange.change();
					}
				, this, $maskPrice, $maskExchange));
				
								
				$total.data('init-price', true);
			}
			
			if ($currency.val() == $.crSettings.defaultCurrencyId) {
				$exchange.val(1);
				$exchange.prev().autoNumeric('set', 1);
			}

			$price.prev().autoNumeric('update', { aSign: $currency.find('option:selected').text() +' ' } )
			$total.autoNumeric('set', $price.val() * $exchange.val());
		},
		
		sumValues: function($total, aFieldName) {
			if ($total.data('init-price') == null) {
				$total.autoNumeric('init', { vMax: 999999999999, aSep: crLang.line('NUMBER_THOUSANDS_SEP'), aDec: crLang.line('NUMBER_DEC_SEP'),  aSign: $.crSettings.defaultCurrencyName + ' ' } );
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
				'success': 	
					$.proxy( 
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
		
		checkGroupCheckBox: function($checkbox) { 
			var $li = $checkbox.parents('li');
			$li.removeClass('active');
			if ($checkbox.is(':checked') == true) {
				$li.addClass('active');
			}
		},
		
		updateGroupCheckBox: function($input) {
			var value 		= [];
			var aCheckbox 	= $input.find('input[type=checkbox]');
			$input.data('$field').val('');
			
			for (var i=0; i<aCheckbox.length; i++) {
				var $checkbox = $(aCheckbox[i]);
				if ($checkbox.is(':checked') == true) {
					value.push($checkbox.val());
				}
			}
			if (value.length > 0) {
				$input.data('$field').val($.toJSON(value));
			}
		},
		
		changeField: function() {
			this.$btnSubmit.removeAttr('disabled');
		},
		
		resizeWindow: function() {
			if (this.$form.is(':visible') != true) {
				return;
			}
			var width = this.$form.width();
			this.$form.find('.table-responsive').css('max-width', width - 30 );
		}
	};
	
	
	renderCrForm = function(data, $parentNode) {
		var buttons 	= [
			'<button type="button" class="btn btn-default" onclick="$.goToUrlList();"><i class="fa fa-arrow-left"></i> ' + crLang.line('Back') + ' </button> ',
			'<button type="button" class="btn btn-danger"><i class="fa fa-trash-o"></i> ' + crLang.line('Delete') + ' </button>',
			'<button type="submit" class="btn btn-primary" disabled="disabled"><i class="fa fa-save"></i> ' + crLang.line('Save') + ' </button> '	
		];
		if (data['urlDelete'] == null) {
			delete buttons[1];
		}
		
		var pageName = location.href;
		if (pageName.indexOf('?') != -1) {
			pageName = pageName.substr(0, pageName.indexOf('?'));
		}

		data = $.extend({
			'action': 	pageName, 
			'frmId': 	'frmId',
			'buttons': 	buttons
		}, data);
					

		var $form = $('<form action="' + data['action'] + '" />')
			.attr('id', data['frmId'])
			.addClass('panel panel-default crForm form-horizontal')
			.attr('role', 'form')
			.appendTo($parentNode);

		var $div = $('<div class="panel-body" />').appendTo($form); 
		this.renderCrFormFields(data.fields, $div);

		if (data['buttons'].length != 0) {
			$div = $('<div class="form-actions panel-footer" > ').appendTo($form);
			for (var i=0; i<data['buttons'].length; i++) {
				$div
					.append($(data['buttons'][i]))
					.append(' ');
			}
		}

		return $form;
	},
	
	
	
	renderPopupForm = function(data) {
		var buttons 	= [
			'<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">' + crLang.line('Close') + '</button>',
			'<button type="button" class="btn btn-danger"><i class="fa fa-trash-o"></i> ' + crLang.line('Delete') + ' </button>',
			'<button type="submit" class="btn btn-primary" disabled="disabled"><i class="fa fa-save"></i> ' + crLang.line('Save') + ' </button> '	
		];
		if (data['urlDelete'] == null) {
			delete buttons[1];
		}
		
		data = $.extend({
			'frmId': 	'frmId',
			'buttons': 	buttons
		}, data);
				
		var $modal = $('\
			<div class="modal" role="dialog" >\
				<div class="modal-dialog" >\
					<div class="modal-content" >\
					</div>\
				</div>\
			</div>\
		');
		
		var $form = $('<form action="' + data['action'] + '" />')
			.attr('id', data['frmId'])
			.addClass('crForm form-horizontal')
			.attr('role', 'form')
			.appendTo($modal.find('.modal-content'));		

		var $modalHeader = $('\
			<div class="modal-header">\
				<button aria-hidden="true" data-dismiss="modal" class="close" type="button">\
					<i class="fa fa-times"></i>\
				</button>\
				<h4 />\
			</div>\
		').appendTo($form);

		$modalHeader.find('h4')
			.append('<i class="' + (data['icon'] != null ? data['icon'] : 'fa fa-edit') + '"></i>')
			.append(' ' + data['title']);
		
		var $modalBody 	= $('<div class="modal-body" />').appendTo($form);
		var $parentNode = $modalBody;
		
		if (data.info != null) {
			var $row 	= $('<div class="row">').appendTo($modalBody);
			$parentNode = $('<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">').appendTo($row);
			
			var $info 	= $('<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">').html(data.info.html).appendTo($row);
			if (data.info.position == 'left' ) {
				$info.before($parentNode);
			}
		}
		
		this.renderCrFormFields(data.fields, $parentNode);

		if (data['buttons'].length != 0) {
			$modalFooter = $('<div class="modal-footer" > ').appendTo($form);
			for (var i=0; i<data['buttons'].length; i++) {
				$modalFooter
					.append($(data['buttons'][i]))
					.append(' ');
			}
		}

		$form.crForm(data);

		return $form;
	},
	
	renderAjaxForm = function(data, $parentNode) {
		var buttons 	= [
			'<button type="button" class="btn btn-default" onclick="$.goToUrlList();"><i class="fa fa-arrow-left"></i> ' + crLang.line('Back') + ' </button>',
			'<button type="button" class="btn btn-danger"><i class="fa fa-trash-o"></i> ' + crLang.line('Delete') + ' </button>',
			'<button type="submit" class="btn btn-primary" disabled="disabled"><i class="fa fa-save"></i> ' + crLang.line('Save') + ' </button> '	
		];
		if (data['urlDelete'] == null) {
			delete buttons[1];
		}
		
		data = $.extend({
			'frmId': 	'frmId',
			'buttons': 	buttons
		}, data);
		
		var $form = $('<form action="' + data['action'] + '" />')
			.attr('id', data['frmId'])
			.addClass('panel panel-default crForm form-horizontal')
			.attr('role', 'form')
			.appendTo($parentNode);		

		if (data['title'] != null) {
			$('<div class="panel-heading" />').text(data['title']).appendTo($form);
		}

		var $div = $('<div class="panel-body" />').appendTo($form); 
		this.renderCrFormFields(data.fields, $div);

		if (data['buttons'].length != 0) {
			$modalFooter = $('<div class="form-actions panel-footer" > ').appendTo($form);
			for (var i=0; i<data['buttons'].length; i++) {
				$modalFooter
					.append($(data['buttons'][i]))
					.append(' ');
			}
		}

		$form.crForm(data);

		return $form;
	},
	
	renderCrFormFields = function(fields, $parentNode) {
		for (var name in fields) {
			var field 		= fields[name];
			var $fieldset 	= $('\
				<fieldset class="form-group">\
					<label class="col-xs-12 col-sm-3 col-md-3 col-lg-3 control-label">' + field['label'] + '</label>\
					<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9"> </div>\
				</fieldset>');
			$div = $fieldset.find('div');

			switch (field['type']) {
				case 'hidden':
					$fieldset = $('<input type="hidden" name="' + name + '" value="' + field['value'] + '" />');
					break;
				case 'text':
				case 'numeric':
					var $input = $('<input type="text" />')
						.attr('name', name)
						.val( field['value'])
						.addClass('form-control')
						.attr('placeholder',  field['placeholder'])
						.appendTo($div);

					if (field['disabled'] == true) {
						$input.attr('disabled', 'disabled');
					}					
					break;
				case 'date':
				case 'datetime':
					var $input = $('<input type="text" />')
						.attr('name', name)
						.val(field['value'])
						.addClass('form-control')
						.attr('size', field['type'] == 'datetime' ? 18 : 9)
						.attr('placeholder', crLang.line('DATE_FORMAT') + (field['type'] == 'datetime' ? ' hh:mm:ss' : '') );

					$datetime = $('<div class="input-group" style="width:1px" />').appendTo($div);
					$datetime.append($input);
					$datetime.append($('<span class="input-group-addon"><i class="glyphicon glyphicon-remove fa fa-times"></i></span>'));
					$datetime.append($('<span class="input-group-addon"><i class="glyphicon glyphicon-th icon-th fa fa-th"></i></span>'));
					break;
				case 'password':
					$div.append('<input type="password" name="' + name + '" class="form-control" />');
					break;
				case 'textarea':
					var $input = $('<textarea cols="40" rows="10" />')
						.attr('name', name)
						.text(field['value'])
						.addClass('form-control')
						.appendTo($div);
					break;
				case 'typeahead':
					$div.append('<input name="' + name + '"  type="text" class="form-control" />');
					break;		
				case 'dropdown':
					var source = field['source'];

					var $input = $('<select />')
						.addClass('form-control')
						.attr('name', name)
						.appendTo($div);

					if (field['appendNullOption'] == true) { // Apendeo aparte porque si lo hago en el objecto chrome lo desordena
						$('<option />')
							.val('')
							.text('-- ' + crLang.line('Choose') + ' --')
							.appendTo($input);
					}
					
					for (var item in source) {
						var item 	= field['source'][item];
						$('<option />')
							.val(item['id'])
							.text(item['text'])
							.appendTo($input);
					}
					
					$input.val(field['value']);
					if (field['disabled'] == true) {
						$input.attr('disabled', 'disabled');
					}
					break;
				case 'groupCheckBox':
					var showId 	= field['showId'] == true;
					var $input 	= $('<ul class="groupCheckBox" name="' + name + '" />').appendTo($div);
					
					$('<li><input type="text" style="display:none" /> </li>').appendTo($input);

					for (var item in field['source']) {
						var item 	= field['source'][item];
						$input.append('\
							<li>\
								<div class="checkbox">\
									 <label>\
										<input type="checkbox" value="' + item['id'] + '" ' + ($.inArray(item['id'], field['value']) != -1 ? ' checked="checked" ' : '' ) + ' />\
										' + item['text'] + (showId == true ? ' - ' + item['id'] : '')  +'\
									</label>\
								</div>\
							</li>');
					}
					break;
				case 'checkbox':
					$fieldset = $('\
						<fieldset class="form-group">\
							<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 "> </div>\
							<div class="col-xs-12 col-sm-9 col-md-9  col-lg-9 "> \
								<div class="checkbox" > \
									<label> \
										<input type="checkbox" name="' + name + '" value="on"  ' + (field['checked'] == true ? ' checked="checked" ' : '' ) + ' />  \
										' + field['label'] + '\
									</label> \
								</div> \
							</div> \
						</fieldset>');
					break;
				case 'gallery':
					$div.append($('\
						<div id="' + name + '" data-toggle="modal-gallery" data-target="#modal-gallery" class="gallery well" >\
							<button type="button" class="btn btn-success btn-sm btnEditPhotos fileinput-button">\
								<i class="fa fa-picture-o" ></i>\
								' + crLang.line('Edit pictures') + '\
							</button>\
							<div class="thumbnails" ></div>\
						</div>\
					'));
					break;
				case 'subform':
					$div.append('\
						<div name="' + name + '" class="subform ">\
							<div class="alert alert-warning">\
								<i class="fa fa-spinner fa-spin fa-lg"></i>\
								<small>' + crLang.line('loading ...') + '</small>\
							</div>\
						</div>');
					break;
				case 'tree':
					$fieldset = $('<fieldset class="form-group tree" />');
					this.renderCrFormTree(field['source'], field['value'], $fieldset);
					break;
				case 'link':
					$fieldset = $('\
						<fieldset class="form-group" >\
							<label class="hidden-xs col-sm-3 col-md-3 col-lg-3 control-label" />\
							<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">\
								<a href="' + field['value'] + '">' + field['label'] + '</a>\
						</fieldset>');
					break;
				case 'raty':
					$div.append('<div class="raty" name="' + name + '" />');
					break;
				case 'upload':
					$div.append('\
						<div class="col-md-5">\
							<span class="btn btn-success fileinput-button">\
								<i class="fa fa-plus"></i>\
								<span>' + crLang.line('Add File') + '</span> \
								<input type="file" name="userfile" > \
							</span> \
						</div> \
						<div class="col-md-5 fileupload-progress fade"> \
							<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100"> \
								<div class="progress-bar progress-bar-success bar bar-success" style="width:0%;"></div> \
							</div> \
							<div class="progress-extended">&nbsp;</div> \
						</div> ');
					break;
				case 'logo':
					// TODO: mejorar este field, agregar el btn upload, etc
					$div.append('<img src="' + field['value'] + '" />');
					break;
				case 'html':
					$fieldset = $(field['value']);
					break;
			}
			
			$($fieldset).appendTo($parentNode);
		}
	},
	
	renderCrFormTree = function(aTree, value, $parent){
		var $ul = $('<ul />').appendTo($parent);
		for (var i=0; i<aTree.length; i++) {
			var $li 	= $('<li/>').appendTo($ul);
			var $link 	= $('<a />')
				.attr('href', base_url + aTree[i]['url'])
				.text(aTree[i]['label'])
				.appendTo($li);
				
			if (value == aTree[i]['id']) {
				$link.addClass('selected');
			}
				
			if (aTree[i]['childs'].length > 0) {
				this.renderCrFormTree(aTree[i]['childs'], value, $li);
			}
		}

		return $ul;
	}	
})($);