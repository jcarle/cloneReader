crMain = {
	aPages: [],

	init: function() {
		$.support.pushState = (history.pushState == false ? false : true);

		crMenu.initMenu();
		this.initEvents();
		this.iniAppAjax();
		resizeWindow();
		$.showWaiting();
	},

	initEvents: function() {
		$.countProcess = 0;

		$(window).bind('beforeunload', function(){
			$.showWaiting(true);
			$.isUnloadPage = true; // Para evitar tirar el error de conección perdida si unlodean la page
		});

		$('body').on('click', 'a',
			function(event) {
				crMain.clickOnLink(event);
			}
		);

		$.ajaxSetup({'dataType': 'json'});


		/**
		 * Propiedades por default para los ajax:
		 * 		skipwWaiting: omite postrar el divWaiting en cada peticion
		 * */
		$(document).ajaxSend(
			function(event, jqXHR, ajaxOptions) {
				if (ajaxOptions.skipwWaiting === true) {
					return;
				}
				$.countProcess++;
				$.showWaiting();
			}
		);

		$(document).ajaxComplete(
			function(event, jqXHR, ajaxOptions) {
				if (ajaxOptions.skipwWaiting === true) {
					return;
				}
				$.countProcess--;
				$.showWaiting();
			}
		);

		$(document).ajaxError(
			function(event, jqXHR, ajaxOptions) {
				if ($.isUnloadPage == true) {
					return;
				}
				if (jqXHR.status === 0 && jqXHR.statusText === 'abort') {
					return;
				}
				if (jqXHR.status === 0 && jqXHR.statusText === 'error') {
					$(document).crAlert( {
						'msg':         crLang.line('Not connected. Please verify your network connection'),
						'isConfirm':   true,
						'confirmText': crLang.line('Retry'),
						'callback':    $.proxy(
							function() { $.ajax(ajaxOptions); }
						, this)
					});
					return;
				}

				var response = $.parseJSON(jqXHR.responseText);
				if ($.hasAjaxDefaultAction(response) == true) { return; }

				crMain.renderPage(response, ajaxOptions.pageName);
			}
		);

		$('#header .navbar-collapse')
			.on('shown.bs.collapse', function() {
				$('body').addClass('navbarVisible');
			})
			.on('hidden.bs.collapse', function() {
				$('body').removeClass('navbarVisible');
			});
	},

	iniAppAjax: function() {
		if ($.support.pushState == false) {
			return;
		}

		$(window).bind('popstate', function () {
			crMain.loadUrl(location.href);
		});

		if ($('.pageContainer > .cr-page').length == 0) {
			crMain.loadUrl(location.href);
		}
	},

	reloadMenu: function(async) {
		$.ajax({
			'url':   $.base_url('app/selectMenu'),
			'async': true,
			'success':
				function(response) {
					var aMenu = response['result']['aMenu'];
					for (var menuName in aMenu) {
						var $parent = $(aMenu[menuName]['parent']);
						$parent.find('.crMenu').remove();
						crMenu.renderMenu(aMenu[menuName]['items'], aMenu[menuName]['className'], $parent);
					}
					crMenu.initMenu();
				}
		});
	},

	/**
	 * Propiedades que se setean desde el js de cada page; se guardan dentro $page.data(); se pueden setear desde la view ajax, o desde un js
	 * 		notRefresh: no vuelve a pedir la page, solo muestra lo que ya hay en memoria
	 * 		skipScrollTop: evita el scroll al cargar/mostrar la page
	 * Eventos que dispara cada page; hay que setearlo en el js de cada page
	 * 		loadUrl: se lanza siempre que pasa por este metodo. Es últi en una $page que tiene notRefresh=true y que cambiaron sus parametros
	 * 		onHide: se lanza al ocultar la page
	 * 		onVisible: se lanza al mostrar la page
	 * Eventos que dispara body:
	 * 		showPage: se lanza al cambiar de page
	 * */
	loadUrl: function(controller) {
		var pageName = this.getPageName();
		this.aPages[pageName] = $('.pageContainer > .' + pageName);
		if (this.aPages[pageName].length == 0) {
			this.aPages[pageName] = $('<div class="cr-page ' + pageName + '"/>').appendTo($('.pageContainer'));
		}

		var $page = this.aPages[pageName];
		$page.trigger('loadUrl');

		if ($page.children().length > 0 && $page.is(':visible') == true && location.hash != '') {
			return;
		}

		if (this.ajax) {
			this.ajax.abort();
			this.ajax = null;
		}

		var url = $.base_url(controller);
		if ($page.data('notRefresh') == true) {
			if ($page.is(':visible') == true) {
				return;
			}
			this.showPage(pageName);
			if ($page.data('skipScrollTop') != true) {
				$('html, body').animate({ scrollTop: 0 }, 'fast');
			}
			return;
		}

		this.ajax = $.ajax({
			'url':      url,
			'data':     { 'pageJson': true },
			'async':    true,
			'pageName': pageName,
			'success':  $.proxy(
				function(pageName, response) {
					if ($.hasAjaxDefaultAction(response) == true) { return; }
					this.renderPage(response, pageName);
					if ($page.data('skipScrollTop') != true) {
						$('html, body').animate({ scrollTop: 0 }, 'fast');
					}
				}
			, this, pageName)
		});
	},

	loadUploadFile: function() {
		if (this.loadedUploadFile == true) {
			return;
		}

		$.ajax({
			'url':      $.base_url('app/uploadFile'),
			'async':    false,
			'dataType': 'text',
			'success':
				function(response) {
					$('body').append(response);
					crMain.loadedUploadFile = true;
				}
		});
	},

	renderPage: function(response, pageName) {
		var data   = response['result'];
		var $page  = crMain.aPages[pageName];
		$page.data(data);

		crMain.showPage(pageName);
		$page.children().remove();
		crMain.renderPageTitle(data, $page);

		switch (data['js']) {
			case 'crList':
				$(null).crList($.extend({
					'autoRender':   true,
					'$parentNode':  $(crMain.aPages[pageName])
				} , data['list']));
				break;
			case 'crForm':
				$(null).crForm( $.extend({
					'autoRender':  true,
					'$parentNode': $(crMain.aPages[pageName])
				} , data['form']));
				break;
			default:
				$page.append(data['html']);
		}
	},

	renderPageTitle: function(data, $page) {
		$('title').text(data['meta']['title'] + (crSettings.addTitleSiteName == true ? ' | ' + crSettings.siteName : ''));

		if (data['breadcrumb'].length != 0) {
			var $ol = $('<ol class="breadcrumb">').appendTo($page);

			for (var i=0; i<data['breadcrumb'].length; i++) {
				var link = data['breadcrumb'][i];
				if (link['active'] == true) {
					$('<li class="active" />').text(link['text']).appendTo($ol);
				}
				else {
					var $li = $('<li/>').appendTo($ol);
					$('<a />').attr('href', link['href']).text(link['text']).appendTo($li);
				}
			}
		}

		if (data['showTitle'] == null) {
			data['showTitle'] = true;
		}
		if (data['showTitle'] == true) {
			var $pageHeader = $('\
				<div class="page-header">\
					<h1> <small> </small></h1>\
				</div>\
			').appendTo($page);

			$pageHeader.find('h1').text(data['meta']['h1']);
		}
	},

	showPage: function(pageName) {
		$.showWaiting(true);

		$('.datetimepicker, .select2-drop, .select2-drop-mask, .select2-hidden-accessible').hide(); // FIXME: Elimino estos divs, sino se van agregando todo el tiempo. Son de objectos de jquery calendar, drodown, etc
		$('.modal').modal('hide'); // Elimino los .alers y los .modal que pueda haber al hacer history.back

		var $page       = this.aPages[pageName];
		var $otherPages = $('.pageContainer > .cr-page:visible:not(.' + pageName + ')');
		var meta        = $page.data('meta');

		$otherPages.hide().trigger('onHide');

		$('title').text(meta['title'] + (crSettings.addTitleSiteName == true ? ' | ' + crSettings.siteName : ''));
		$page.stop().show();
		$page.trigger('onVisible');
		$('body').trigger('showPage');

		$.showWaiting(false);
	},

	getPageName: function() {
		var pageName = location.href.replace(crSettings.base_url, '');
		if (pageName.indexOf('?') != -1){
			pageName = pageName.substr(0, pageName.indexOf('?'));
		}
		if (pageName.indexOf('#') != -1) {
			pageName = pageName.substr(0, pageName.indexOf('#'));
		}
		var aTmp = pageName.split('/');
		var controller = aTmp[0];
		if (controller.trim() == '') {
			controller = crSettings.pageHome;
		}

		return 'cr-page-' + controller + (aTmp.length > 1 ? '-' + aTmp[1] : '');
	},

	/**
	 * Modifica un link para que la page se carge por ajax (si $.support.pushState=true) o para mostrar el $.showWaiting antes de redireccionar
	 * Para omitir este comportamiento se puede setear
	 * 		skip-app-link = true 		como property de un <a/>
 	 */
	clickOnLink: function(event) {
		if (event.button != 0) {
			return;
		}

		var $link = $(event.currentTarget);
		if ($link.attr('target') != null) {
			return;
		}
		if ($link.data('skip-app-link') == true) {
			return;
		}
		var url = $link.attr('href');
		if (url == null || url.substr(0, 1) == '#' || url.substr(0, 10) == 'javascript') {
			return;
		}
		if (url.substr(0, 7) == 'http://' || url.substr(0, 8) == 'https://') {
			if (url.indexOf(crSettings.base_url) == -1) {
				return;
			}
		}

		$.hideMobileNavbar();

		event.preventDefault();
		return $.goToUrl(url);
	}
};

$(document).ready( function() {
	crMain.init();
});
