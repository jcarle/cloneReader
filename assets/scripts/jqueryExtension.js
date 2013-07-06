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
	
	linkToObject: function(sTmp){
		var oParams = {};
		var aParamName = sTmp.split('&');
	
		for(var i=0;i<aParamName.length;i++){
			var aParamsValues = aParamName[i].split('=');
			if(aParamsValues[0].trim() != '' && aParamsValues[0].trim() != 'undefined') {
				oParams[aParamsValues[0]] = aParamsValues[1];
			}
		}
		return oParams;
	},
	
	objectToLink: function(oParams) {
		var sHref = '';
		for(var paramName in oParams) {
			sHref+= paramName + '=' + oParams[paramName] + '&';
		}
		return sHref;
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
	
	showWaiting: function(forceWaiting) {
		/*
		 * TODO:
		 * Para forzar que muestre o oculte el div, sumo o resto a la variable countProcess; pensar si hay una forma mas elegante de resolver esto. 
		 */
		if ($.countProcess < 0) { $.countProcess = 0; }
		if (forceWaiting == true) {$.countProcess++;}
		if (forceWaiting == false) {$.countProcess--;}
				
		var isWaiting = ($.countProcess > 0);

	
		$('#divWaiting').css( {
			'display':	isWaiting == true ? 'block' : 'none',
			'z-index': 	$.topZIndex('body > *') + 1
		} );
		document.body.className	=  (isWaiting == true ? 'isWaiting' : '');
	},	
	
	goToUrl: function(url) {
		$.showWaiting(true);
		location.href = url;
	},
	
	initMenu: function() { // TODO: mover esto de aca!
		$('.menu div ul.menuAdmin li ul').addClass('dropdown-menu');
		$('.menu div ul.menuAdmin > li > ul').addClass('pull-right');
		$('.menu div ul.menuAdmin > li > ul > li').addClass('dropdown-submenu dropdown-submenu-left');
		$('.menu div ul.menuAdmin li:first').addClass('btn-group');
		
		$('.menu div ul.menuAdmin li a:first')
			.html('<i class="icon-gear icon-2x" />')
			.addClass('btn btn-mini')
			.addClass('dropdown-toggle')
			.attr('data-toggle', 'dropdown');
	},
	
	loadSubForm: function(controller /*, field*/) {
		$.ajax( {
			type: 	'get', 
			url:	controller
		})
		.done( $.proxy( 
			function (result, a, b) {
				
				var frmId = $(result).attr('id');	
				$(result).appendTo($('body'));
				
				var $subform = $('#' + frmId);
				var options = $subform.jForm('options');
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
//										options.frmParentId.loadSubForm(field)
										$(this).remove();
									}
						})
			}
		, this));				
	}	
});


$(document).ready(function() {
	$.initMenu();
	resizeWindow();
	
	$.showWaiting(true);
	$('a').click(function(event) {
		var url = $(event.target).attr('href');
		if (url == null) {
			return;
		}
		$.goToUrl(url);
	});
	
	
	
	$.countProcess = 0;
	
	$.ajaxSetup({dataType: "json"});
	
	$(document).ajaxSend(
		function(event, jqXHR, ajaxOptions) {
			$.countProcess ++;
			$.showWaiting();	
		}
	);
	 
	$(document).ajaxComplete(
		function(event, jqXHR, ajaxOptions) {
			$.countProcess --;
			$.showWaiting();	
		}
	);
});
	
$(window).resize(function() {
	resizeWindow();
});

function resizeWindow() {
	$('.content')
		.css('min-height', 1)
		.css('min-height', $(document).outerHeight(true) - $('.menu').offset().top - $('.menu').outerHeight(true) - $('#footer').outerHeight(true) ); 

}

function cn(value) {
	console.log(value);
}
