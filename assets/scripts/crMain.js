crMain = {
	aPages: [],
	
	init: function(aMenu) {
		
		for (var menuName in aMenu) {
			var $menu = $(aMenu[menuName]['parent']);
			this.renderMenu(aMenu[menuName]['items'], aMenu[menuName]['className'], $menu);
			$menu.find('li ul').addClass('dropdown-menu');
		}
		
		
		$(window).on('hashchange',function(){
cn(location.hash.slice(1));
			
			crMain.loadUrl(location.hash.slice(1));
		});
		
this.loadUrl('users');
	},
	
	renderMenu: function(aMenu, className, $parent){
		if (aMenu.length == 0) {
			return;
		}
		
		var $ul = $('<ul></ul>').appendTo($parent);
		if (className != null) {
			$ul.addClass(className);
		}
	
		
		for (var i=0; i<aMenu.length; i++) {
			var label 	= _msg[aMenu[i]['label']];
			if (label == null) {
				label = aMenu[i]['label'];
			}
			var $li 	= $('<li></li>').appendTo($ul);
			var $link 	= $('<a></a>')
				.appendTo($li)
				.attr('title', label)
				.text(label);
			
			if (aMenu[i]['url'] != null) {
				$link.attr('href', '#' + aMenu[i]['url']);
			}
			if (aMenu[i]['icon'] != null) {
				$link.prepend(' <i class="' + aMenu[i]['icon'] + '" ></i> ')
			}
			
			if (aMenu[i]['childs'].length > 0) {
				$link.addClass('dropdown-toggle').attr('data-toggle', 'dropdown');
				this.renderMenu(aMenu[i]['childs'], null, $li);
			}
		}
	},
	
	goToUrl: function(controller) {
		location.hash = controller;
	},
	
	loadUrl: function(controller) {
		var pageName = location.hash.slice(1);		
		if (pageName.indexOf('?') != -1){
			pageName = pageName.substr(0, pageName.indexOf('?'));
		}

		if (this.aPages[pageName] == null) {
			this.aPages[pageName] = $('<div class="page ' + pageName + '"/>').appendTo($('.container'));
		}
		$('.container .page').hide();
		this.aPages[pageName].stop().show();

		if (this.ajax) {
			this.ajax.abort();
			this.ajax = null;
		}
				
		this.ajax = $.ajax({
			'url': 		base_url + controller,
			'async':	true,
			'success': 
				function(response) {
					if (response['code'] != true) {
						return $(document).crAlert(response['result']);
					}
					
					var result = response['result'];
					switch (result['js']) {
						case 'crList':
							crMain.renderCrList(result, crMain.aPages[pageName]);
							$(crMain.aPages[pageName]).find('.crList').crList();
							break;
					}
				}
		})		
	},
	
	renderCrList: function(data, $parent) {
		$parent.children().remove();
		
		var params 		= $.getUrlVars();
		var $crList		= $('<div class="crList"></div>').appendTo($parent);
		var $panel		= $('<div class="panel panel-default" />').appendTo($crList);
		var $form 		= $('\
			<form method="get" class="panel-heading form-inline" id="frmCrList" role="search">\
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

// TODO: implementar los filtros
				/*
<?php
if ($filters != null) {
	$this->load->view('includes/crFilterList', array('form' => array('fields' => $filters, 'frmId' => 'crFrmFilterList') ));			
}
*/
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
					.attr('href', '#' + data['controller'] + '?' + $.param(params))
					.text(data['sort'][key]);
		
				if (orderBy == key) {
					$link.prepend(' <i class="' + (orderDir == 'asc' ? 'icon-arrow-up' : 'icon-arrow-down') + ' icon-fixed-width " ></i> ');
				}
			}
		}

		var $div 		= $('<div class="table-responsive" />').appendTo($crList);
		var $table 		= $('<table class="table table-hover" />').appendTo($div);
		var $thead		= $('<thead />').appendTo($table);
		var $tr			= $('<tr class="label-primary" />').appendTo($thead);
		var urlDelete 	= data['urlDelete'] == true;
		var showId 		= data['showId'] == true;
		if (urlDelete == true) {
			$('<th class="checkbox">	<input type="checkbox"> </th>').appendTo($tr);	
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
			$( '<tr class="warning"><td colspan="' + (data['columns'].length + 1) + '"> ' + _msg['No results'] + ' </td></tr>').appendTo($tbody);
		}

		for (var i=0; i<data['data'].length; i++) {
			var row = data['data'][i];
			var id 	= row[Object.keys(row)[0]];
			var $tr	= $( '<tr data-controller="#' + data['controller'] + '/edit/' + id +'">').appendTo($tbody);
			
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

		// TODO: mejorar esta parte; hacer un metodo que formatee numeros en crFuncitons
		var foundRows = $('<span />')
			.text(data['foundRows'])
			.autoNumeric('init', { aSep: _msg['NUMBER_THOUSANDS_SEP'], aDec: _msg['NUMBER_DEC_SEP'],  aSign: '', mDec: 0 } )
			.text();
	
		$('\
			<a href="#' + data['controller'] + '/add" class="btnAdd btn btn-sm btn-success">\
				<i class="icon-file-alt icon-large"></i>\
				' + _msg['Add'] + '\
				</a>\
				<span>' + $.sprintf(_msg['%s rows'], foundRows)+ ' </span>\
		').appendTo($div);;
	
		var $div = $('<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6" />').appendTo($row);
		var $ul	= $('<ul class="pagination">').appendTo($div);
	
		var params 		= $.getUrlVars();
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
				var params 		= $.getUrlVars();
				params['page'] 	= page;			
				return '#' + data['controller'] + '?' + $.param(params);
			},
		});
	}
};



$(document).ready( function() { 
	crMain.init(APP_MENU); } 
);