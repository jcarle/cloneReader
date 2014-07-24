cloneReader = {
	init: function(aFilters) {
		this.$page 		= $('.cr-page-home').attr('id', 'cloneReader'); // TODO: revisar el name
		this.$toolbar 	= $('<nav class="navbar navbar-default" role="navigation" />').appendTo(this.$page);
		this.$ulFilters	= $('<ul class="ulFilters"/>').appendTo(this.$page);
		this.$ulEntries	= $('<ul class="ulEntries"  />').appendTo(this.$page);
		
		this.fixDatetime = moment(datetime, 'YYYY-MM-DDTHH:mm:ss').diff(moment(), 'ms'); // guardo en memoria la diferencia de tiempo entre la db y el cliente, para mostrar bien las fechas
		moment.lang($.crSettings.langId);
		this.isMobile = $.isMobile();

		this.minUnreadEntries 	= 2;
		this.isLastPage			= false;
		this.currentEntries		= []; // para guardar las entries visibles y no volver a pedir al servidor si solo se cambia el tipo de vista
		this.aEntries	 		= {};
		this.filters			= null;
		this.tags				= null;
		this.aUserEntries 		= {};
		this.aUserTags			= {};
		this.aFilters 			= $.extend({
			'page':			1,
			'onlyUnread':	true,
			'sortDesc': 	true,
			'id': 			$.crSettings.tagHome, 
			'type': 		'tag',
			'viewType': 	'detail',
			'isMaximized': 	false
		}, aFilters);	
		this.isMaximized		= (this.isMobile == true ? false : this.aFilters.isMaximized); // Uso una variable local para maximinar, y SOLO la guardo en la db si isMobili = false
		this.aSystemTags		= [$.crSettings.tagAll, $.crSettings.tagStar, $.crSettings.tagHome, $.crSettings.tagBrowse];
		this.isLoaded			= false;
		
		this.buildCache();
		this.renderToolbar();
		this.loadFilters(false);
		this.initEvents();
//		this.updateMainMenu();
//		this.resizeWindow();
		this.$page.trigger('onVisible');
		this.isLoaded = true;
	},

	updateMainMenu: function() {
		if (this.$dropdownMenu == null) {
			this.$dropdownMenu 	= $('ul.menuProfile').find('.fa-gear').parent().parent().find('ul.dropdown-menu:first');
			
			$('<li role="presentation" class="divider"></li>').appendTo(this.$dropdownMenu);
			this.$helpKeyboardShortcut = $('<li class="dropdown-submenu"><a href="javascript:cloneReader.helpKeyboardShortcut();" title="' + crLang.line('Keyboard shortcut') + '">' + crLang.line('Keyboard shortcut') + '</a></li>').appendTo(this.$dropdownMenu);			
		}
		
		if (this.$page.is(':visible') == true) {
			this.$helpKeyboardShortcut.prev().show();
			this.$helpKeyboardShortcut.show();
			if (this.isMobile == true) {
				this.$helpKeyboardShortcut.prev().hide();
			}
		}
		else {
			this.$helpKeyboardShortcut.prev().hide();
			this.$helpKeyboardShortcut.hide();		
		}
	},
	
	initEvents: function() {
		this.$page.data('notRefresh', true);
		
		this.$page.bind('onVisible', $.proxy(
			function() {
				$('.menu').hide();
				$('body').css({ 'background': '#E5E5E5', 'overflow': 'hidden' });
				
				this.resizeWindow();
				this.updateMainMenu();

				if (this.indexFilters != null) {
					var filter = this.getFilter(this.aFilters);
					$('title').text(filter.name + ' | ' + $.crSettings.siteName);
				}
			}

		, this));
		
		this.$page.bind('onHide', $.proxy(
			function() {
				this.$mainToolbar.hide();
				this.$toolbar.hide();
				$('#header .logo').attr('href', base_url);
				$('#header').css( {'box-shadow': 'none' });
				$('.menu').show();
				$('body').css({ 'background': 'white', 'overflow': '' });
				
				this.resizeWindow();
				this.updateMainMenu();
			}
		, this));
					
		
		setInterval(function() { cloneReader.saveData(true); }, ($.crSettings.feedTimeSave * 1000)); 
//		setInterval(function() { cloneReader.loadFilters(true); }, ($.crSettings.feedTimeReload * 60000));
		setInterval(function() { cloneReader.updateEntriesDateTime(); }, ($.crSettings.feedTimeReload * 60000));

		this.$ulEntries
			.on({ 'tap' : 
				function(event){
					var $entry = $(event.target);
					cloneReader.selectEntry($entry, false, false);
				} 
			})
			.scrollStopped(function(){
				cloneReader.checkScroll();
				cloneReader.getMoreEntries();
			});
		
		this.maximiseUlEntries(this.isMaximized, false);
		
		$(window).resize(function() {
			cloneReader.resizeWindow();
			if (cloneReader.isMobile != true) {
				cloneReader.scrollToEntry(cloneReader.$ulEntries.find('li.selected'), false);
			}
			cloneReader.maximiseUlEntries(cloneReader.isMaximized, true);
		});
		
		$(document).keyup($.proxy(
			function(event) {
				if ($('body').hasClass('modal-open') == true) {
					return;
				}
				if (this.$page.is(':visible') != true) {
					return;
				}
				
				event.stopPropagation();
//cn(event['keyCode']);
				switch (event['keyCode']) {
					case 74: // J: next
					case 75: // N: prev
						this.goToEntry(event['keyCode'] == 74);
						break;	
					case 82: // R: reload
						this.loadEntries(true, true, {});
						break;
					case 85: // U: expand!			
						this.maximiseUlEntries(!this.isMaximized, false);
						break;
					case 83: // S: star
						var $entry = this.$ulEntries.find('.entry.selected');
						if ($entry.length != 0) {
							this.starEntry($entry, ($entry.find('.star.selected').length == 0));
						}
						break;
					case 86: // V: open link
						var $entry = this.$ulEntries.find('.entry.selected');
						if ($entry.length != 0) {
							this.$ulEntries.find('.entry.selected .header a')[0].click();
						}
						break;
					case 77: // M read entry
						var $entry = this.$ulEntries.find('.entry.selected');
						if ($entry.length != 0) {
							this.readEntry($entry, $entry.find('.read').hasClass('selected'));
						}
						break;
					case 65: // A Add feed
						this.$mainToolbar.find('.add').click();
						break;
					case 69: // E
						var $entry = this.$ulEntries.find('.entry.selected');
						if ($entry.length != 0) {
							this.showFormShareByEmail($entry.data('entryId'));
						}
						break;
					case 49: // 1 Detail view
						this.$mainToolbar.find('.viewDetail').click();
						break;
					case 50: // 2 List view
						this.$mainToolbar.find('.viewList').click();
						break;
				}
			}
		, this));

		$(document).click(
			function(event) {
				if ($(event.target).parents('.modal').length != 0) {
					return;
				}
				if ($('.crAlert:visible').length != 0) {
					return;
				}
				
				var $popupForm = cloneReader.$page.find('.popupForm:visible');
				if ($popupForm.length != 0) {
					if ($.contains($popupForm[0], event.target)) {
						return;
					}
				}
				cloneReader.hidePopupWindow();
			}
		);
		
		$('#header .logo').click(function(event) { 
			if (cloneReader.isMobile != true) {  return;  }
			cloneReader.maximiseUlEntries(!cloneReader.isMaximized, false);
		} );
	},
	
	checkScroll: function() {
		if (this.aFilters.viewType == 'list') {
			return;
		}
		if (this.$ulEntries.find('li.selected').length == 0 && this.$ulEntries.scrollTop() == 0) { 
			return;
		}
		if (this.$ulEntries.is(':animated') == true) {
			return;
		}		

		var top 		= this.$ulEntries.offset().top;
		var height	 	= this.$ulEntries.outerHeight();
		var aLi			= this.$ulEntries.find('.entry');
		
		for (var i=0; i<aLi.length; i++) {
			var $entry 	= $(aLi[i]);
			var offset 	= $entry.find('p:first').offset();
			if (top <= offset.top) { 
				this.selectEntry($entry, false, false);
				return;
			}
			if (top >= offset.top && (offset.top + $entry.height())  >= height) {
				this.selectEntry($entry, false, false);
				return;
			}
		}
	},	
	
	buildCache: function() {
		$.ajax({
			'url': 			base_url + 'entries/buildCache',
			'async':		true, //false
			'skipwWaiting':	true,
			'success':  	
				function(response) {
					if ($.hasAjaxDefaultAction(response) == true) { return; }		
					if (response.result.hasNewEntries == true) {
						cloneReader.loadFilters(true);
					}
				}
		});
	},
	
	renderToolbar: function() {
		this.$toolbar.html(' \
			<ul class="nav navbar-nav"> \
				<li> \
					<button title="' + crLang.line('Expand') + '" class="expand"> \
						<i class="fa fa-exchange"  /> \
						<span class="btnLabel">' +  crLang.line('Expand') + '</span> \
					</button> \
				</li> \
			</ul> \
			<ul class="nav mainToolbar"> \
				<li> \
					<button title="' + crLang.line('Add feed') + '" class="add" > \
						<i class="fa fa-plus" /> \
						<span class="btnLabel">' + crLang.line('Add feed') + '</span> \
					</button> \
				</li> \
				<li> \
					<button title="' + crLang.line('Mark all as read') + '" class="btnMarkAllAsRead" > \
						<i class="fa fa-archive" /> \
						<span>' + crLang.line('Mark all as read') + '</span> \
					</button> \
				</li> \
				<li> \
					<div class="btn-group feedSettings" > \
						<button class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="' + crLang.line('Feed settings') + '"> \
							<span> ' + crLang.line('Feed settings') +' </span> \
							<span class="caret" /> \
						</button> \
						<ul class="dropdown-menu popupFeedSettings" /> \
					</div> \
				</li> \
				<li> \
					<div class="btn-group filterSort" > \
						<button class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="' + crLang.line('Sort') + '" > \
							<span/> \
							<span class="caret" /> \
						</button> \
						<ul class="dropdown-menu" > \
							<li class="filterNewestSort"> <a> ' + crLang.line('Sort by newest') + ' </a> </li> \
							<li class="filterOldestSort"> <a> ' + crLang.line('Sort by oldest') + ' </a> </li> \
						</ul> \
					</div> \
				</li> \
				<li> \
					<div class="btn-group filterUnread" > \
						<button class="btn btn-default dropdown-toggle" data-toggle="dropdown" > \
							<span/> \
							<span class="caret" /> \
						</button> \
						<ul class="dropdown-menu" > \
							<li class="filterAllItems"> <a> '+ crLang.line('All items') + ' </a> </li> \
							<li class="filterOnlyUnread" > <a> ' + $.sprintf(crLang.line('%s unread items'), '  <span class="count" /> ')   + ' </a> </li> \
						</ul> \
					</div> \
				</li> \
				<li> \
					<div class="btn-group" data-toggle="buttons-radio" > \
						<button class="viewList" title="' + crLang.line('List view') + '" > \
							<i class="fa fa-align-justify" /> \
						</button> \
						<button class="viewDetail" title="' + crLang.line('Detail view') + '" > \
							<i class="fa fa-th-list" /> \
						</button> \
					</div> \
				</li> \
				<li> \
					<button title="' + crLang.line('Reload') + '" class="reload" > <i class="fa fa-refresh" /> \
						<span class="btnLabel">' + crLang.line('Reload') + '</span> \
					 </button> \
				</li> \
				<li class="directionNav" > \
					<div class="btn-group"  > \
						<button title="' + crLang.line('Prev') + '" class="prev" > <i class="fa fa-caret-up" /> </button> \
						<button title="' + crLang.line('Next') + '" class="next" > <i class="fa fa-caret-down" /> </button> \
					</div> \
				</li> \
			</ul> \
		');
		
		this.$mainToolbar 		= this.$toolbar.find('.mainToolbar');
		this.$popupFeedSettings = this.$toolbar.find('.popupFeedSettings');

		
		this.$toolbar.find('ul button').addClass('btn').addClass('btn-default').addClass('navbar-btn');
		
		this.$toolbar.find('.expand').click(function() { cloneReader.maximiseUlEntries(!cloneReader.isMaximized, false) } );
		this.$toolbar.find('.btnMarkAllAsRead').click( function() { cloneReader.markAllAsRead(); } );
 		this.$mainToolbar.find('.next').click(function() { cloneReader.goToEntry(true) });
		this.$mainToolbar.find('.prev').click(function() { cloneReader.goToEntry(false) });
		this.$mainToolbar.find('.reload').click(function() { cloneReader.loadEntries(true, true, {}) });
		this.$mainToolbar.find('.viewDetail').click( function(event) { event.stopPropagation(); cloneReader.loadEntries(true, false, {'viewType': 	'detail'}); } );
		this.$mainToolbar.find('.viewList').click(function(event) { event.stopPropagation(); cloneReader.loadEntries(true, false, {'viewType': 	'list'}); });
		this.$mainToolbar.find('.filterAllItems').click(function() { cloneReader.loadEntries(true, false, { 'onlyUnread': false }); });
		this.$mainToolbar.find('.filterOnlyUnread').click(function() { cloneReader.loadEntries(true, false, { 'onlyUnread': true }); });
		this.$mainToolbar.find('.filterNewestSort').click(function(event) { cloneReader.loadEntries(true, false, {'sortDesc': true}); });
		this.$mainToolbar.find('.filterOldestSort').click(function(event) { cloneReader.loadEntries(true, false, {'sortDesc': false}); });
		this.$mainToolbar.find('.add').click(  function(event) {  
				event.stopPropagation(); 
				cloneReader.showPopupForm(crLang.line('Add new feed'), crLang.line('Add feed url'), function() { cloneReader.addFeed(); }, $(event.target)); 
			}
		);
		this.$mainToolbar.find('.feedSettings').click(function() { cloneReader.showPopupFeedSettings(); });
		
		this.$mainToolbar.find('.filterUnread, .filterSort, .feedSettings').hide();
		this.$mainToolbar.find('.dropdown-toggle').click(
			function(event) {
				cloneReader.hidePopupWindow();
			}
		);
	},
	
	loadEntries: function(clear, forceRefresh, aFilters) {
		this.hidePopupWindow();
		
		var lastFilters = $.toJSON(this.aFilters);
		this.aFilters 	= $.extend(this.aFilters, aFilters);
		
		if (this.$ulEntries.children().length == 0) { // Para la primera carga
			forceRefresh = true;
		}
		
		if (clear == true && this.isMobile == true && this.$ulEntries.children().length != 0) { // Si no es la primera carga y es mobile, maximizo al cambiar el filtro
			this.maximiseUlEntries(true, false);
		}

		if (forceRefresh != true && $.toJSON(this.aFilters) === lastFilters) {
			return;
		}
		if (clear == true) {
			this.aFilters['page'] = 1;
			this.$ulEntries.children().remove();
			this.$ulEntries.scrollTop(0);
		}
		
		if (this.isMobile != true) { // Actualizo el valor de isMaximized sino isMobile 
			this.aFilters.isMaximized = this.isMaximized;
		}
		
		this.renderNotResult(true);
		this.renderEntriesHead();
		this.selectFilters();
		this.updateToolBar();
		
		lastFilters = $.parseJSON(lastFilters);
		if (this.aFilters.onlyUnread != lastFilters.onlyUnread) {
			this.renderFilters(this.filters, this.$ulFilters, true);
		}

		// Si SOLO cambio el tipo de vista reendereo sin pasar por el servidor
		if ($.inArray($.toJSON(aFilters), ['{"viewType":"detail"}', '{"viewType":"list"}']) != -1) {
			this.updateUserFilters();
			if (this.isLoadEntries == true) {
				return;
			}
			this.renderEntries(this.currentEntries);
			return;
		}
		
		if (this.aFilters['page'] == 1) {
			this.currentEntries = [];
		}
		if (!(this.aFilters.id == lastFilters.id && this.aFilters.type == lastFilters.type)) {
			this.renderUlFilterBranch(this.getFilter(lastFilters));
		}
		if (clear == true) {
			this.saveData(false);
		}		

		if (this.ajax) {
			this.ajax.abort();
			this.ajax = null;
		}
		
		
		if (this.isBrowseTags()) {
			this.browseTags();
			return;
		}
		
		this.isLoadEntries = true;
		
		this.ajax = $.ajax({		
			'url': 		base_url + 'entries/select',
			'data': 	{ 
				'post': 				$.toJSON(this.aFilters), 
				'pushTmpUserEntries': 	clear 
			},
			'type':			'post',
			'skipwWaiting':	true,
			'success':  	
				function(response) {
					if ($.hasAjaxDefaultAction(response) == true) { return; }

					cloneReader.isLastPage 		= (response.result.length < $.crSettings.entriesPageSize);
					cloneReader.currentEntries 	= $.merge(cloneReader.currentEntries, response.result);
					cloneReader.renderEntries(response.result);
				},
			'complete':
				function () {
					cloneReader.isLoadEntries = false;
				}
		});	
	},
	
	renderEntries: function(result) {
		this.$ulEntries.removeClass('list');
		if (this.aFilters.viewType == 'list') {
			this.$ulEntries.addClass('list');
		}
		
		if (result.length == 0) {
			this.updateMenuCount();
			this.renderNotResult(false);
			return;
		}
		
		this.$noResult.hide();
		
		for (var i=0; i<result.length; i++) {
			var entry = result[i];
			if (this.aEntries[entry.entryId] == null) {
				this.aEntries[entry.entryId] = entry;
			}
			
			var $entry = $('<li/>')
					.addClass('entry')
					.data({ 'entryId': entry.entryId } )
					.appendTo(this.$ulEntries);

			this.renderEntry($entry);
		}
	
		this.updateMenuCount();
		this.renderNotResult(false);
	},
	
	renderEntry: function($entry) {
		if ($entry.hasClass('noResult') == true) {
			return false;
		}
		
		$entry.children().remove();		

		var entryId = $entry.data('entryId');
		var entry 	= this.aEntries[entryId];
		
		if (entry.entryTitle == '') {
			var datetime = moment(entry['entryDate'], 'YYYY-MM-DDTHH:mm:ss');
			entry.entryTitle = datetime.format('LL');
		}		

		if (this.aFilters.viewType == 'detail') {
			this.renderDetailEntry($entry, entry);
		}
		else {
			this.renderListEntry($entry, entry);
		}

		$entry.find('.star').click(function(event) {
			event.stopPropagation();
			$star = $(event.target);
			if ($star.hasClass('.star') == false) {
				$star = $star.parents('.star');
			}
			cloneReader.starEntry($star.parents('.entry'), !$star.hasClass('selected'));
		});
		
		this.starEntry($entry, entry.starred);
		this.readEntry($entry, (entry.entryRead == true));
		
		setTimeout( function() { cloneReader.updateEntryDateTime($entry); } , 0);
		
		if (this.aFilters['type'] ==  'tag' && this.aFilters['id'] == $.crSettings.tagHome) {
			$entry.find('.star, .footer, .entryOrigin').remove();
		} 
		
		return true;
	},
	
	renderDetailEntry: function($entry, entry) {
		var $header = $('<div/>').addClass('header').appendTo($entry);
		
		$('<a />')
			.addClass('entryTitle')
			.attr('href', entry.entryUrl)
			.css('background-image', 'url(' + base_url + (entry.feedIcon == null ? 'assets/images/default_feed.png' : 'assets/favicons/' + entry.feedIcon) + ')')
			.html(entry.entryTitle || '&nbsp;')
			.appendTo($header);

		$('<label title="' + crLang.line('Star') + '"><i /></label>').addClass('star fa').appendTo($header);
		$('<span />').addClass('entryDate').appendTo($header);
					
		
		if (entry.entryAuthor == '') {
			var entryOrigin = $.sprintf(crLang.line('From %s'), '<a >' + entry.feedName + '</a>');
		}
		else {
			var entryOrigin = $.sprintf(crLang.line('From %s by %s'), '<a >' + entry.feedName + '</a>', entry.entryAuthor);
		}
		var $div = $('<div class="entryOrigin" />').html( entryOrigin).appendTo($header);
	
		$div.find('a').click(
			function() {
				cloneReader.loadEntries(true, false, { 'type': 'feed', 'id': cloneReader.aEntries[$(this).parents('.entry').data('entryId')]['feedId'] });
			}
		);

		var entryContent 	= $('<div>' + entry.entryContent + '</div>').clone().find('script, noscript, style, iframe, link, meta, br').remove().end().html();
		var $p 				= $('<p/>').html(entryContent).appendTo($entry);

		$entry.find('img').error(
			function() {
				$(this)
					.addClass('imgError')
					.attr('title', 'No network connection or image is not available')
					.attr('src', 'assets/images/error.svg');
			});

		var $footer = $('<div class="panel-footer footer" />').appendTo($entry);

		$('<label class="star checkbox" title="' + crLang.line('Star') + '" > <i/> </label>').appendTo($footer);
		$('<label class="read checkbox" > <i/> <span> ' + crLang.line('Keep unread') + ' </span> </label>').appendTo($footer);
		

		$('<a class="btnSocial fa fa-lg fa-envelope"  />')
			.click(function(event) {
				event.stopPropagation();
				var $entry = $($(event.target).parents('.entry'));
				var entryId = $entry.data('entryId');
				cloneReader.showFormShareByEmail(entryId);
			})
			.appendTo($footer);	
		
		var aSocial = [
			{'icon': 'fa fa-facebook-square', 		'app': 'fb:share/',	'url': 'http://www.facebook.com/sharer/sharer.php?u='},
			{'icon': 'fa fa-twitter-square', 		'app': '',		'url': 'http://www.twitter.com/home?status='},
			{'icon': 'fa fa-google-plus-square', 	'app': 'tw:',	'url': 'http://plus.google.com/share?url='},
		];
		for (var i=0; i<aSocial.length; i++) {
			var url = aSocial[i].url + entry.entryUrl;
			//var linkToApp = aSocial[i].app + aSocial[i].url + entry.entryUrl;
			$('<a data-rel="external" class="btnSocial fa-lg ' + aSocial[i].icon + '" href="' + url + '"  />').appendTo($footer);
		}

/*
TODO: pensar como mejorar esta parte
		
		if (this.isMobile == true) {		
//			if ( $p.get(0).scrollHeight > $p.height()) {
				$('<button class="btn btn-default  btnViewAll"> <i class="fa fa-lg fa-reorder" /> </button>')
					.click(function(event) {
						var $entry = $(event.target).parents('.entry');
						$entry.find('> p').css( {'max-height': 'none' });
						$entry.find('.btnViewAll').remove(); 
					})
					.appendTo($entry);	
//			}
		} */

		$entry.find('.read, .read i').click(function(event) {
			event.stopPropagation();
			$checkbox = $(event.target);
			if ($checkbox.hasClass('read') == false) {
				$checkbox = $checkbox.parents('.read:first');
			}
			cloneReader.readEntry($checkbox.parents('.entry'), $checkbox.hasClass('selected'));
		});				
		
		$entry.find('p').children().removeAttr('class');
		$entry.find('a').attr('target', '_blank');
		
		$entry.click(function(event) {
			var $entry = $(event.target).parents('.entry');
			if ($entry.hasClass('selected') == true) { return; }
			cloneReader.selectEntry($entry, false, false);
		});
	},
	
	renderListEntry: function($entry, entry) {
		var $div 			= $('<div/>').addClass('title').appendTo($entry);

		$('<label><i /></label>').addClass('fa star').appendTo($div);
		$('<span />').addClass('feedName').html($.stripTags(entry.feedName, '')).appendTo($div);
		$('<span />').addClass('entryDate').appendTo($div);
		
		$('<span />').addClass('entryContent').html($.stripTags(entry.entryContent, ''))
			.appendTo($div)
			.prepend($('<h2 />').html($.stripTags(entry.entryTitle, '')));		

		$entry.find('.title').click(function(event) {
			var $entry = $(event.target).parents('.entry');
			
			if ($entry.hasClass('expanded') == true) {
				$entry
					.removeClass('expanded')
					.removeClass('selected')
					.find('.detail').remove();
				return;
			}
			cloneReader.selectEntry($entry, true, false);
		});	
	},
	
	renderEntriesHead: function() {
		var filter = this.getFilter(this.aFilters);
		if (filter == null) { return; }
		
		if (this.$entriesHead == null) {
			this.$entriesHead = $('<li/>').addClass('entriesHead');
		}
		
		this.$entriesHead.text(filter.name);
		this.$ulEntries.prepend(this.$entriesHead);				
		
		$('title').text(filter.name + ' | ' + $.crSettings.siteName);
	},	
	
	selectFilters: function() {
		this.$ulFilters.find('li.selected').removeClass('selected');
		
		var filter = this.getFilter(this.aFilters);
		if (filter == null) { return; }
				
		filter.$filter.addClass('selected');
	},
	
	renderNotResult: function(loading) {
		if (this.$noResult == null) {
			this.$noResult = $('<li/>').addClass('noResult');
		}
		
		this.$noResult.appendTo(this.$ulEntries).show();
		
		if (loading == true) {
			this.$noResult.html('<div class="alert alert-warning"> <i class="fa fa-spinner fa-spin fa-lg"></i> ' + crLang.line('loading ...') + '</div>').addClass('loading');
		}
		else {
			this.$noResult.html('<div class="well well-lg"> ' + crLang.line('no more entries') + ' </div>').removeClass('loading');
		}
		
		this.resizeNoResult();
	},

	starEntry: function($entry, value) {
		$entry.find('.star').removeClass('selected');
		$entry.find('.star i').removeAttr('class');
		if (value == true) {
			$entry.find('.star').addClass('selected');
			$entry.find('.star i').addClass('fa-star');
		}
		else {
			$entry.find('.star i').addClass('fa-star-o');
		}
		$entry.find('.star i').addClass('fa fa-lg');
		
		var entryId = $entry.data('entryId');
		var starred = (this.aEntries[entryId].starred == 1);
		this.aEntries[entryId].starred = value;
		if (starred != value) {
			this.addToSave(this.aEntries[entryId]);
		}
	},

	readEntry: function($entry, value) {
		$entry.find('.read').removeClass('selected');
		$entry.find('.read i').removeAttr('class');
		
		if (value == false) {
			$entry.find('.read').addClass('selected');
			$entry.find('.read i').addClass('fa-check-square');
		}
		else {
			$entry.find('.read i').addClass('fa-square-o');
		}
		$entry.find('.read i').addClass('fa fa-lg');
		
		var entryId		= $entry.data('entryId');
		var entryRead	= (this.aEntries[entryId].entryRead == 1);
		this.aEntries[entryId].entryRead = value;
		if (entryRead != value) {
			this.addToSave(this.aEntries[entryId]);
			
			var feedId 	= this.aEntries[entryId].feedId;
			var filter 	= this.indexFilters['feed'][feedId];
			var sum 	= (value == true ? -1 : +1);
			
			filter.count = parseInt(filter.count) + sum;
			if (filter.count < 0) {
				filter.count = 0;
			}

			this.renderUlFilterBranch(filter);
			this.updateMenuCount();
		}

		$entry.removeClass('readed');
		if (value == true) {
			$entry.addClass('readed');
		}
	},
	
	renderUlFilterBranch: function(filter) { // actualizo solo los contadores de la parte del arbol de filters seleccionado
		this.renderCounts(filter); 
		var parents = this.getAllParentsByFilter(filter);
		for(var i=0; i<parents.length; i++) {
			this.renderCounts(parents[i]);
		}	
	},
	
	renderCounts: function(filter, count) {
		filter		= this.getFilter(filter);
		var $filter = filter.$filter;
		var count 	= this.getCountFilter(filter);
		if ($filter.length == 0) { return; }
		
		if (count < 0) {
			count = 0;
		}
					
		var $count = $filter.find('.count:first');
		$count.text('(' + (count > $.crSettings.feedMaxCount ? $.crSettings.feedMaxCount + '+' : count) + ')');
		$count.hide();
		if (count > 0) {
			$count.show();
		}
		
		
		$filter.removeClass('empty')
		if (count == 0) {
			$filter.addClass('empty');
		}
		$filter.hide().toggle(this.filterIsVisible(filter, true));		
	},
	
	updateToolBar: function() {
		if (this.isBrowseTags() == true) {
			this.$mainToolbar.hide();
			return;
		}
		this.$mainToolbar.show();
		
		this.$mainToolbar.find('.filterSort span:first').text(this.$mainToolbar.find(this.aFilters.sortDesc == true ? '.filterNewestSort' : '.filterOldestSort').text());
		this.$mainToolbar.find('.filterUnread span:first').html(this.$mainToolbar.find(this.aFilters.onlyUnread == true ? '.filterOnlyUnread a' : '.filterAllItems a').html());

		this.$mainToolbar.find('.viewDetail, .viewList').removeClass('active');
		if (this.aFilters.viewType == 'detail') {
			this.$mainToolbar.find('.viewDetail').addClass('active');
		}
		else {
			this.$mainToolbar.find('.viewList').addClass('active');
		}

		this.$mainToolbar.find('.feedSettings').hide();
		if (this.aFilters.type == 'feed') {
			this.$mainToolbar.find('.feedSettings').show();
		}
		
		this.$mainToolbar.find('.filterUnread').hide();
		this.$mainToolbar.find('.btnMarkAllAsRead').hide();
		
		if (!(this.aFilters.type == 'tag' && $.inArray(this.aFilters.id, [$.crSettings.tagStar, $.crSettings.tagHome]) != -1)) {
			this.$mainToolbar.find('.btnMarkAllAsRead').show();
			this.$mainToolbar.find('.filterUnread').show();
		}
		
		this.$mainToolbar.find('.filterSort').show();
	},

	updateMenuCount: function() {
		var count = this.getCountFilter(this.getFilter(this.aFilters));
		if (count > $.crSettings.feedMaxCount) {
			count = $.crSettings.feedMaxCount + '+';
		}
		this.$mainToolbar.find('.filterUnread .count').text(count);
		this.$page.find('.filterOnlyUnread .count').text(count);
	},
	
	selectEntry: function($entry, scrollTo, animate) {
		if ($entry.length == 0) { return; }
		if ($entry.hasClass('noResult') == true) { return; }
		if (this.$ulEntries.find('> li.entry.selected:first').is($entry)) { return; }
	
		this.$ulEntries.find(' > li.entry.selected').removeClass('selected');
		$entry.addClass('selected');

		if (this.aFilters.viewType == 'list') {
			this.$ulEntries.find('.entry').removeClass('expanded');
			this.$ulEntries.find('.entry .detail').remove();
			
			this.renderEntry($entry);
			
			$entry.addClass('expanded');
			var entryId = $entry.data('entryId');
			var entry 	= this.aEntries[entryId];		
			$div = $('<div/>').data('entryId', entryId).addClass('detail');
			this.renderDetailEntry($div, entry);
			$div.find('.header .entryDate, .header .star').remove();
			$entry.append($div);
			
			$entry.find('.footer .star').click(function(event) {
				event.stopPropagation();
				$star = $(event.target);
				if ($star.hasClass('star') == false) {
					$star = $star.parents('.star');
				}
				cloneReader.starEntry($star.parents('.entry'), !$star.hasClass('selected'));
			});
			
			this.starEntry($entry, entry.starred);
			
			animate = false;
		}

		if (scrollTo == true) {
			this.scrollToEntry($entry, animate);
		}	

		this.readEntry($entry, true);
//		this.getMoreEntries();
	},
	
	scrollToEntry: function($entry, animate) {
		if ($entry.length == 0) { return; }
		if (this.isMobile == true && this.$ulEntries.is(':visible') == false) { return; }

		var top = $entry.get(0).offsetTop - 10;		

		if (animate == true) { 
			 this.$ulEntries.stop().animate( {  scrollTop: top  } );
		}
		else {
			this.$ulEntries.stop().scrollTop(top);
		}
	},
	
	goToEntry: function(next) {
		$.hideMobileNavbar();
		
		var $entry = this.$ulEntries.find('.entry.selected');
		if ($entry.length == 0 && next != true) {
			return;
		}		
		
		if ($entry.length == 0) {
			$entry = this.$ulEntries.find('.entry').first();
		}
		else {
			$entry = (next == true ? $entry.nextAll('.entry:first') : $entry.prevAll('.entry:first'));
		}
		this.selectEntry($entry, true, true);
		this.$ulEntries.focus();
	},
	
	getMoreEntries: function() {
		if (this.isBrowseTags() == true) {
			return;
		}
		
		// busco más entries si esta visible el li 'noResult', o si el li.selected es casi el ultimo
		if (this.isLastPage == true) { 
			return;
		}
		
		if (this.isMobile == true && this.$ulEntries.is(':visible') == false) { return; }
		
		if (
			this.$noResult.visible(true) == true 
			||
			((this.$ulEntries.find('.entry').length - this.minUnreadEntries) <= this.$ulEntries.find('.entry.selected').index())
		) {
			
			if ($.active == 0) {
				this.loadEntries(false, false, { 'page': this.aFilters['page'] + 1 });
			}
		}
	},

	loadFilters: function(reload) {
		this.saveData(false);
		
		$.ajax({ 
			'url': 		base_url + 'entries/selectFilters',
			'success': 	
				$.proxy(
					function(reload, response) {
						if ($.hasAjaxDefaultAction(response) == true) { return; }

console.time("t1");	
						if (reload == true) {
							var scrollTop = this.$ulFilters.scrollTop();
						}
						this.filters 	= response.result.filters;
						this.tags 		= response.result.tags;
						this.runIndexFilters(this.filters, null, true);
						this.renderFilters(this.filters, this.$ulFilters, true);
						this.resizeWindow();

						if (reload == true) {
							this.$ulFilters.scrollTop(scrollTop);
							this.$ulFilters.find('.selected').hide().fadeIn('slow');
							this.updateMenuCount();
						}
						else {
							this.loadEntries(true, false, {});
						}
						
console.timeEnd("t1");
					}
				, this, reload)
		});	
	},
	
	runIndexFilters: function(filters, parent, clear) {
		if (clear == true) {
			this.indexFilters 	= { 'tag': {}, 'feed': {}};
			this.$ulFilters.children().remove();
		}
		
		for (var i=0; i<filters.length; i++) {
			var filter = filters[i];
			
			if (this.indexFilters[filter.type][filter.id] == null) {
				this.indexFilters[filter.type][filter.id] = filter;
				$.extend(filter, { '$filter': $(), 'parents': [] });
			}
			
			filter = this.getFilter(filter);
			
			if (parent != null) {
				filter.parents.push(parent);
			}
			
			if (filter.childs != null) {
				this.runIndexFilters(filter.childs, filter, false);
			}
		}		
	},

	renderFilters: function(filters, $parent, selectFilters){
		var index = 0;
		for (var i=0; i<filters.length; i++) {
			var filter = filters[i];
			
			filter = this.getFilter(filter);

			filter.count = this.getCountFilter(filter);
			this.renderCounts(filter);

			if (this.filterIsVisible(filter, $parent.hasClass('filterVisible')) == true) {
				this.renderFilter(filter, $parent, index);
				index++;
			}
				
			if (filter.$filter != null && filter.childs != null && filter.expanded == true ) {
				this.expandFilter(filter, filter.expanded);
				this.renderFilters(filter.childs, filter.$filter.find('ul:first'), false);
			}
		}
		if (selectFilters == true) {
			this.selectFilters();
		}
	},

	renderFilter: function(filter, $parent, index) {
		var filter = this.getFilter(filter);
			
		if (filter.$filter.length != 0 && filter.$filter.parents().is($parent)) {
			return filter.$filter;
		}

		var $filter = $('<li/>')
					.data('filter', filter)
					.html('<div><span class="icon fa" /><a>' + filter.name + '</a><span class="count" /></div>')
					.appendTo($parent);

		if (index != null && index != $filter.index()) { // para ordenar los items que se crearon en tiempo de ejecución
			$($parent.find('> li').get(index)).before($filter);
		}
					
		filter.$filter = filter.$filter.add($filter);

		$filter.find('a')
			.attr('title', filter.name)
			.click(function (event) {
				var filter = $($(event.target).parents('li:first')).data('filter');
				cloneReader.loadEntries(true, false, { 'type': filter.type, 'id': filter.id });
			});
			
		this.renderCounts(filter);

		if (filter.icon != null) {
			$filter.find('.icon').css('background-image', 'url(' + filter.icon + ')');
		}
		if (filter.classIcon != null) {
			$filter.find('.icon').addClass(filter.classIcon);
		}				

		
		if (filter.childs != null) {
			$filter.append('<ul />').find('.icon').addClass('arrow');
			$filter.find('.icon')
				.addClass('fa-caret-square-o-right')
				.click(
					function(event) {
						var $filter	= $($(event.target).parents('li:first'));
						var filter	= $filter.data('filter');
						cloneReader.expandFilter(filter, !filter.expanded);
					});

			if (filter.expanded == false) { 
				this.animateFilter($filter.find('ul'), false);
			}
		}

		return $filter;
	},
	
	filterIsVisible: function(filter, parentIsVisible) {
		filter = this.getFilter(filter);
		if (filter.type == 'tag' && $.inArray(filter.id, this.aSystemTags) != -1) {
			return true;
		}		
		if (parentIsVisible == true && parseInt(this.getCountFilter(filter)) > 0) {
			return true;
		}
		if (parentIsVisible == true && this.aFilters.onlyUnread == false) {
			return true;
		}
		if (this.isSelectedFilter(filter) == true) {
			return true;
		}						
		
		return false;
	},
	
	isSelectedFilter: function(filter) {
		if (filter.id == this.aFilters.id && filter.type == this.aFilters.type) {
			return true;
		}
		if (filter.childs != null) {
			for (var i=0; i<filter.childs.length; i++) {
				if (this.isSelectedFilter(filter.childs[i]) == true) {
					return true;
				}
			}			
		}
		return false;
	},
	
	getFilter: function(filter) {
		var tmp = this.indexFilters[filter.type][filter.id];
		if (tmp != null) {
			return tmp;
		}	
		return this.indexFilters['tag'][$.crSettings.tagAll];	
	},
	
	getAllParentsByFilter: function(filter){
		var parents = [];
		for (var i=0; i<filter.parents.length; i++) {
			parents.push(filter.parents[i]);
			if (filter.parents.length != 0) {
				parents = $.merge(parents, this.getAllParentsByFilter(filter.parents[i]));
			}
		}
		return parents;
	},
	
	getFeedsByFilter: function(filter) {
		if (filter.childs == null) {
			return [filter];
		}
				
		var feeds = {};
		for (var i=0; i<filter.childs.length; i++) {
			if (filter.childs[i].childs != null) {
				$.extend(feeds, this.getFeedsByFilter(filter.childs[i]));
			}
			else {
				if (filter.childs[i].type == 'feed') {
					feeds[filter.childs[i].id] = this.getFilter(filter.childs[i]);
				}
			}
		}		
		return feeds;
	},

	getCountFilter: function(filter) {
		if (filter == null) {
			return 0;
		}
		if (filter.childs == null) {
			return filter.count;
		}
		
		var feeds = this.getFeedsByFilter(filter);		
		var count = 0;
		for (var feedId in feeds) {
			count += parseInt(feeds[feedId].count);
		}
		return count;
	},				

	expandFilter: function(filter, value){
		if (filter.expanded != value) {
			filter.expanded = value;
			this.aUserTags[filter.id] = {
				'tagId': 		filter.id,
				'expanded': 	value
			};
		}

		var $filter	= filter.$filter;
		
		if (value != true) {
			this.animateFilter($filter, value);
		}

		var $ul 	= $filter.find('ul:first');
		var index 	= 0;
		for (var i=0; i<filter.childs.length; i++) {
			if (this.filterIsVisible(filter.childs[i], true) == true) {
				this.renderFilter(filter.childs[i], $ul, index);
				index++;
			}
		}

		this.animateFilter($filter, value);
		this.getFilter(this.aFilters).$filter.addClass('selected');
	},
	
	animateFilter: function($filter, value) {
		var $ul 	= $filter.find('ul:first');
		var $arrow 	= $filter.find('.arrow:first');
		
		$arrow.removeClass('fa-caret-square-o-down').removeClass('fa-caret-square-o-right');
		
		if (value != true) {
			$arrow.addClass('fa-caret-square-o-right');
			$ul.removeClass('filterVisible');
			$ul.slideUp('fast');
			return;
		}

		$arrow.addClass('fa-caret-square-o-down');
		$ul.addClass('filterVisible');
		$ul.slideDown('fast');
	},
	
	maximiseUlEntries: function(value, isResize) {
		if (isResize == false) {
			$.hideMobileNavbar();
		}

		this.isMaximized = value;
		
		var speed = 100;
		
		if (this.isMobile == true) {
			if (value == true) {
				this.$ulFilters.addClass('outside');
			}
			else {
				this.$ulFilters.removeClass('outside');
			}
			return;
		}


		if (value == false) {
			this.$ulEntries.removeClass('maximixed');
		}
		else {
			this.$ulEntries.addClass('maximixed');
		}
		
		this.$ulEntries.one('transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd', 
			function() {		
					cloneReader.scrollToEntry(cloneReader.$ulEntries.find('li.selected'), false);
			});
			
		if (this.isLoaded == true) {
			this.updateUserFilters();
		}
	},
	
	subscribeFeed: function(feedId) {
		$.ajax({
			'url': 		base_url + 'entries/subscribeFeed',
			'data': 	{  'feedId': feedId },
			'type':	 	'post',
			'success': 	
				$.proxy(
					function(feedId, response) {
						if ($.hasAjaxDefaultAction(response) == true) { return; }
						
						cloneReader.loadEntries(true, true, { 'type': 'feed', 'id': feedId }); 
						cloneReader.loadFilters(true);
					}
				, this, feedId)
		});
	},

	addFeed: function() {
		var feedUrl = this.$popupForm.find('input').val();
		if (feedUrl == '') {
			return this.$popupForm.find('input').crAlert(crLang.line('Enter a url'));
		}
		if ($.validateUrl(feedUrl) == false) {
			return this.$popupForm.find('input').crAlert(crLang.line('Enter a valid url'));
		}

		this.hidePopupWindow();

		$.ajax({
			'url': 		base_url + 'entries/addFeed',
			'data': 	{  'feedUrl': feedUrl },
			'type':	 	'post',
			'success': 	
				function(response) {
					if ($.hasAjaxDefaultAction(response) == true) { return; }
					
					cloneReader.loadEntries(true, true, { 'type': 'feed', 'id': response['result']['feedId'] }); 
					cloneReader.loadFilters(true);
				}
		});
	},
	
	addToSave: function(entry) {
		if (this.aFilters['type'] ==  'tag' && this.aFilters['id'] == $.crSettings.tagHome) { // si estoy en el tag home, no guardo nada 
			return;
		}
		
		this.aUserEntries[entry.entryId] = {
			'entryId': 		entry.entryId,	
			'entryRead': 	entry.entryRead,
			'starred': 		entry.starred
		};
	},
	
	saveData: function(async){
		if (Object.keys(this.aUserEntries).length == 0 && Object.keys(this.aUserTags).length == 0) {
			return;
		}
		
		$.ajax({
			'url': 		base_url + 'entries/saveData',
			'data': 	{ 
					'entries': 	$.toJSON(this.aUserEntries),
					'tags': 	$.toJSON(this.aUserTags) 
			},
			'type':	 		'post',
			'skipwWaiting':	(async == true),
			'async':		async,
			'success': 		
				function(response) {
					if ($.hasAjaxDefaultAction(response) == true) { return; }
				}
		});
		
		this.aUserEntries 	= {};
		this.aUserTags		= {};
	},

	addTag: function() {
		var tagName = this.$popupForm.find('input').val();
		if (tagName.trim() == '') {
			return this.$popupForm.find('input').crAlert('enter a tag name');
		}

		this.hidePopupWindow();

		$.ajax({
			'url': 		base_url + 'entries/addTag',
			'data': 	{ 
				'tagName': 	tagName ,
				'feedId':	this.aFilters.id
			},
			'type':	 	'post',
			'success': 	
				function(response) {
					if ($.hasAjaxDefaultAction(response) == true) { return; }

					cloneReader.loadEntries(true, true, { 'type': 'tag', 'id': response['result']['tagId'] }); 
					cloneReader.loadFilters(true);
				}
		});
	},

	saveUserFeedTag: function(feedId, tagId, append) {
		this.hidePopupWindow();
		$.hideMobileNavbar();

		$.ajax({
			'url': 		base_url + 'entries/saveUserFeedTag',
			'data': 	{ 
				'feedId': 	feedId,
				'tagId': 	tagId,
				'append':	append
			},
			'type':	 	'post',
			'success': 	
				function(response) {
					if ($.hasAjaxDefaultAction(response) == true) { return; }
					cloneReader.saveData(false);
					cloneReader.loadFilters(true);
				}
		});
	},
	
	markAllAsRead: function(feedId) {
		this.hidePopupWindow();
		
		var filter = this.getFilter(this.aFilters);


		$(document).crAlert( {
			'msg': 			$.sprintf( crLang.line('Mark "%s" as read?'), filter.name),
			'isConfirm': 	true,
			'callback': 	$.proxy(
				function() {
					$.ajax({
						'type':	 	'post',
						'url': 		base_url + 'entries/markAllAsRead',
						'data': 	{
							'type': 	this.aFilters.type,
							'id': 		this.aFilters.id
						},
						'success': 	
							function(response) {
								if ($.hasAjaxDefaultAction(response) == true) { return; }
								cloneReader.aEntries = {}
								cloneReader.loadEntries(true, true, {});
								cloneReader.loadFilters(true);
							}
					});
				}
			, this)
		});	
	},
	
	unsubscribeFeed: function(feedId) {
		this.hidePopupWindow();
		
		var filter = this.getFilter(this.aFilters);
		
		$(document).crAlert( {
			'msg': 			$.sprintf( crLang.line('Unsubscribe "%s"?'), filter.name),
			'isConfirm': 	true,
			'callback': 	$.proxy(		
				function () {
					$.ajax({
						'type':	 	'post',
						'url': 		base_url + 'entries/unsubscribeFeed',
						'data': 	{ 'feedId':	feedId 	},
						'success': 	
							function(response) {
								if ($.hasAjaxDefaultAction(response) == true) { return; }
								cloneReader.loadEntries(true, true, { 'type': 'tag', 'id': $.crSettings.tagAll });
								cloneReader.loadFilters(true);
							}
					});
				}
			, this)
		});
	},
	
	updateUserFilters: function() {
// TODO: hacer que no guarde tanto asi no mata al servidor
		this.ajaxUpdateUserFilters = $.ajax({
			'url': 			base_url + 'entries/updateUserFilters',
			'data': 		{ 'post': $.toJSON(this.aFilters) },
			'type':			'post',
			'skipwWaiting': true,
			'success': 		
				function(response) {
					if ($.hasAjaxDefaultAction(response) == true) { return; }
				}
		});
	},
	
	updateEntriesDateTime: function() {
		this.$ulEntries.find('.entryDate').each(
			function() {
				cloneReader.updateEntryDateTime($(this).parents('li'));
			}
		);
	},
	
	updateEntryDateTime: function($entry) {
		var entryId = $entry.data('entryId');
		var entry 	= this.aEntries[entryId];
		if (entry == null) { return; }
		if (entry.entryDate == null) { return; }
		
		if (this.aFilters.viewType == 'detail') {
			$entry.find('.entryDate').text(this.humanizeDatetime(entry.entryDate, 'LLL') + ' (' + this.humanizeDatetime(entry.entryDate) + ')');
		}
		else {
			$entry.find('.entryDate').text(this.humanizeDatetime(entry.entryDate));
		}
	},	

	showPopupFeedSettings: function() {
		this.$popupFeedSettings.find('li').remove();

		var feedId = this.aFilters.id;

		var aItems = [
			{ 'html': crLang.line('Unsubscribe'), 		'callback': function() { cloneReader.unsubscribeFeed(cloneReader.aFilters.id);  } },
			{ 'html': crLang.line('New tag'), 			'class': 'newTag', 'callback': 
				function(event) {
					event.stopPropagation(); 
					cloneReader.showPopupForm(crLang.line('Add new tag'), crLang.line('enter tag name'), function() { cloneReader.addTag(); }, cloneReader.$mainToolbar.find('.feedSettings'));
				}
			},
			{ 'class': 'divider' }
		];

		for (var i=0; i<this.tags.length; i++) {
			var tag = this.tags[i];
			if ($.inArray(tag.tagId, this.aSystemTags) == -1) {
				var filter	= this.indexFilters['tag'][tag.tagId];
				var check 	= '';
				var hasTag 	= this.feedHasTag(this.getFilter(this.aFilters), filter);
				if (hasTag == true) {
					check = '&#10004';
				}
				aItems.push( { 'html': (hasTag == true ? '<i class="fa fa-check fa-fw" />' : '') + tag.tagName, 'data': { 'feedId': feedId, 'tagId': tag.tagId} , 'callback': function() {  
					var $filter = $(this); 
					cloneReader.saveUserFeedTag($filter.data('feedId'), $filter.data('tagId'), $filter.find('i.fa-check').length == 0 ); 
				} } );
			}
		}
		
 
		for (var i=0; i<aItems.length; i++) {
			var item 	= aItems[i];
			if (item.class == 'divider') {
				var $item 	= $('<li class="divider" />').appendTo(this.$popupFeedSettings);
			}
			else {
				var $item 	= $('<li><a>' + item.html + '</a></li>').appendTo(this.$popupFeedSettings);
				if (item.data != null) {
					$item.data(item.data);
				}
				$item.click(item.callback);
			} 
		}
			
		this.$popupFeedSettings.css({ 'max-height': this.$ulEntries.height(), 'overflow': 'auto' })
	},
	
	feedHasTag: function(feed, tag) {
		if (feed == null || tag == null) {
			return false;
		}
		for (var i=0; i<feed.parents.length; i++) {
			if (tag.type == feed.parents[i].type && tag.id == feed.parents[i].id) {
				return true;
			}
		} 
		return false;
	},

	showPopupForm: function(title, placeholder, callback, $element){
		if (this.$popupForm == null) {
			this.$popupForm = $('\
				<form class="btn-default dropdown-menu form-inline popupForm "> \
					<div class="input-group"> \
						<input type="text" class="form-control"  /> \
						<span class="input-group-btn" > \
							<button class="btn btn-primary"> <i class="fa fa-check" /> </button> \
						</span> \
					</div> \
				</form>\
			');
			
			this.$popupForm.find('input').keyup(function(event) {
				event.stopPropagation();
			});
		}
		
		this.hidePopupWindow();
		$.hideMobileNavbar();
		
		this.$popupForm
			.unbind()
			.submit(function(event) {
				event.preventDefault();
				callback();
				return false;
			});
		this.$popupForm.find('input').attr('placeholder', placeholder).val('');

		//var top		= $element.offset().top + $element.height() - this.$toolbar.offset().top;
		var top		= 92; //FIXME: harckodeta! //this.$toolbar.height() + this.$toolbar.offset().top; 
		var left 	= $element.offset().left - this.$page.offset().left;
		
		this.$popupForm
			.css({ 'top': top,  'left': left, 'position': 'fixed' })
			.appendTo(this.$page)
			.stop()
			.fadeIn();
			
		if (this.isMobile == false) {
			this.$popupForm.find('input').focus();
		}
	},
	
	resizeWindow: function() {
		if (this.$page.is(':visible') != true) {
			return;
		}
		
		this.isMobile = $.isMobile();
		
		this.$mainToolbar.removeClass('navbar-nav pull-right').show();

		if (this.isMobile == true) {
			this.$toolbar.hide();
			this.$mainToolbar.appendTo($('#header .navbar-collapse')).addClass('navbar-nav');
			$('#header .logo').removeAttr('href');
			$('#header').css( { 'box-shadow': '0 0px 7px #666' });
		}
		else {
			this.$mainToolbar.appendTo( this.$toolbar ).addClass('navbar-nav pull-right');
			this.$toolbar.show();
			$('#header .logo').attr('href', base_url);
			this.hidePopupWindow();
			$('#header').css( {'box-shadow': 'none' });
		}

		this.$page.find('.pageTitle').remove();

		this.resizeNoResult();
	},
	
	resizeNoResult: function() {
		if (this.$noResult == null) { return; }
		
		this.$noResult
			.css('min-height', 
				this.$ulEntries.height() 
//				- $('#header').outerHeight()
//				- this.$ulEntries.find('.entriesHead').outerHeight()
//				- this.$ulEntries.offset().top 
//				- this.$noResult.offset().top
//				- this.$noResult.find('div').outerHeight()
				- (this.isMobile == true ? 75 : 130) // FIXME: desharkodear! 
				)		
//			.css('border', '1px red solid' )
		;				
	},
	
	hidePopupWindow: function() {
		this.$page.find('.popupForm').hide();
		this.$mainToolbar.find('.open').removeClass('open');
	},
	
	isBrowseTags: function() {
		if (this.aFilters.type == 'tag' && this.aFilters.id == $.crSettings.tagBrowse) {
			return true;
		}
		return false;
	},		
	
	browseTags: function() {
		this.ajax = $.ajax({		
			'url': 		base_url + 'entries/browseTags',
			'type':		'post',
			'success': 	
				function(response) {
					if ($.hasAjaxDefaultAction(response) == true) { return; }
					cloneReader.renderBrowseTags(response.result);
				}
		});
	},
	
	renderBrowseTags: function(result) {
		this.$ulEntries.removeClass('list');
		
		this.updateToolBar();
		
		if (result.length == 0) {
			this.renderNotResult(false);
			return;
		}
		
		this.$noResult.hide();
		
		var $li 	= $('<li class="browseTags"></li>').appendTo(this.$ulEntries);
		var $ul		= $('<div class="list-group"></div>').appendTo($li);
		
		for (var i=0; i<result.length; i++) {
			var tag = result[i];
			
			var $tag = $('<a class="list-group-item" href="javascript:void(0);"><i class="icon fa fa-tag"></i> ' + tag.tagName + ' </a>').appendTo($ul);
			$tag
				.data('tag', tag)
				.click($.proxy(
					function(event) {
						var $tag = $(event.target);
						
						$tag.parent().find('div.list-group-item').remove();
						
						if ($tag.hasClass('active')) {
							$tag.removeClass('active');
							return;
						}
						
						$tag.parent().find('.list-group-item').removeClass('active');
						$tag.addClass('active');

						this.ajax = $.ajax({		
							'url': 		base_url + 'entries/browseFeedsByTagId',
							'data': 	{ 'tagId': $tag.data('tag').tagId },
							'success': 	
								$.proxy( 
									function($tag, response) {
										if ($.hasAjaxDefaultAction(response) == true) { return; }
										
										for (var i=0; i<response.result.length; i++) {
											var feed 	= response.result[i];
											var feedId 	= feed.feedId;
											
// TODO: refactorizar, para que no metan tags html.  ej "Sinergia sin control"
											var $feed = $(
											'<div class="list-group-item">\
												<h4 class="list-group-item-heading"> \ ' + feed.feedName + '</h4> \
												<p class="list-group-item-text">' + (feed.feedDescription == null ? '' : feed.feedDescription) + '</p>  \
												<a class="feedLink" href="' + feed.feedLink + '">' + (feed.feedLink == null ? '' : feed.feedLink) + '</a>  \
												<button title="' + crLang.line('Subscribe') + '" class="btn btn-success" type="button"> \
													<i class="fa fa-plus"  /> \
													<span class="btnLabel">' +  crLang.line('Subscribe') + '</span> \
												</button> \
											</div>');
											
											$feed.find('button').click($.proxy(
												function(feedId) {
													this.subscribeFeed(feedId);
												}
											, this, feedId));
											
											
											$feed.find('h4').css('background-image', 'url(' + base_url + (feed.feedIcon == null ? 'assets/images/default_feed.png' : 'assets/favicons/' + feed.feedIcon) + ')')
											$tag.after($feed);
										}
										
										//this.$ulEntries.stop().scrollTop( $tag.get(0).offsetTop + this.$entriesHead.height()  );
										this.$ulEntries.stop().scrollTop( $tag.position().top + 45 ); // FIXME: harckodeta!! 
									}
								, this, $tag)
						})
					}
				,this));
		}
	},
	
	humanizeDatetime: function(datetime, format) {
		datetime = moment(datetime, 'YYYY-MM-DDTHH:mm:ss').add('ms', -this.fixDatetime);
		if (datetime >= moment()) {
			datetime = moment().add('ms', -1);
		} 
		
		if (format == null) {
			return datetime.fromNow();
		}
		return datetime.format(format);
	},
	
	showFormShareByEmail: function(entryId) {
		if (this.ajaxShareByEmail) {
			this.ajaxShareByEmail.abort();
			this.ajaxShareByEmail = null;
		}
				
		this.ajaxShareByEmail = $.ajax({
			'url': 		base_url + 'entries/shareByEmail/' + entryId,
			'async':	true,
			'success': 	
				$.proxy( 
					function (response) {
						if ($.hasAjaxDefaultAction(response) == true) { return; }
						$.showPopupForm(response['result']['form']);
					}
				, this)
		});
	},
	
	helpKeyboardShortcut: function() {
		$.hideMobileNavbar();
		
		if (this.$keyboardShortcut != null) {
			$.showModal(this.$keyboardShortcut, false);
			return;
		}
		
		$.ajax({
			'type': 	'get',
			'url': 		base_url + 'help/keyboardShortcut/',
			'success': 	
				$.proxy( 
					function (response) {
						if ($.hasAjaxDefaultAction(response) == true) { return; }
						
						this.$keyboardShortcut = $.showPopupForm(response['result']['form']);
					}
				, this),
		});
	}
};


$.fn.scrollStopped = function(callback) {
	$(this).scroll(function(){
		var self = this, $this = $(self);
		if ($this.data('scrollTimeout')) {
			clearTimeout($this.data('scrollTimeout'));
		}
		$this.data('scrollTimeout', setTimeout(callback, 250, self));
	});
};

