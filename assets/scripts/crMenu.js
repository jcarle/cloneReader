crMenu = {
	aSkipAppLink: ['logout', 'langs/change'], // Para forzar una carga completa de la page. Se usa en appAjax
	
	renderMenu: function(aMenu, className, $parent, depth){
		if (aMenu.length == 0) {
			return;
		}
		
		if (depth == null) {
			depth = 0;
		}
		
		var $ul = $('<ul></ul>').appendTo($parent);
		if (className != null) {
			$ul.addClass(className);
		}
	
		
		for (var i=0; i<aMenu.length; i++) {
			var item    = aMenu[i];
			var label   = (item['menuTranslate'] == true ? crLang.line(item['label']) : item['label']);
			var $li     = $('<li></li>').appendTo($ul);
			var $link   = $('<a></a>')
				.appendTo($li)
				.attr('title', label);

			if (item['menuClassName'] != null)  {
				$li.addClass(item['menuClassName']);
			}
			
			if (item['url'] != null) {
				$link.attr('href', base_url + item['url']);
				
				var aTmp = item['url'].split('/'); // Para quitar los parametros adicionales de un controller
				var controller = aTmp[0];
				if (aTmp.length > 1) {
					controller += '/' + aTmp[1];
				}
				if ($.inArray(controller, this.aSkipAppLink) != -1) {
					$link.attr('data-skip-app-link', true);
				}
			}

			if (item['icon'] != null) {
				$link.append(' <i class="' + item['icon'] + '" ></i> ')
			}
			$('<span>').text(label).appendTo($link);

			if (item['menuDividerBefore'] == true) {
				$link.before(' <li role="presentation" class="divider"></li> ');
			}
			if (item['menuDividerAfter'] == true) {
				$link.after(' <li role="presentation" class="divider"></li> ');
			}

			
			if (item['childs'].length > 0) {
				$link.addClass('dropdown-toggle').attr('data-toggle', 'dropdown');
				if (depth >= 1) {
					$li.addClass(' dropdown-submenu dropdown-submenu-left ');
				}
				
				this.renderMenu(item['childs'], 'dropdown-menu' , $li, (depth + 1));
			}
		}
	},
	
	initMenu: function() {
		var $menuProfile = $('ul.menuProfile');

		$menuProfile.find('.lang-' + crSettings.langId ).before('<i class="fa fa-check fa-fw"></i>');
		$menuProfile.find('.fa-flag-o').parent()
			.append('<span class="badge pull-right">' + crSettings.langId + '</span>');
	
		$('ul.dropdown-menu [data-toggle=dropdown]').on('click', 
			function(event) {
				event.preventDefault(); 
				event.stopPropagation(); 
				
				var expand = $(this).parent().hasClass('open');
				
				$('ul.dropdown-menu [data-toggle=dropdown]').parent().removeClass('open');
				
				if (expand == false) {
					$(this).parent().addClass('open');
				}
			}
		);
	},		
};
