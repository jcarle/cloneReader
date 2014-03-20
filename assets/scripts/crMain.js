crMain = { // TODO: renombrar a crPage ?
	aPages: [],
	aJs: {},
	
	init: function() {
		this.initEvents();
		this.iniAppAjax();
		crMenu.initMenu();
		resizeWindow();
		
		// TODO: seteamos el evento global o de a uno a cada link ?
		$.showWaiting(false);
	},
	
	initEvents: function() {
		// TODO: revisar, a ver si se puede sacar esta exception
		$('a:not(.btn-facebook):not(.btn-google)').live('click', function(event) {
			if (event.button != 0) {
				return;
			}
			if ($.support.pushState == false) {
				return;
			}
			
			var url = $(event.currentTarget).attr('href');
			if (url == null || url.substr(0, 1) == '#') {
				return;
			}
			event.preventDefault();
			return $.goToUrl(url);
		});	
		
		$.countProcess = 0;
		
		$.ajaxSetup({dataType: "json"});
		
		$(document).ajaxSend(
			function(event, jqXHR, ajaxOptions) {
				if (ajaxOptions.skipwWaiting === true) {
					return;
				}
				$.countProcess ++;
				$.showWaiting();	
			}
		);
		 
		$(document).ajaxComplete(
			function(event, jqXHR, ajaxOptions) {
				if (ajaxOptions.skipwWaiting === true) {
					return;
				}			
				$.countProcess --;
				$.showWaiting();	
			}
		);
		
		$(document).ajaxError(
			function(event, jqXHR, ajaxOptions) {
				if (jqXHR.status === 0 && jqXHR.statusText === 'abort') {
					return;
				}
				if (jqXHR.status === 0 && jqXHR.statusText === 'error') {
					$(document).crAlert( {
						'msg': 			_msg['Not connected. Please verify your network connection'],
						'isConfirm': 	true,
						'confirmText': 	_msg['Retry'],
						'callback': 	$.proxy(
							function() { $.ajax(ajaxOptions); }
						, this)
					});
					return;
				}
				
				var result = $.parseJSON(jqXHR.responseText);
				$.hasAjaxErrorAndShowAlert(result);
			}
		);	
	},
	
	iniAppAjax: function() {
		if ($.support.pushState == false) {
			return;
		}		
cn('iniAppAjax!');		
		
		$.ajax({
			'url': 		base_url + 'app/selectMenuAndTranslations',
			'async':	false,
			'success': 
				function(result) {
					_msg = result['result']['aLangs']; // TODO: meter _msg en algun lado, que no sea global
		
					var aMenu = result['result']['aMenu'];
					for (var menuName in aMenu) {
						var $menu = $(aMenu[menuName]['parent']);
						$menu.children().remove();
						crMenu.renderMenu(aMenu[menuName]['items'], aMenu[menuName]['className'], $menu);
					}
				}
		});

		$(window).bind("popstate", function () {  
			crMain.loadUrl(location.href);
		});  

//		if ($('.container > .page').length == 0) {
			crMain.loadUrl(location.href);
//		}
	},
	
	loadUrl: function(controller) {
		var pageName = this.getPageName();
		this.aPages[pageName] = $('.container > .' + pageName);
		if (this.aPages[pageName].length == 0) {
			this.aPages[pageName] = $('<div class="page ' + pageName + '"/>').appendTo($('.container'));
		}

		if (this.ajax) {
			this.ajax.abort();
			this.ajax = null;
		}
		
		var url = base_url + controller.replace(base_url, '');
		
		this.ajax = $.ajax({
			'url': 		url,
			'data': 	{ 'appType': 'ajax' },
			'async':	true,
			'success': 
				function(response) {
					if (response['code'] != true) {
						return $(document).crAlert(response['result']);
					}
					
					// FIXME: Elimino estos divs, sino se van agregando todo el tiempo. Son de objectos de jquery calendar, drodown, etc
					$('.datetimepicker, select2-drop, .select2-hidden-accessible').remove();

					$('.container > .page').hide();
					crMain.aPages[pageName].stop().show();
					
					var data 	= response['result'];
					var $page 	= crMain.aPages[pageName];
					
					$page.children().remove();
					
					crMain.renderPageTitle(data, $page);
					
					switch (data['js']) {
						case 'crList':
							$(null).crList($.extend({
								'autoRender': 	true,
								'$parentNode': 	$(crMain.aPages[pageName])
							} , data['list']));
							break;
						case 'crForm':
							$(null).crForm( $.extend({
								'autoRender': 	true,
								'$parentNode': 	$(crMain.aPages[pageName])
							} , data['form']));
							break;
						default:
							$page.append(data['html']);
					}
					
					if (data['aJs'] != null) {
						for (var i=0; i<data['aJs'].length; i++) {
							var fileName = data['aJs'][i];
							if (crMain.aJs[fileName] == null) {
								crMain.aJs[fileName] = fileName;
								$.getScript(base_url + 'assets/scripts/' + fileName);
							}
						}
					}
				}
		})		
	},
	
	renderPageTitle: function(data, $page) {
		$('title').text(data['title'] + ' | ' + SITE_NAME);
		
		if (data['breadcrumb'] != null) {
			$('<ol class="breadcrumb">').appendTo($page);
// TODO: implementar!			
			/*
			for ($breadcrumb as $link) {
				if (element('active', $link) == true) {
					echo '<li class="active"> '.$link['text'].'</li>';
				}
				else {
					echo '<li><a href="'.$link['href'].'">'.$link['text'].'</a></li>';
				} 
			}*/
		}

		if (data['showTitle'] == null) {
			data['showTitle'] = true;
		}
		if (data['showTitle'] == true) {
			$pageTitle = $('\
				<div class="pageTitle">\
					<h2> <small> </small></h2>\
				</div>\
			').appendTo($page);
			
			$pageTitle.find('h2').text(data['title']);
		}

	},
	
	getPageName: function() {
		var pageName = location.href.replace(base_url, '');
		if (pageName.indexOf('?') != -1){
			pageName = pageName.substr(0, pageName.indexOf('?'));
		}
		var position = pageName.indexOf('/edit/');
		if (position != -1){
			pageName = pageName.substr(0, position + 5);
		}
		
		pageName = pageName.split('/').join(' ').trim().split(' ').join('-')
		return 'cr-page-' + pageName;
	}
};

$(document).ready( function() {
	$.support.pushState = (history.pushState == false ? false : true);
	 
	crMain.init(); 
});
