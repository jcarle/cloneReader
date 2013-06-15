cloneReader = {
	init: function(aFilters) {
		this.$container = $('#cloneReader');
		this.$ulMenu 	= $('<ul class="ulMenu" />').appendTo(this.$container);
		this.$ulFilters	= $('<ul class="ulFilters"/>').appendTo(this.$container); 
		this.$ulEntries	= $('<ul class="ulEntries"/>').appendTo(this.$container);
		
		this.fixDatetime = moment(datetime, 'YYYY-MM-DDTHH:mm:ss').diff(moment(), 'ms'); // guardo en memoria la diferencia de tiempo entre la db y el cliente, para mostrar bien las fechas
		moment.lang('es'); // TODO: harckodeta!
		
		this.$ulEntries.data('margin-left', this.$ulEntries.css('margin-left'));		

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
			'id': 			TAG_ALL, 
			'type': 		'tag',
			'viewType': 	'detail',
			'isMaximized': 	false
		}, aFilters);		

		this.buildCache();
		this.renderMenu();
		this.loadFilters(false);
		this.initEvents();
		this.resizeWindow();
	},

	initEvents: function() {
		setInterval(function() { cloneReader.saveData(true); }, (FEED_TIME_SAVE * 1000)); 
		setInterval(function() { cloneReader.loadFilters(true); }, (FEED_TIME_RELOAD * 60000));
		setInterval(function() { cloneReader.updateEntriesDateTime(); }, (FEED_TIME_RELOAD * 60000));
		
		this.$ulFilters.niceScroll({'cursorcolor': '#CCC', 'cursorwidth': '8', 'scrollspeed': 90, 'mousescrollstep': 65 }); // TODO: revisar los parametros de niceScroll
		this.$ulEntries.niceScroll({'cursorcolor': '#CCC', 'cursorwidth': '8', 'scrollspeed': 90, 'mousescrollstep': 65 });
		
		this.maximiseUlEntries(this.aFilters.isMaximized, false);						
		
		$(window).resize(function() {
			cloneReader.resizeWindow();
			cloneReader.scrollToEntry(cloneReader.$ulEntries.find('li.selected'), false);
		});

		this.$ulEntries.scroll($.proxy(
			function(event) {
				this.scrollEntries();
				
				if (this.aFilters.viewType == 'list') {
					this.getMoreEntries();
					return;
				}

				if (this.$ulEntries.is(':animated') == true) {
					return;
				}

				var top 	= this.$ulEntries.offset().top;
				var height 	= this.$ulEntries.outerHeight();
				var aLi		= this.$ulEntries.find('.entry .header').parent(); // recorro solos los visibles ( tienen header ;)
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
			}
		, this));		
		
		$(document).keyup($.proxy(
			function(event) {
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
						this.maximiseUlEntries(!this.aFilters.isMaximized, true);
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
				}
			}
		, this));

		$(document).click(
			function(event) {
				if ($('.alert').length != 0) {
					return;
				}
				
				var $popUpWindow = cloneReader.$container.find('.popUpWindow:visible');
				if ($popUpWindow.length != 0) {
					if ($.contains($popUpWindow[0], event.target)) {
						return;
					}
				}
				cloneReader.hidePopupWindow();
			}
		);
	},
	
	buildCache: function() {
		$.ajax({
			'url': 		base_url + 'entries/buildCache',
			'async':	true //false
		})		
	},
	
	renderMenu: function() {
		this.createMenu([
			{ 'html': '&#8644;', 	'title': 'expand', 'class': 'expand',	'callback': function() { cloneReader.maximiseUlEntries(!cloneReader.aFilters.isMaximized, true) }},
			{ 'html': '&#9660;', 	'title': 'next',	'class': 'next', 	'callback': function() { cloneReader.goToEntry(true) }},
			{ 'html': '&#9650;', 	'title': 'prev', 	'class': 'prev', 	'callback': function() { cloneReader.goToEntry(false) }},
			{ 'html': '&#10226;', 	'title': 'reload', 	'class': 'reload', 'callback': function() { cloneReader.loadEntries(true, true, {}) }},
			{ 'html': '&nbsp;', 	'class': 'viewDetail', 		'title': 'detail view', 	'callback': function() { cloneReader.loadEntries(true, false, {'viewType': 	'detail'}); }},
			{ 'html': '&nbsp;', 	'class': 'viewList', 		'title': 'list view', 		'callback': function() { cloneReader.loadEntries(true, false, {'viewType': 	'list'}); }},
			{ 'html': '<span />', 	'class': 'filterUnread', 
				'childs':  [
					{ 'html': 'all items', 							'class': 'filterAllItems', 'callback': function() { cloneReader.loadEntries(true, false, { 'onlyUnread': false }); }},
					{ 'html': '<span class="count" /> new items', 	'class': 'filterOnlyUnread', 'callback': function() { cloneReader.loadEntries(true, false, { 'onlyUnread': true }); }},
				]
			},
			{ 'html': '<span />', 	'class': 'filterSort', 
				'childs':  [
					{ 'html': 'sort by newest', 'class': 'filterNewestSort', 'callback': function(event) { cloneReader.loadEntries(true, false, {'sortDesc': true}); }},
					{ 'html': 'sort by oldest', 'class': 'filterOldestSort', 'callback': function(event) { cloneReader.loadEntries(true, false, {'sortDesc': false}); }},
				]
			},
			{ 'html': 'Feed settings', 	'class': 'feedSettings', 'callback':  function() { cloneReader.showPopupFeedSettings(); },
				'childsClassName': 'popupFeedSettings',
				'childs':  []
			},
			{ 'html': '+',  'title': 'add feed', 'class': 'add', 'callback': 
				function(event) { 
					event.stopPropagation(); 
					cloneReader.showPopupForm('add feed url', function() { cloneReader.addFeed(); }, cloneReader.$ulMenu.find('li.add')); 
				}
			}
		], this.$ulMenu);
		
		this.$ulMenu.find('.add .arrow, .filterUnread, .filterSort, .feedSettings').hide();
	},

	showPopupMenu: function($li) {
		if ($li.get(0).tagName != 'LI') {
			$li = $li.parents('li');
		}

		var $popUpWindow 	= $li.data('popUpWindow');
		if ($popUpWindow.is(':visible')) {
			this.hidePopupWindow();
			return;
		} 

		this.hidePopupWindow();

		var $popUpWindow 	= $li.data('popUpWindow');
		var top				= $li.offset().top + $li.height() - this.$container.offset().top + 1; // FIXME: revisar el '1'
		var left 			= $li.offset().left - this.$container.offset().left;
		var width 			= $li.innerWidth();;

		this.$ulMenu.find('li').removeClass('expanded');
		$li.addClass('expanded');

		this.showPopupWindow($popUpWindow, top, left, width);
	},
	
	createMenu: function(items, $parent) {
		for (var i=0; i<items.length; i++) {
			var item 	= items[i];
			var $li 	= $('<li/>').html(item.html).attr('title', item.title).addClass(item.class).appendTo($parent);
			if (item.data != null) {
				$li.data(item.data);
			}
			if (item.childs != null) {
				$('<span class="arrow">&#9660;</span>').appendTo($li);
				var $popUpWindow = $('<ul/>')
					.addClass('popUpWindow')
					.addClass(item.childsClassName)
					.hide().appendTo(this.$container);

				this.createMenu(item.childs, $popUpWindow);
				$li
					.data('popUpWindow', $popUpWindow)
					.click(function(event) { 
							event.stopPropagation(); 
							cloneReader.showPopupMenu($(event.target));
					});
			}

			$li.click(item.callback);
		}
	},	
	
	loadEntries: function(clear, forceRefresh, aFilters) {
		this.hidePopupWindow();
		
		var lastFilters = $.toJSON(this.aFilters);
		this.aFilters 	= $.extend(this.aFilters, aFilters);
		
		if (cloneReader.$ulEntries.children().length == 0) { // Para la primera carga
			forceRefresh = true;
		}
		
		if (forceRefresh != true && $.toJSON(this.aFilters) === lastFilters) {
			return;
		}
		if (clear == true) {
			this.aFilters['page'] = 1;
			this.$ulEntries.children().remove();
			this.$ulEntries.scrollTop(0);
		}		
		
		this.renderNotResult(true);
		this.renderEntriesHead();
		this.selectFilters();
		this.updateUlMenu();
		
		lastFilters = $.parseJSON(lastFilters);
		if (this.aFilters.onlyUnread != lastFilters.onlyUnread) {
			this.renderFilters(this.filters, this.$ulFilters, true);
		}

		// Si SOLO cambio el tipo de vista reendereo sin pasar por el servidor
		if ($.inArray($.toJSON(aFilters), ['{"viewType":"detail"}', '{"viewType":"list"}']) != -1) {
			this.renderEntries(this.currentEntries);
			this.updateUserFilters();
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
		
		this.ajax = $.ajax({		
			'url': 		base_url + 'entries/select',
			'data': 	{ 
				'post': 				$.toJSON(this.aFilters), 
				'pushTmpUserEntries': 	clear 
			},
			'type':		'post'
		})
		.done(function(response) {
			if (response['code'] != true) {
				return $(document).alert(response['result']);
			}
			cloneReader.isLastPage 		= (response.result.length == 0);
			cloneReader.currentEntries 	= $.merge(cloneReader.currentEntries, response.result);
			cloneReader.renderEntries(response.result);
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
					.addClass('clean')
					.addClass('entry')
					.data({ 'entryId': entry.entryId } )
					.appendTo(this.$ulEntries);

			this.renderEntry($entry);
		}
	
		this.updateMenuCount();
		this.renderNotResult(false);
	},
	
	scrollEntries: function() {
		var isVisible 	= false;
		var $entries 	= this.$ulEntries.find('.entry');
		for (var i=0; i<$entries.length; i++) {
			var show = this.renderEntry($($entries.get(i)));
			if (show == true) {
				isVisible = true;
			}
			if (show == false && isVisible == true) {
				break;
			}			
		}
	},		
	
	renderEntry: function($entry) {
		if ($entry.hasClass('noResult') == true) {
			return false;
		}
		if ($entry.visible( true ) == false) {
			$entry.addClass('clean').children().remove();
			return false;
		}
		if ($entry.hasClass('clean') == false) {
			return false;
		}		

		var entryId = $entry.data('entryId');
		var entry 	= this.aEntries[entryId];

		$entry.removeClass('clean');
		
		if (this.aFilters.viewType == 'detail') {
			this.renderDetailEntry($entry, entry);
		}
		else {
			this.renderListEntry($entry, entry);
		}

		$entry.find('.star').click(function(event) {
			event.stopPropagation();
			$star = $(event.target);
			cloneReader.starEntry($star.parents('.entry'), !$star.hasClass('selected'));
		});
		
		this.starEntry($entry, entry.starred);
		this.readEntry($entry, (entry.entryRead == true));
//		$entry.stop().css('opacity', 0).animate( {'opacity': 1}, 'fast');
		
		setTimeout( function() { cloneReader.updateEntryDateTime($entry); } , 0);
		
		return true;
	},
	
	renderDetailEntry: function($entry, entry) {
		var $header = $('<div/>').addClass('header').appendTo($entry);

		$('<a />')
			.addClass('entryTitle')
			.attr('href', entry.entryUrl)
			.css('background-image', 'url(' + (entry.feedIcon == null ? 'assets/images/default_feed.png' : 'images/' + entry.feedIcon) + ')')
			.html(entry.entryTitle || '&nbsp;')
			.appendTo($header);
			
		var $div = $('<div />').html('from <a >' + entry.feedName + '</a>').appendTo($header);
		if (entry.entryAuthor != '') {
			$div.html($div.html() + ' by ' + entry.entryAuthor ).appendTo($header);				
		}	
		$div.find('a').click(
			function() {
				cloneReader.loadEntries(true, false, { 'type': 'feed', 'id': cloneReader.aEntries[$(this).parents('.entry').data('entryId')]['feedId'] });
			}
		);

		$('<span />').addClass('entryDate').appendTo($header);
		$('<span />').addClass('star').appendTo($header);
	
		var $entryContent = $('<div/>'); // TODO: revisar esta parte, chequear que elimine bien los <scripts>
		$entryContent.text(entry.entryContent); //$entryContent[0].innerHTML = entry.entryContent;
		$entryContent.find('script').remove();
		$entryContent.find('iframe').remove();
		$('<p/>').html($entryContent.text()).appendTo($entry);

		var $footer = $('<div/>').addClass('footer').appendTo($entry);

		$('<span />').addClass('star').appendTo($footer);
		$('<span />').addClass('read').html('<span class="checkbox"/>keep unread').appendTo($footer);


		$entry.find('.read .checkbox').click(function(event) {
			event.stopPropagation();
			$checkbox = $(event.target);
			cloneReader.readEntry($checkbox.parents('.entry'), $checkbox.parent().hasClass('selected'));
		});				
						
		$entry.css('min-height', $entry.height());
		$entry.find('img').load(
			function(event) {
				var $entry = $(event.target).parents('.entry');
				$entry.css('min-height', $entry.height());
			}
		);
		
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

		$('<span />').addClass('star').appendTo($div);
		$('<span />').addClass('feedName').text(entry.feedName).appendTo($div);
		$('<span />').addClass('entryContent').text($.stripTags(entry.entryContent, ''))
			.appendTo($div)
			.prepend($('<h2 />').text(entry.entryTitle));
		$('<span />').addClass('entryDate').appendTo($div);

		$entry.find('.feedName, .entryContent').click(function(event) {
			var $entry = $(event.target).parents('.entry');
			
			if ($entry.hasClass('expanded') == true) {
				$entry
					.removeClass('expanded')
					.removeClass('selected')
					.find('.detail').remove();
				cloneReader.scrollEntries();
				return;
			}
			cloneReader.selectEntry($entry, false, false);
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
	},	
	
	selectFilters: function() {
		this.$ulFilters.find('li.selected').removeClass('selected');
		
		var filter = this.getFilter(this.aFilters);
		if (filter == null) { return; }
				
		filter.$filter.addClass('selected');
		
		this.updateNiceScroll();
	},
	
	renderNotResult: function(loading) {
		if (this.$noResult == null) {
			this.$noResult = $('<li/>').addClass('noResult');
		}
		this.$noResult.css('min-height', this.$ulEntries.height() - 90).appendTo(this.$ulEntries).show();
		
		if (loading == true) {
			this.$noResult.text('loading ...').addClass('loading');
		}
		else {
			this.$noResult.text('no more entries').removeClass('loading');
		}
	},

	starEntry: function($entry, value) {
		$entry.find('.star').removeClass('selected');
		if (value == true) {
			$entry.find('.star').addClass('selected');
		}
		
		var entryId = $entry.data('entryId');
		var starred = (this.aEntries[entryId].starred == 1);
		this.aEntries[entryId].starred = value;
		if (starred != value) {
			this.addToSave(this.aEntries[entryId]);
		}
	},

	readEntry: function($entry, value) {
		$entry.find('.read .checkbox').html('')
		$entry.find('.read').removeClass('selected');
		if (value == false) {
			$entry.find('.read .checkbox').html('&#10004;')
			$entry.find('.read').addClass('selected');
		}
		
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
		$count.text('(' + (count > FEED_MAX_COUNT ? FEED_MAX_COUNT + '+' : count) + ')');
		$count.hide();
		if (count > 0) {
			$count.show();
		}
		
		
		$filter.removeClass('empty')
		if (count == 0) {
			$filter.addClass('empty');
		}
		$filter.hide().toggle(this.isVisible(filter, true));		
	},
	
	updateUlMenu: function() {
		this.$ulMenu.find('.filterSort span:first').text(this.$container.find(this.aFilters.sortDesc == true ? '.popUpWindow .filterNewestSort' : '.popUpWindow .filterOldestSort').text());
		this.$ulMenu.find('.filterUnread span:first').html(this.$container.find(this.aFilters.onlyUnread == true ? '.popUpWindow .filterOnlyUnread' :  '.popUpWindow .filterAllItems').html());
		
		this.$ulMenu.find('li.viewDetail, li.viewList').removeClass('checked');
		if (this.aFilters.viewType == 'detail') {
			this.$ulMenu.find('li.viewDetail').addClass('checked');
		}
		else {
			this.$ulMenu.find('li.viewList').addClass('checked');
		}		

		this.$ulMenu.find('li.feedSettings').hide();
		if (this.aFilters.type == 'feed') {
			this.$ulMenu.find('li.feedSettings').show();
		}
		
		this.$ulMenu.find('.filterUnread').hide();
		if (!(this.aFilters.type == 'tag' && this.aFilters.id == TAG_STAR)) {
			this.$ulMenu.find('.filterUnread').show();
		}
		
		this.$ulMenu.find('.filterSort').show();
	},

	updateMenuCount: function() {
		var count = this.getCountFilter(this.getFilter(this.aFilters));
		if (count > FEED_MAX_COUNT) {
			count = FEED_MAX_COUNT + '+';
		}
		this.$ulMenu.find('.filterUnread .count').text(count);
		this.$container.find('.popUpWindow .filterOnlyUnread .count').text(count);
	},
	
	selectEntry: function($entry, scrollTo, animate) {
		if ($entry.length == 0) { return; }
		if ($entry.hasClass('noResult') == true) { return; }
		if (this.$ulEntries.find('.entry.selected:first').is($entry)) { return; }
		
		this.$ulEntries.find('.entry.selected').removeClass('selected');
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
				cloneReader.starEntry($star.parents('.entry'), !$star.hasClass('selected'));
			});			
			
			this.scrollEntries();
			
			animate = false;
		}

		if (scrollTo == true) {
			this.scrollToEntry($entry, animate);
		}					

		this.readEntry($entry, true);
		this.getMoreEntries();
	},
	
	scrollToEntry: function($entry, animate) {
		if ($entry.length == 0) { return; }
		
		var top = $entry.offset().top - this.$ulEntries.offset().top + this.$ulEntries.scrollTop() - 10;
		if (animate == true) { 
			this.$ulEntries.stop().animate( {scrollTop: top } );
		}
		else {
			this.$ulEntries.stop().scrollTop(top);
		}
	},
	
	goToEntry: function(next) {
		var $entry = this.$ulEntries.find('.entry.selected');
		if ($entry.length == 0) {
			$entry = this.$ulEntries.find('.entry').first();
		}
		else {
			$entry = (next == true ? $entry.nextAll('.entry:first') : $entry.prevAll('.entry:first'));
		}
		this.selectEntry($entry, true, true);
	},
	
	getMoreEntries: function() {
		// busco más entries si esta visible el li 'noResult', o si el li.selected es casi el ultimo
		if (this.isLastPage == true) { 
			return;
		}
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
		$.ajax({ url: base_url + 'entries/selectFilters' })
		.done($.proxy(
			function(reload, response) {
				if (response['code'] != true) {
					return $(document).alert(response['result']);
				}
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
		, this, reload));	
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
				
			if (this.isVisible(filter, $parent.is(':visible')) == true) {
				this.renderFilter(filter, $parent, index);
				index++;
			}
				
			if (filter.$filter != null && filter.childs != null) {
				this.renderFilters(filter.childs, filter.$filter.find('ul:first'), false);
				this.expandFilter(filter, filter.expanded);
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
					.html('<div><span class="icon" /><a>' + filter.name + '</a><span class="count" /></div>')
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
		
		if (filter.childs != null) {
			$filter.append('<ul />').find('.icon').addClass('arrow');
			$filter.find('.icon').click(
				function(event) {
					var $filter	= $($(event.target).parents('li:first'));
					var filter	= $filter.data('filter');
					cloneReader.expandFilter(filter, !filter.expanded);
				});

			if (filter.expanded == false) { 
				$filter.find('ul').hide(); 
			}
		}

		return $filter;
	},
	
	isVisible: function(filter, parentIsVisible) {
		filter = this.getFilter(filter);
		if (filter.type == 'tag' && (filter.id == TAG_STAR || filter.id == TAG_HOME)) {
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
		return this.indexFilters[filter.type][filter.id];
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
		var $arrow 	= $filter.find('.arrow:first');
		var $ul 	= $filter.find('ul:first');

		if (value != true) {
			$arrow.html('&#9658;')
			$ul.stop().hide('fast', function() { $(this).hide()});
			return;
		}

		var index = 0;
		for (var i=0; i<filter.childs.length; i++) {
			if (this.isVisible(filter.childs[i], true) == true) {
				this.renderFilter(filter.childs[i], $ul, index);
				index++;
			}
		}

		$arrow.html('&#9660;');
		$ul.stop().show('fast', function() { 
			$(this).show(); 
			cloneReader.$ulFilters.getNiceScroll().resize(); 
		});
		
		this.updateNiceScroll();
		
		this.getFilter(this.aFilters).$filter.addClass('selected');
	},
	
	maximiseUlEntries: function(value, animate) {
		var marginLeft = 0;

		if (value == false) {
			marginLeft = this.$ulEntries.data('margin-left');
		}
		
		this.aFilters.isMaximized = value;
// TODO: revisar		
//		this.updateUserFilters();
		this.updateNiceScroll();
		
		if (animate == true) {
			this.$ulEntries.stop().animate(
				{ 'margin-left': marginLeft }, 
				{ 
					duration: 100 ,
				 	complete: function() {
						cloneReader.scrollToEntry(cloneReader.$ulEntries.find('li.selected'), false);
				}
			});
		}
		else {
			this.$ulEntries.stop().css(	{ 'margin-left': marginLeft } ); 			
		}				
	},
	
	updateNiceScroll: function() {
		this.$ulFilters.getNiceScroll().hide();
		if (this.aFilters.isMaximized == false) {
			this.$ulFilters.getNiceScroll().show();
		}	
	},

	addFeed: function() {
		var feedUrl = this.$popupForm.find('input').val();
		if (feedUrl == '') {
			return this.$popupForm.find('input').alert('enter a url');
		}
		if ($.validateUrl(feedUrl) == false) {
			return this.$popupForm.find('input').alert('enter a valid url');
		}

		this.hidePopupWindow();

		$.ajax({
			'url': 		base_url + 'entries/addFeed',
			'data': 	{  'feedUrl': feedUrl },
			'type':	 	'post',
		})
		.done(function(response) {
			if (response['code'] != true) {
				return $(document).alert(response['result']);
			}
			
			cloneReader.loadEntries(true, true, { 'type': 'feed', 'id': response['result']['feedId'] }); 
			cloneReader.loadFilters(true);
		});				
	},
	
	addToSave: function(entry) {
		this.aUserEntries[entry.entryId] = {
			'entryId': 		entry.entryId,	
			'entryRead': 	entry.entryRead,
			'starred': 		entry.starred				
		};
	},
	
	saveUserTags: function() {
		if (Object.keys(this.aUserTags).length == 0) {
			return;
		}
		
		$.ajax({
			'url': 		base_url + 'entries/saveUserTags',
			'data': 	{ 
					'tags': 	$.toJSON(this.aUserTags) 
			},
			'type':	 	'post',
			'async':	false
		})
		.done(function(response) {
			if (response['code'] != true) {
				return $(document).alert(response['result']);
			}
		});			

		this.aUserTags		= {};		
	},
	
	saveData: function(async){
		if (this.$ulEntries.getNiceScroll()[0].scrollrunning == true) {
			return;
		}
		if (Object.keys(this.aUserEntries).length == 0 && Object.keys(this.aUserTags).length == 0) {
			return;
		}
		
		if (async == true) {
			$.countProcess--; // para evitar que muestre el loading a guardar datos en brackground
		}
		$.ajax({
			'url': 		base_url + 'entries/saveData',
			'data': 	{ 
					'entries': 	$.toJSON(this.aUserEntries),
					'tags': 	$.toJSON(this.aUserTags) 
			},
			'type':	 	'post',
			'async':	async
		})
		.done(function(response) {
			if (response['code'] != true) {
				return $(document).alert(response['result']);
			}
		});			
		
		this.aUserEntries 	= {};
		this.aUserTags		= {};
	},

	addTag: function() {
		var tagName = this.$popupForm.find('input').val();
		if (tagName.trim() == '') {
			return this.$popupForm.find('input').alert('enter a tag name');
		}

		this.hidePopupWindow();

		$.ajax({
			'url': 		base_url + 'entries/addTag',
			'data': 	{ 
				'tagName': 	tagName ,
				'feedId':	this.aFilters.id
			},
			'type':	 	'post',
		})
		.done(function(response) {
			if (response['code'] != true) {
				return $(document).alert(response['result']);
			}
			
			cloneReader.loadEntries(true, true, { 'type': 'tag', 'id': response['result']['tagId'] }); 
			cloneReader.loadFilters(true);
		});				
	},

	saveUserFeedTag: function(feedId, tagId, append) {
		this.hidePopupWindow();

		$.ajax({
			'url': 		base_url + 'entries/saveUserFeedTag',
			'data': 	{ 
				'feedId': 	feedId,
				'tagId': 	tagId,
				'append':	append
			},
			'type':	 	'post',
		})
		.done(function(response) {
			if (response['code'] != true) {
				return $(document).alert(response['result']);
			}
			cloneReader.saveUserTags();
			cloneReader.loadFilters(true);
		});
	},
	
	markAsReadFeed: function(feedId) {
		this.hidePopupWindow();

		if (!(confirm('seguro?'))){ // TODO: hacer un popup mas lindo
			return;
		}		

		$.ajax({
			'type':	 	'post',
			'url': 		base_url + 'entries/markAsReadFeed',
			'data': 	{ 'feedId':	feedId 	},
		})
		.done(function(response) {
			if (response['code'] != true) {
				return $(document).alert(response['result']);
			}
			cloneReader.aEntries = {}
			cloneReader.loadEntries(true, true, {});
			cloneReader.loadFilters(true);
		});		
	},
	
	unsubscribeFeed: function(feedId) {
		this.hidePopupWindow();

		if (!(confirm('seguro?'))){ // TODO: hacer un popup mas lindo
			return;
		}		

		$.ajax({
			'type':	 	'post',
			'url': 		base_url + 'entries/unsubscribeFeed',
			'data': 	{ 'feedId':	feedId 	},
		})
		.done(function(response) {
			if (response['code'] != true) {
				return $(document).alert(response['result']);
			}
			cloneReader.loadEntries(true, true, { 'type': 'tag', 'id': TAG_ALL });
			cloneReader.loadFilters(true);
		});		
	},
	
	updateUserFilters: function() {
// TODO: hacer que no guarde tanto asi no mata al servidor		
		$.countProcess--; // para evitar que muestre el loading a guardar datos en brackground
		$.ajax({		
			'url': 		base_url + 'entries/updateUserFilters',
			'data': 	{ 'post': $.toJSON(this.aFilters) },
			'type':		'post'
		})
		.done(function(response) {
			if (response['code'] != true) {
				return $(document).alert(response['result']);
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
		if (this.$popupFeedSettings == null) {
			this.$popupFeedSettings = this.$container.find('.popupFeedSettings');
		}

		this.$popupFeedSettings.find('li').remove();

		var feedId = this.aFilters.id;

		var aItems = [
			{ 'html': 'Mark all as read', 	'callback': function() { cloneReader.markAsReadFeed(cloneReader.aFilters.id); } },
			{ 'html': 'Unsubscribe', 		'callback': function() { cloneReader.unsubscribeFeed(cloneReader.aFilters.id);  } },
			{ 'html': 'New tag', 			'class': 'newTag', 'callback': 
				function(event) { 
					event.stopPropagation(); 
					cloneReader.showPopupForm('enter tag name', function() { cloneReader.addTag(); }, cloneReader.$ulMenu.find('.feedSettings'));
				}
			}
		];

		for (var i=0; i<this.tags.length; i++) {
			var tag = this.tags[i];
			if (tag.tagId != TAG_ALL && tag.tagId != TAG_STAR && tag.tagId != TAG_HOME) {
				var filter	= this.indexFilters['tag'][tag.tagId];
				var check 	= '';
				var hasTag 	= this.feedHasTag(this.getFilter(this.aFilters), filter);
				if (hasTag == true) {
					check = '&#10004';
				}
				aItems.push( { 'html': '<span class="check">' + check + '</span>' + tag.tagName,  'class': (hasTag == true ? 'selected' : ''), 'data': { 'feedId': feedId, 'tagId': tag.tagId} , 'callback': function() {  var $filter = $(this); cloneReader.saveUserFeedTag($filter.data('feedId'), $filter.data('tagId'), !$filter.hasClass('selected') ); } } );
			}
		}

		this.createMenu(aItems, this.$popupFeedSettings);
		this.$popupFeedSettings.find('li.newTag .arrow').hide();
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

	showPopupForm: function(placeholder, callback, $li){
		if (this.$popupForm == null) {
			this.$popupForm = $('<form> <input /> <button> add</button></form>').addClass('popUpWindow').addClass('popupForm');
			
			this.$popupForm.find('input').keyup(function(event) {
				event.stopPropagation();
			});
		}

		this.hidePopupWindow();
		$li.addClass('expanded');

		this.$popupForm
			.unbind()
			.submit(function(event) {
				event.preventDefault();
				callback();
				return false;
			});

		var top		= $li.offset().top + $li.height() - this.$container.offset().top + 1; // FIXME: revisar el '1'
		var left 	= $li.offset().left - this.$container.offset().left;
		var width 	= 300;

		this.showPopupWindow(this.$popupForm, top, left, width);
		
		this.$popupForm.find('input').attr('placeholder', placeholder).val('').focus();
	},
	
	resizeWindow: function() {
		this.hidePopupWindow();
		$('.content > h1').hide();
		$('.content').width('auto').css( { 'min-height': 1, 'overflow': 'hidden' });
		
		this.$ulFilters.height(1);
		
		$('.nicescroll-rails').hide(); 
		
		this.$ulEntries
			.height(1)
			.height($(document).outerHeight(true) - 1 - this.$ulEntries.offset().top - $('#footer').outerHeight(true)); // TODO: revisar el -1

		this.$ulFilters.height(this.$ulEntries.height());
		
		$('.nicescroll-rails').show();
		this.updateNiceScroll();
		
		this.scrollEntries();
	},

	showPopupWindow: function($popUpWindow, top, left, width) {
		$popUpWindow
			.css({ 'top': top,  'left': left, 'width': width, 'max-height': this.$ulEntries.height() })
			.appendTo(this.$container)
			.stop().fadeIn();
	},
	
	hidePopupWindow: function() {
		this.$container.find('.popUpWindow').hide();
		this.$ulMenu.find('li').removeClass('expanded');
	},
	
	humanizeDatetime: function(datetime, format) {
		datetime = moment(datetime, 'YYYY-MM-DDTHH:mm:ss').add('ms', -this.fixDatetime);
		if (datetime >= moment()) {
			datetime = moment().add('ms', -1);
		} 
		
		if (format == null) {
			return datetime.fromNow();
		}
		return datetime.format('LLL');
	}	
};
