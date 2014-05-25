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
			var label 	= crLang.line(aMenu[i]['label']);
			var $li 	= $('<li></li>').appendTo($ul);
			var $link 	= $('<a></a>')
				.appendTo($li)
				.attr('title', label)
				.text(label);
			
			if (aMenu[i]['url'] != null) {
				$link.attr('href', base_url + aMenu[i]['url']);
				
				var aTmp = aMenu[i]['url'].split('/'); // Para quitar los parametros adicionales de un controller
				var controller = aTmp[0];
				if (aTmp.length > 1) {
					controller += '/' + aTmp[1];
				}
				if ($.inArray(controller, this.aSkipAppLink) != -1) {
					$link.attr('data-skip-app-link', true);
				}
			}
			if (aMenu[i]['icon'] != null) {
				$link.prepend(' <i class="' + aMenu[i]['icon'] + '" ></i> ')
			}
			
			if (aMenu[i]['childs'].length > 0) {
				$link.addClass('dropdown-toggle').attr('data-toggle', 'dropdown');
				if (depth >= 1) {
					$li.addClass(' dropdown-submenu dropdown-submenu-left ');
				}
				
				this.renderMenu(aMenu[i]['childs'], 'dropdown-menu' , $li, (depth + 1));
			}
		}
	},
	
	initMenu: function() {
		var $menuProfile = $('ul.menuProfile');
		
		var $iconGear 	= $menuProfile.find('.fa-gear');
		var $settings 	= $iconGear.parent();
		var label		= $settings.text();
		$settings
			.addClass('menuItemSettings')
			.html('')
			.append($iconGear)
			.append('<span>' + label + '</span>');
			
			
		if ($menuProfile.find('.menuItemAbout').parents('ul:first').find('li').length > 1) {
			$menuProfile.find('.menuItemAbout').parents('li').before($('<li role="presentation" class="divider"></li>'));
		}

		$menuProfile.find('.lang-' + $.crSettings.langId ).before('<i class="fa fa-check fa-fw"></i>');
		$menuProfile.find('.fa-flag-o').parent()
			.append('<span class="badge pull-right">' + $.crSettings.langId + '</span>');
	
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
