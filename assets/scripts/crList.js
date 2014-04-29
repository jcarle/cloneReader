;(function($) {
	var 
		methods,
		crList;
		
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
					$element = renderCrList(options, options.$parentNode);
				}
				else { 
					return null;
				}
			}

			if ($element.data('crList') == null) {
				$element.data('crList', new crList($element, options));
			}
			
			return $element;
		}
	};

	$.fn.crList = function( method ) {
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on crList' );
		}  
	}
	
	crList = function($crList, options) {
		this.$crList		= $crList;
		this.$form 			= this.$crList.find('form');
		this.$table			= this.$crList.find('table');
		this.$crFilterList	= this.$crList.find('.crFilterList');
		this.$btnOrder		= this.$crList.find('.btnOrder');
		this.$btnFilter		= this.$crFilterList.prev();
		this.options	 	= $.extend({}, options );

		this.$table.find('tbody .date, tbody .datetime').each(
			function() {
				$.formatDate($(this));
			}
		);

		this.$filter = this.$form.find('input[name=filter]');
		
		this.$form.find('.icon-remove').parent()
			.css( { 'cursor': 'pointer', 'color': '#555555' } )
			.click($.proxy(
				function (event){
					this.$btnFilter.removeClass('btn-info');
					this.$btnOrder.removeClass('btn-info');
					this.$form.find('input[type=text], input[name=orderBy], input[name=orderDir], select').val('');
					this.$form.find('input:checked').attr('checked', false);
					this.$form.submit();
				}
			, this));
		
		this.$table.find('tbody tr').each($.proxy(
			function (event, tr){
				this.checkedRow(tr);
			}
		, this));
		
		if ($.trim($.url().param('filter')) != '' && $.isMobile() == false) {
			this.$filter.focus();
		}
	
		this.$crFilterList.click(function(event) {
			event.stopPropagation();
		});	
		
		if (this.$btnFilter.length != 0) {
			this.$form.addClass('hasBtnFilter'); 
		}
		if (this.$btnOrder.length != 0) {
			this.$form.addClass('hasBtnOrder');
		}
		
		if (options['filters'] != null) {
			this.$form.crForm( { 'fields': options['filters'] });
			var hasFilter = (this.$crFilterList.find('input[type=text][value!=""], input:checked, select[value!=""]').length != 0);
			if (hasFilter == true) {
				this.$btnFilter.addClass('btn-info');
			}
		}
		
		this.initEvents();
		this.resizeWindow();
	}
	
	crList.prototype = {
		initEvents: function() {
			$(window).resize($.proxy(
				function() {
					this.resizeWindow();
				}
			, this));

			this.$crList.find('.btnDelete').click($.proxy(
				function() { 
					var aDelete = [];
					var $input = this.$table.find('tr.info input');
					for (var i=0; i<$input.length; i++) {
						aDelete.push($($input[i]).val());
					}

					if (aDelete.length == 0) { return;  }
					
					$(document).crAlert( {
						'msg': 			_msg['Are you sure?'],
						'isConfirm': 	true,
						'callback': 	function() {}
					});
				}
			, this));
			
			this.$table.find('tbody tr td.checkbox').click(
				function(event) {
					event.stopPropagation();
				}
			);
			
			this.$table.find('tbody input[type=checkbox]').change( $.proxy(
				function(event) {
					this.checkedRow($(event.target).parent().parent());
				}
			, this));
			
			this.$table.find('thead input[type=checkbox]').change( $.proxy(
				function() {
					this.checkAll();
				}
			, this));			
			
			
			if (this.options['readOnly'] != true) {
				this.$table.find('tbody tr').on('click', 
					function (event) {
						$.goToUrl($(this).data('controller') + '?urlList=' + encodeURIComponent($.base64Encode(location.href)));
					}
				);
				
				this.$crList.find('.btnAdd').on('click',
					function (event) {
						$.goToUrl($(this).attr('href') + '?urlList=' + encodeURIComponent($.base64Encode(location.href)));
						event.preventDefault;
						return false;
					}
				);
			}			
			this.$form.unbind();

			this.$form.on('submit', 
				function(event) {
					event.stopPropagation();
					var $form = $(this);
					$.goToUrl($form.attr('action') + '?' + $form.serialize());
					return false;
				}
			);
		},
		
		checkedRow: function(row) {
			$(row).removeClass('info');
			
			if ($('input[type=checkbox]', row).is(':checked')) {
				$(row).addClass('info');
			}
			
			
			this.$table.find('thead input[type=checkbox]').removeAttr('checked', 'checked');
			if (!$('tbody input[type=checkbox]:not(:checked)').length) {
				this.$table.find('thead input[type=checkbox]').attr('checked', 'checked');
			}
		},
		
		checkAll: function() {
			this.$table.find('tbody input[type=checkbox]').removeAttr('checked', 'checked');
			
			if (this.$table.find('thead input[type=checkbox]').is(':checked')) {
				this.$table.find('tbody input[type=checkbox]').attr('checked', 'checked');
			}
			
			this.$table.find('tbody input[type=checkbox]').change();
		},
		
		resizeWindow: function() {
			this.$table.parent().removeClass('table-force-responsive');
			if (this.$table.outerWidth(true) > this.$table.parent().outerWidth(true)) {
				this.$table.parent().addClass('table-force-responsive');
			}
		}
	},
		
	renderCrList = function(data, $parentNode) {
		var params 		= $.url().param();
		var $crList		= $('<div class="crList"></div>').appendTo($parentNode);
		var $panel		= $('<div class="panel panel-default" />').appendTo($crList);
		var $form 		= $('\
			<form method="get" class="panel-heading form-inline" id="frmCrList" role="search" action="' + data['controller'] + '" >\
				<div class="btn-group">\
					<div class="input-group">\
						<span class="input-group-addon">\
							<i class="icon-remove" ></i>\
						</span>\
						<input type="text" name="filter" value="" class="form-control" placeholder="' + _msg['search'] + '" />\
						<span class="input-group-btn">\
							<button type="submit" class="btn btn-default">' + _msg['Search'] + '</button>\
						</span>\
					</div>\
				</div>\
			</form>\
		');
		$form.appendTo($panel);
		$form.find('input[name=filter]').val(params['filter']);
		
		if ($.trim(params['filter']) != '' && $.isMobile() == false) {
			$form.find('input[name=filter]').focus();
		}

		if (data['filters'] != null) {
			this.renderFilterFist(data['filters'], $form);
		}

		if (data['sort'] != null) {
			var defaultOrderBy 	= Object.keys(data['sort'])[0];
			var orderBy 		= params['orderBy'];
			if ($.inArray(orderBy, Object.keys(data['sort'])) == -1) {
				orderBy 	= defaultOrderBy;
			}
			var orderDir 	= params['orderDir'] == 'desc' ? 'desc' : 'asc';
			
			delete params['orderBy']; 
			delete params['orderDir'];
			delete params['page'];
	
			var $sort = $('\
				<div class="btn-group">\
					<input type="hidden" name="orderBy"  value="' + orderBy + '" />\
					<input type="hidden" name="orderDir" value="' + orderDir + '" />\
					<div class="dropdown">\
						<button type="button" class="btn btn-default dropdown-toggle dropdown-toggle btnOrder ' + (orderBy != defaultOrderBy || orderDir != 'asc' ? ' btn-info ' : '') + '" type="button" data-toggle="dropdown">\
							<i class="icon-sort-by-attributes" ></i>\
						</button>\
						<ul class="dropdown-menu pull-right" role="menu" />\
					</div>\
				</div>\
			').appendTo($form);
			
			var $ul = $sort.find('ul');	
	
			for (key in data['sort']) {
				params['orderBy'] 	= key;
				params['orderDir'] 	= (orderDir == 'desc' ? 'asc' : 'desc');
				
				var $li 	= $('<li/>').appendTo($ul);
				var $link 	= $('<a/>')
					.appendTo($li)
					.attr('href', base_url + data['controller'] + '?' + $.param(params))
					.text(data['sort'][key]);
		
				if (orderBy == key) {
					$link.prepend(' <i class="' + (orderDir == 'asc' ? 'icon-arrow-up' : 'icon-arrow-down') + ' icon-fixed-width " ></i> ');
				}
			}
		}

		var $div 		= $('<div class="table-responsive" />').appendTo($crList);
		var $table 		= $('<table class="table" />').appendTo($div);
		var $thead		= $('<thead />').appendTo($table);
		var $tr			= $('<tr class="label-primary" />').appendTo($thead);
		var urlDelete 	= data['urlDelete'] == true;
		var showId 		= data['showId'] == true;
		if (urlDelete == true) {
			$('<th class="checkbox">	<input type="checkbox"> </th>').appendTo($tr);	
		}
		if (data['readOnly'] != true) {
			$table.addClass('table-hover');
		}
		if (showId == true) {
			$('<th class="numeric"> # </th>').appendTo($tr);	
		}
	
		for (var columnName in data['columns']) {
			var $th = $(' <th />')
				.text(data['columns'][columnName])
				.appendTo($tr);		
	
			if ($.isPlainObject(data['columns'][columnName])) {
				$th
					.text(data['columns'][columnName]['value'])
					.addClass(data['columns'][columnName]['class']);
			}
		}

		var $tbody = $(' <tbody />').appendTo($table);
		if (data['data'].length == 0) {
			$( '<tr class="warning"><td colspan="' + (Object.keys(data['columns']).length + 1) + '"> ' + _msg['No results'] + ' </td></tr>').appendTo($tbody);
		}

		for (var i=0; i<data['data'].length; i++) {
			var row = data['data'][i];
			var id 	= row[Object.keys(row)[0]];
			var $tr	= $( '<tr data-controller="' + base_url + data['controller'] + '/edit/' + id +'">').appendTo($tbody);
			
			if (urlDelete == true) {	
				$('	<td class="checkbox"> <input name="chkDelete" value="' + id + '" /> </td> ').appendTo($tr);
			}
			if (showId == true) {
				$('<td class="numeric" />').appendTo($tr).text(id);
			}
	
			for (columnName in data['columns']) {
				var $td = $(' <td />')
					.text(row[columnName] || '')
					.appendTo($tr);
				
				if ($.isPlainObject(data['columns'][columnName])) {
					$td.addClass(data['columns'][columnName]['class']);
				}
			}
		}

		var $footer = $('<div class="panel panel-default footer" />').appendTo($crList);
		var $row	= $('<div class="panel-footer row" />').appendTo($footer);
		var $div 	= $('<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6" />').appendTo($row);

		if (urlDelete == true) {
			$('<a class="btnDelete btn btn-sm btn-danger" > <i class="icon-trash icon-large"></i> ' + _msg['Delete'] + ' </a>').appendTo($div);
		}

		$('\
			<a href="' + data['controller'] + '/add" class="btnAdd btn btn-sm btn-success">\
				<i class="icon-file-alt icon-large"></i>\
				' + _msg['Add'] + '\
			</a>\
			<span>' + $.sprintf(_msg['%s rows'], $.formatNumber(data['foundRows']))+ ' </span>\
		').appendTo($div);;
	
		var $div = $('<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6" />').appendTo($row);
		var $ul	= $('<ul class="pagination">').appendTo($div);
	
		var params 		= $.url().param();
		var currentPage = params['page'];
		var totalPages	= Math.ceil(data['foundRows'] / PAGE_SIZE);
		delete params['page'];
	
		$ul.bootstrapPaginator({
			'bootstrapMajorVersion': 	3,
			'currentPage': 				currentPage,
			'numberOfPages': 			5, 
			'totalPages': 				totalPages,
			'totalRows':				data['foundRows'],
			'itemTexts': 				function (type, page, current) {
				switch (type) {
					case "first":
						return "1";
					case "prev":
						return "<";
					case "next":
						return ">";
					case "last":
						return totalPages;
					case "page":
						return page;
				}
			},	
			'tooltipTitles': 			function (type, page, current) {
				return null;
			},
			'shouldShowPage': 			function(type, page, current){
				switch(type) {
					case "first":
						return (current > 5);
					case "prev":
						return (current != 1);
					case "next":
					case "last":
						if (current == page || current == totalPages) {
							return false;
						}
						return true;
					default:
						return true;
				}
			},
			'pageUrl': 				function(type, page, current){
				var params 		= $.url().param();
				params['page'] 	= page;			
				return base_url + data['controller'] + '?' + $.param(params);
			},
		});
		
		return $crList;
	},

	renderFilterFist = function(fields, $parentNode) {
		$parentNode.append('\
			<div class="btn-group"> \
				<div class="dropdown"> \
					<button type="button" class="btn btn-default dropdown-toggle dropdown-toggle" type="button" data-toggle="dropdown"> \
						<i class="icon-filter" ></i> \
					</button> \
					<div class="crFilterList  panel panel-default fade in crForm form-horizontal dropdown-menu"> \
						<div class="panel-heading"> ' + _msg['Filter'] + ' </div> \
						<div class="panel-body"> </div> \
						<div class="modal-footer form-actions"> \
							<button type="submit" class="btn btn-default"> <i class="icon-search" ></i> ' + _msg['Search'] + ' </button> \
						</div> \
					</div> \
				</div> \
			</div> ');
			
		$(document).crForm('renderCrFormFields', fields, $parentNode.find('.panel-body'));
	}
})($);
