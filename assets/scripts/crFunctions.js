$.extend({
	namespace: function() {
		var a = arguments,
			o = null,
			i, j, d;
		
		for (i = 0; i < a.length; i = i + 1) {
			d = a[i].split(".");
			o = window;
			
			for (j=0; j<d.length; j=j+1) {
				o[d[j]] = o[d[j]] || {};
				o = o[d[j]];
			}
		}
		
		return o;
	},
	
	base_url: function() {
		// TODO: implementar !
	},

	isMobile: function() {
		return $(window).width() < 768;		
	},	
	
	validateEmail: function(value) {
		if (value == '') {
			return true;
		}
		var filter = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
		return !!filter.test(value);
	},

	validateUrl: function(value) {
		if (value.length == 0) { return true; }
 
		if(!/^(https?|ftp):\/\//i.test(value)) {
			value = 'http://' + value;
		}
		
		var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
		return regexp.test(value);
	},
	
	strPad: function(i,l,s) {
		var o = i.toString();
		if (!s) { s = '0'; }
		while (o.length < l) {
			o = s + o;
		}
		return o;
	},	
	
	base64Decode: function( data ) {
		var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
		var o1, o2, o3, h1, h2, h3, h4, bits, i = 0, ac = 0, dec = "", tmp_arr = [];
	
		if (!data) {
			return data;
		}
	
		data += '';
	
		do {
			h1 = b64.indexOf(data.charAt(i++));
			h2 = b64.indexOf(data.charAt(i++));
			h3 = b64.indexOf(data.charAt(i++));
			h4 = b64.indexOf(data.charAt(i++));
	
			bits = h1<<18 | h2<<12 | h3<<6 | h4;
	
			o1 = bits>>16 & 0xff;
			o2 = bits>>8 & 0xff;
			o3 = bits & 0xff;
	
			if (h3 == 64) {
				tmp_arr[ac++] = String.fromCharCode(o1);
			} else if (h4 == 64) {
				tmp_arr[ac++] = String.fromCharCode(o1, o2);
			} else {
				tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
			}
		} while (i < data.length);
	
		dec = tmp_arr.join('');
		dec = $.utf8Decode(dec);
	
		return dec;
	},
	
	base64Encode: function(data) {
		var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
		var o1, o2, o3, h1, h2, h3, h4, bits, i = 0, ac = 0, enc="", tmp_arr = [];
	
		if (!data) {
			return data;
		}
	
		data = $.utf8Encode(data+'');
	
		do { // pack three octets into four hexets
			o1 = data.charCodeAt(i++);
			o2 = data.charCodeAt(i++);
			o3 = data.charCodeAt(i++);
	
			bits = o1<<16 | o2<<8 | o3; h1 = bits>>18 & 0x3f;
			h2 = bits>>12 & 0x3f;
			h3 = bits>>6 & 0x3f;
			h4 = bits & 0x3f;
	
			// use hexets to index into b64, and append result to encoded string
			tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
		} while (i < data.length);
	
		enc = tmp_arr.join('');
	
		switch (data.length % 3) {
			case 1:
				enc = enc.slice(0, -2) + '==';
				break;
			case 2:
				enc = enc.slice(0, -1) + '=';
				break;
		}
	
		return enc;
	},
	
	utf8Decode: function( str_data ) {
		var tmp_arr = [], i = 0, ac = 0, c1 = 0, c2 = 0, c3 = 0;
	
		str_data += '';
	
		while ( i < str_data.length ) {
			c1 = str_data.charCodeAt(i);
			if (c1 < 128) {
				tmp_arr[ac++] = String.fromCharCode(c1);
				i++;
			} else if ((c1 > 191) && (c1 < 224)) {
				c2 = str_data.charCodeAt(i+1);
				tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
				i += 2;
			} else {
				c2 = str_data.charCodeAt(i+1);
				c3 = str_data.charCodeAt(i+2);
				tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
	
		return tmp_arr.join('');
	},
	
	utf8Encode: function( argString ) {
		var string = (argString+''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
	
		var utftext = "", start, end, stringl = 0;
	
		start = end = 0;
		stringl = string.length;
		for (var n = 0; n < stringl; n++) {
			var c1 = string.charCodeAt(n);
			var enc = null;
	
			if (c1 < 128) {
				end++;
			}
			else if (c1 > 127 && c1 < 2048) {
				enc = String.fromCharCode((c1 >> 6) | 192) + String.fromCharCode((c1 & 63) | 128);
			}
			else {
				enc = String.fromCharCode((c1 >> 12) | 224) + String.fromCharCode(((c1 >> 6) & 63) | 128) + String.fromCharCode((c1 & 63) | 128);
			}
			if (enc !== null) {
				if (end > start) {
					utftext += string.slice(start, end);
				}
				utftext += enc;
				start = end = n+1;
			}
		}
	
		if (end > start) {
			utftext += string.slice(start, stringl);
		}
	
		return utftext;
	},
	
	stripTags: function(str, allowed_tags) {
		var key = '', allowed = false;
		var matches = [];
		var allowed_array = [];
		var allowed_tag = '';
		var i = 0;
		var k = '';
		var html = '';
		var replacer = function (search, replace, str) {
			return str.split(search).join(replace);
		};
		// Build allowes tags associative array
		if (allowed_tags) {
			allowed_array = allowed_tags.match(/([a-zA-Z0-9]+)/gi);
		}
		str += '';
		// Match tags
		matches = str.match(/(<\/?[\S][^>]*>)/gi);
		// Go through all HTML tags
		for (key in matches) {
			if (isNaN(key)) {
				// IE7 Hack
				continue;
			}
			// Save HTML tag
			html = matches[key].toString();
			// Is tag not in allowed list? Remove from str!
			allowed = false;
			// Go through all allowed tags
			for (k in allowed_array) {
				// Init
				allowed_tag = allowed_array[k];
				i = -1;
				if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+'>');}
				if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+' ');}
				if (i != 0) { i = html.toLowerCase().indexOf('</'+allowed_tag)   ;}
	
				// Determine
				if (i == 0) {
					allowed = true;
					break;
				}
			}
			if (!allowed) {
				str = replacer(html, "", str); // Custom replace. No regexing
			}
		}
		return str;
	},
	
	showNotification: function(msg, className){
		if (className == null) {
			className = 'alert-success';
		}
		$div = $('<div class="notification alert ' + className +' fade in navbar-fixed-top"><strong>' + msg + '</strong></div>')
			.appendTo('body')
			.fadeTo('slow', 0.95).delay(2000).slideUp('slow');
	},
	
	showWaiting: function(forceWaiting) {
		/*
		 * TODO:
		 * Para forzar que muestre o oculte el div, sumo o resto a la variable countProcess; pensar si hay una forma mas elegante de resolver esto. 
		 */
		if ($.countProcess < 0) { $.countProcess = 0; }
		if (forceWaiting == true) {$.countProcess++;}
		if (forceWaiting == false) {$.countProcess--;}
				
		var isLoading = ($.countProcess > 0);

	
		$('#divWaiting').css( { 'display':	isLoading == true ? 'block' : 'none' } );
		
		$('#divWaiting').appendTo('body');
		
		$('body').removeClass('isLoading');
		if (isLoading == true) {
			$('body').addClass('isLoading');
		}
	},	
	
	goToUrl: function(url) {
		if ($.support.pushState == false) {
			$.showWaiting(true);
			location.href = url;
			return;
		}

		history.pushState(null, null, url);
		crMain.loadUrl(url);
	},
	
	goToUrlList: function() {
		var urlList = $.url().param('urlList');
		if (urlList != null) {
			$.goToUrl($.base64Decode(decodeURIComponent(urlList)));
		}
	},
	
	reloadUrl: function() {
		if ($.support.pushState == false) {
			$.showWaiting(true);	
			location.reload();
			return;
		}
		
		crMain.loadUrl(location.href);
	},
	
	ISODateString: function(d){
		function pad(n) {return n<10 ? '0'+n : n}
		return d.getUTCFullYear()+'-'
		+ pad(d.getUTCMonth()+1)+'-'
		+ pad(d.getUTCDate()) +' '
		+ pad(d.getUTCHours())+':'
		+ pad(d.getUTCMinutes())+':'
		+ pad(d.getUTCSeconds())
	},
	
	formatDate: function($element) {
		if ($.crSettings.momentLoaded != true) {
			$.crSettings.momentLoaded = true;
			moment.lang($.crSettings.langId);
		}
		
		if ($element.data('datetime') == null) {
			$element.data('datetime', $element.text());
		}

		var datetime = $element.data('datetime');
		if (datetime == '') {
			return;
		}
		if (moment(datetime, 'YYYY-MM-DDTHH:mm:ss').isValid() == false) {
			$element.text('');
			return;
		}
		
		var $moment = moment(datetime, 'YYYY-MM-DDTHH:mm:ss' );
		var format  = crLang.line('MOMENT_DATE_FORMAT');
		if ($element.hasClass('datetime')) {
			format += ' HH:mm:ss';
		}
		
		$element.attr('title', $moment.format(format) );
		
		if ($element.hasClass('fromNow')) {
			$element.text( $moment.fromNow() );
		}
		else {
			$element.text( $moment.format( format) );
		}
	},
	
	hideMobileNavbar: function() {
		if ($.isMobile() == true) {
			if ($('.navbar-ex1-collapse').is(':visible') == true) {
				$('.navbar-ex1-collapse').collapse('hide');
			}
		}
	},	
	
	showModal: function($modal, keyboard, onCloseRemove) {
		$('body').addClass('modal-open');
		
		$modal.data('onCloseRemove', onCloseRemove == null ? true : onCloseRemove);
		
		$modal.modal( { 'backdrop': 'static', 'keyboard': keyboard });

		$('.modal').css('z-index', 1039);
		
		$(document).unbind('hidden.bs.modal');
		$(document).bind('hidden.bs.modal', function (event) {
			if ($(event.target).data('onCloseRemove') == true) {
				$(event.target).remove();
				$(this).removeData('bs.modal');
			}
			
			$(document.body).removeClass('modal-open');
			if ($('.modal-backdrop').length > 0) {
				$('.modal-backdrop').last().show();
				$('body').addClass('modal-open');
				$('.modal:last').css('z-index', 1050);
			}
		}); 
		
		$(document).off('focusin.modal');
		
		$('.modal-backdrop').hide();

		$('.modal-backdrop:last')
			.css( {'opacity': 0.3  } )
			.show();
		$('.modal:last').css('z-index', 1050);
	},
	
	/**
	 * 	Ejecutar las acciones por defecto de una peticion ajax (alerts, redirects, notifications, etc)
	 * 	Params:
	 * 		skipAppLink					fuerza la variable $.support.pushState=false; se utiliza para un hard redirect
	 * 		goToUrl						carga una url
	 * 		notification				muestra una notificación
	 * 		msg							muestra un alert, y al cerrarlo carga una url
	 * 		loadMenuAndTranslations		vuelve a pedir el menu y las traducciones
	 * 		reloadUrl					vuelve a cargar la url actual
	 * 		formErrors					un array con el formato: {'fieldName': 'errorMessage' }. 
	 * 										muestra un alert con los errores del form; 
	 * 										en las  llamadas a esta funcion desde crForm se agrega la referencia "response['result']['crForm']" para agregar el has-error a los fields con errores
	 */
	hasAjaxDefaultAction: function(response) {
		if (response == null) {
			$(document).crAlert('error');
			return true;
		}
		var result = response['result'];
		
		if (result['loadMenuAndTranslations'] == true) {
			crMain.loadMenuAndTranslations(true);
		}
		
		if (result['skipAppLink'] == true) {
			$.support.pushState = false;
		}
		
		if (response['code'] != true && result['crForm'] != null && result['formErrors'] != null) {
			var msg = '';
			for (var fieldName in result['formErrors']){
				result['crForm'].setErrorField(fieldName);
				msg += '<p>' + result['formErrors'][fieldName] + '</p>';
			}
			if (msg != '') {
				$(document).crAlert(msg);
				return true;
			}
		}
		
		if (response['code'] != true) {
			$(document).crAlert(result);
			return true;
		}
		
		if (result['msg'] != null && result['goToUrl'] != null) {
			$(document).crAlert({
				'msg':      result['msg'],
				'callback': function() {
					$.goToUrl(result['goToUrl']);
				}
			});
			return true;
		}
		if (result['notification'] != null) {
			$.showNotification(result['notification']);
			return true;
		}
		if (result['goToUrl'] != null) {
			$.goToUrl(result['goToUrl']);
			return true;
		}
		
		if (result['reloadUrl'] == true) {
			$.reloadUrl();
			return true;
		}
		if (result['msg'] != null) {
			$(document).crAlert({
				'msg':      result['msg']
			});
			return true;
		}

		return false;
	},
	
	showPopupForm: function(form) {
		var $subform 		= $(document).crForm('renderPopupForm', form);
		var $modal			= $subform.parents('.modal');
		
		$.showModal($modal, false);
		
		return $modal;
	},
	
	formatNumber: function(value) { // TODO: ver si hay alguna manera de que autoNumeric devuelva el numero formateado sin tener que crear un $elemento
		return $('<span />')
			.text(value)
			.autoNumeric('init', { aSep: crLang.line('NUMBER_THOUSANDS_SEP'), aDec: crLang.line('NUMBER_DEC_SEP'),  aSign: '', mDec: 0 } )
			.text();	
	},
	
	normalizeLang: function(langId) {
		// FIXME: mejorar esto, pone una parte del langId en mayusculas. Se usa en datetimepicker
		var aTmp = langId.split('-');
		if (aTmp.length == 2) {
			return aTmp[0] + '-' + aTmp[1].toUpperCase();
		}
		
		return langId;
	},
	
	initGallery: function($gallery) {
		if ($gallery.data('initGallery') == true) {
			return;
		}
		
		$gallery.on('click', 
			function(event) {
				var target 	= event.target;
				if ($(target).hasClass('thumbnail') == false) {
					return;
				}
				var link    = target.src ? $(target).parents('a').get(0) : target;
				var options = {index: link, event: event, startSlideshow: true, slideshowInterval: 5000, stretchImages: false};
				var links   = this.getElementsByTagName('a');
				blueimp.Gallery(links, options);
			}
		);
		
		$gallery.data('initGallery', true);
	}
});

$(window).resize(function() {
	resizeWindow();
});

function resizeWindow() {
	return;
	$('.content')
		.css('min-height', 1)
		.css('min-height', $(document).outerHeight(true) - $('.menu').offset().top - $('.menu').outerHeight(true) - $('footer').outerHeight(true) ); 
}

function cn(value) {
	console.log(value);
}
