cloneReader = {
	init: function(aFilters) {
		this.$container = $('#cloneReader');
		this.$ulMenu 	= $('<ul class="ulMenu" />').appendTo(this.$container);
		this.$ulFeeds	= $('<ul class="ulFeeds"/>').appendTo(this.$container); 
		this.$ulEntries	= $('<ul class="ulEntries"/>').appendTo(this.$container);
		
		this.fixDatetime = moment(datetime, 'YYYY-MM-DDTHH:mm:ss').diff(moment(), 'ms'); // guardo en memoria la diferencia de tiempo entre la db y el cliente, para mostrar bien las fechas
		moment.lang('es'); // TODO: harckodeta!
		
		this.$ulEntries.data('margin-left', this.$ulEntries.css('margin-left'));		

		this.aEntries 	= {};
		this.aFeeds		= {};

		this.aFilters 	= $.extend({
			'onlyUnread':	true,
			'sortDesc': 	true,
			'id': 			null, 
			'type': 		null,
			'viewType': 	'detail'
		}, aFilters);

		this.aUserEntries 	= {};
		this.aUserFeeds		= {};
		
		this.renderMenu();
		this.loadFeeds();
		this.initEvents();
		this.resizeWindow();
	},

	initEvents: function() {
		setInterval(function() { cloneReader.saveData(true); }, (FEED_TIME_SAVE * 1000)); 
		setInterval(function() { cloneReader.reloadFeeds(); }, (FEED_TIME_RELOAD * 60000));
		setInterval(function() { cloneReader.updateEntriesDateTime(); }, (FEED_TIME_RELOAD * 60000));
		
		this.$ulFeeds.niceScroll({'cursorcolor': '#CCC', 'cursorwidth': '8' });
		this.$ulEntries.niceScroll({'cursorcolor': '#CCC', 'cursorwidth': '8' });						
		
		$(window).resize(function() {
			cloneReader.resizeWindow()
		});

		this.$ulEntries.scroll($.proxy(
			function(event) {
				this.$ulEntries.find('.entry').each(
					function() {
						cloneReader.renderEntry($(this));
					}
				);
				
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
					var offset 	= $entry.offset();
					if (top <= (offset.top + 10)) { // TODO: revisar el + 10 (al moverse con las flechas, selecciona el item siguiente)
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
		
		$(document).keydown($.proxy(
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
						this.expandEntries();
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
	
	renderMenu: function() {
		this.createMenu([
			{ 'html': '&#8644;', 	'title': 'expand', 'class': 'expand',	'callback': function() { cloneReader.expandEntries(true) }},
			{ 'html': '', 'class': 'feedName' }, // TODO: hacer que se ajuste el width al espacio disponible!
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
		
		var lastFilters =  $.toJSON(this.aFilters);
		this.aFilters 	=	$.extend(this.aFilters, aFilters);
		
		if (cloneReader.$ulEntries.children().length == 0) { // Para la primera carga
			forceRefresh = true;
		}
		
		if (forceRefresh != true && $.toJSON(this.aFilters) === lastFilters) {
			return;
		}

		if (clear == true) {
			delete this.aFilters.lastEntryId;
			this.$ulEntries.children().remove();
			this.$ulEntries.scrollTop(0);
		}
		
		if (this.ajax) {
			this.ajax.abort();
			this.ajax = null;
		}
		
		this.renderNotResult(true);

		this.ajax = $.ajax({		
			'url': 		base_url + 'entries/select',
			'data': 	{ 'post': $.toJSON(this.aFilters) },
			'type':		'post'
		})
		.done(function(response) {
			if (response['code'] != true) {
				return $(document).alert(response['result']);
			}
			cloneReader.renderEntries(response.result);
		});	
		
		this.updateUlFeeds();
		this.updateUlMenu();
	},
	
	renderEntries: function(result) {
		this.$ulEntries.removeClass('list');
		if (this.aFilters.viewType == 'list') {
			this.$ulEntries.addClass('list');
		}
		
		if (result.length == 0) {
			this.updateMenuCount();
			this.renderNotResult();
			return;
		}
		
		this.$noResult.remove();
		
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
		this.renderNotResult();
	},
	
	renderEntry: function($entry) {
		if ($entry.hasClass('noResult') == true) {
			return;
		}
		if ($entry.visible( true ) == false) {
			$entry.addClass('clean').children().remove();
			return;
		}

		var entryId = $entry.data('entryId');
		var entry 	= this.aEntries[entryId];

		if ($entry.hasClass('clean') == false) {
			return;
		}
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
	},
	
	
	renderDetailEntry: function($entry, entry) {
		var $header = $('<div/>').addClass('header').appendTo($entry);

		$('<a />')
			.addClass('entryTitle')
			.attr('href', entry.entryUrl)
			.css('background-image', 'url(https://plus.google.com/_/favicon?domain=' + entry.feedLInk + ')')
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
			cloneReader.selectEntry($entry, true, true);
		});
	},
	
	renderListEntry: function($entry, entry) {
		var entryContent 	= $('<div/>').html(entry.entryContent).text();
		var $div 			= $('<div/>').addClass('title').appendTo($entry);
		
		$('<span />').addClass('star').appendTo($div);
		$('<span />').addClass('feedName').text(entry.feedName).appendTo($div);
		$('<span />').addClass('entryContent').text(entryContent)
			.appendTo($div)
			.prepend($('<h2 />').text(entry.entryTitle));
		$('<span />').addClass('entryDate').appendTo($div);

		$entry.find('.feedName, .entryContent').click(function(event) {
			var $entry = $(event.target).parents('.entry');
			cloneReader.selectEntry($entry, true, false);
		});	
	},	
	
	renderNotResult: function(loading) {
		if (this.$noResult == null) {
			this.$noResult = $('<li/>').addClass('noResult');
		}
		this.$noResult.css('min-height', this.$ulEntries.height() - 30).appendTo(this.$ulEntries);
		
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
			var sum 	= (value == true ? -1 : +1);
			var $feed 	= this.$ulFeeds.find('li[data-type=feed][data-id=' + feedId + ']');
			this.aFeeds[feedId].count = parseInt(this.aFeeds[feedId].count) + sum;
			if (this.aFeeds[feedId].count < 0) {
				this.aFeeds[feedId].count = 0;
			}
			

			// actualizo solo los contadores de la parte del arbol de feeds seleccionada
			this.renderCounts($feed, this.aFeeds[feedId].count);
			
			var $aFeeds = $.merge([], $feed);
			for (var i=0; i<$feed.length; i++) {
				$aFeeds = $.merge($aFeeds, $($feed[i]).parents('li'));
			}
			$aFeeds = $($aFeeds);

			this.updateTagsCount($aFeeds.filter('[data-type=tag]'));
			this.updateMenuCount();
			this.updateUlFeeds($aFeeds);
		}

		$entry.removeClass('readed');
		if (value == true) {
			$entry.addClass('readed');
		}
	},
	
	renderCounts: function($li, count) {
		if (count < 0) {
			count = 0;
		}
					
		var $count = $li.find('.count:first');
		$count.text('(' + count + ')');
		$count.hide();
		if (count > 0) {
			$count.show();
		}
		$li.data('count', count);
	},
	
	updateUlMenu: function() {
		this.$ulMenu.find('.filterSort span:first').text(this.$container.find(this.aFilters.sortDesc == true ? '.popUpWindow .filterNewestSort' : '.popUpWindow .filterOldestSort').text());
		this.$ulMenu.find('.filterUnread span:first').html(this.$container.find(this.aFilters.onlyUnread == true ? '.popUpWindow .filterOnlyUnread' :  '.popUpWindow .filterAllItems').html());
		this.$ulMenu.find('.feedName').text(this.$ulFeeds.find('.selected a:first').text());
		
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
		
		this.$ulMenu.find('.filterUnread, .filterSort').show();
	},

	updateUlFeeds: function($feeds) {
		this.$ulFeeds.find('li.selected').removeClass('selected');
		this.$ulFeeds.find('li[data-type=' + this.aFilters.type + '][data-id=' + this.aFilters.id + ']').addClass('selected');
		
		if ($feeds == null) {
			$feeds = this.$ulFeeds.find('li[data-type=tag][data-id=' + TAG_ALL + '] li');
		}
		
		$feeds
			.removeClass('empty')
			.each(function() {
				var $feed = $(this);
				if ($feed.data('count') == 0) {
					$feed.addClass('empty');
				}
				cloneReader.renderFeedVisibility($feed);
			});
	},

	renderFeedVisibility: function($feed) {
		var visible = false;

		if (this.aFilters.onlyUnread == false) {
			visible = true;
		}
		else if (parseInt($feed.data('count')) != 0) {
			visible = true;
		}
		else if ($feed.hasClass('selected') == true || $feed.find('li.selected').length != 0) {
			visible = true;
		}

		$feed.hide().toggle(visible);
	},
	
	updateTagsCount: function($tag) {
		if ($tag == null) {
			var $tag	= this.$ulFeeds.find('li[data-type=tag]');
		}
		for(var z=0; z<$tag.length; z++) { 
			var $childs 	= $($tag[z]).find('ul > li[data-type=feed]');
			var count		= 0;
			for (var i=0; i<$childs.length; i++) {
				var feedId = $($childs[i]).data('id');
				count += parseInt(this.aFeeds[feedId].count);			
			}
			this.renderCounts($($tag[z]), count);
		}		
	},
	
	updateMenuCount: function() {
		var count = this.$ulFeeds.find('.selected .count:first').text().replace('(', '').replace(')', '');
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
			
			this.scrollToEntry($entry, false);
		}
		else {
			if (scrollTo == true) {
				this.scrollToEntry($entry, animate);
			}					
		}

		this.readEntry($entry, true);
		this.getMoreEntries();
	},
	
	scrollToEntry: function($entry, animate) {
		if ($entry.length == 0) { return; }
		
		var top = $entry.offset().top - this.$ulEntries.offset().top + this.$ulEntries.scrollTop();
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
		if (
			this.$noResult.visible(true) == true 
			||
			((this.$ulEntries.find('.entry').length - 2) <= this.$ulEntries.find('.entry.selected').index()) // TODO: hacer una variable con el '2' !
		) {
			var lastEntryId = this.$ulEntries.find('.entry').last().data('entryId');
			if (this.aFilters.lastEntryId != lastEntryId) {
				this.loadEntries(false, false, { 'lastEntryId': this.$ulEntries.find('.entry').last().data('entryId') });
			}	
		}
	},

	loadFeeds: function() {
		$.ajax({ url: base_url + 'entries/selectFeeds' })
		.done(
			function(response) {
				if (response['code'] != true) {
					return $(document).alert(response['result']);
				}

				cloneReader.renderFeeds(response.result, cloneReader.$ulFeeds);
	
				var $feed = cloneReader.$ulFeeds.find('li[data-type=' + cloneReader.aFilters.type + '][data-id=' + cloneReader.aFilters.id + '] a:first');
				if ($feed.length == 0) {
					$feed = cloneReader.$ulFeeds.find('li:first a');
				}
				$feed.click();
			}
		);	
	},
	
	reloadFeeds: function() {
		$.ajax({ url: base_url + 'entries/selectFeeds' })
		.done(
			function(response) {
				if (response['code'] != true) {
					return $(document).alert(response['result']);
				}

				cloneReader.$ulFeeds.children().remove();
				cloneReader.aFeeds	= {};
						
				cloneReader.renderFeeds(response.result, cloneReader.$ulFeeds);
				cloneReader.updateUlFeeds();
				cloneReader.updateUlMenu();
				cloneReader.$ulFeeds.find('.selected').hide().fadeIn('slow');
			}
		);	
	},	

	renderFeeds: function(result, $parent){
		for (var i=0; i<result.length; i++) {
			var feed = result[i];
			if (feed.childs != null) {
				var $feed = this.renderFeed('tag', feed, $parent);
				$feed.data('expanded', feed.expanded);
				$feed.append('<ul />').find('.icon').addClass('arrow');
				$feed.find('.icon')
					.click(
						function(event) {
							var $feed 	= $($(event.target).parents('li:first'));
							var $ul 	= $feed.find('ul');
							cloneReader.expandFeed($feed, !$ul.is(':visible'));
						}
					);

				if (feed.expanded == false) { $feed.find('ul').hide(); }
				this.renderFeeds(feed.childs, $feed.find('ul'));
				this.expandFeed($feed, feed.expanded);
			}
			else {
				this.aFeeds[feed.id] = feed;
				var $feed = this.renderFeed('feed', feed, $parent);
				this.renderCounts($feed, feed.count);
			}
		}
		this.updateTagsCount();
		this.resizeWindow();
	},

	renderFeed: function(type, feed, $parent) {
		var $feed = $parent.find('li[data-type=' + feed.type + '][data-id=' + feed.id + ']');
		if ($feed.length != 0) {
			return $feed;
		}

		var $feed = $('<li/>')
					.attr('data-type', feed.type)
					.attr('data-id', feed.id)
					.data({ 'type': feed.type, 'name': feed.name, 'id': feed.id, 'count': (feed.count || 0) })
					.html('<div><span class="icon" /><a>' + feed.name + '</a><span class="count" /></div>')
					.appendTo($parent);

		$feed.find('a')
			.attr('title', feed.name)
			.click(function (event) {
				var $feed = $($(event.target).parents('li:first'));
				cloneReader.loadEntries(true, false, { 'type': $feed.data('type'), 'id': $feed.data('id')});
			});
		if (feed.icon != null) {
			$feed.find('.icon').css('background-image', 'url(' + feed.icon + ')');
		}

		return $feed;
	},

	expandFeed: function($feed, value){
		if ($feed.data('expanded') != value) {
			$feed.data('expanded', value);
			this.aUserFeeds[$feed.data('id')] = {
				'tagId': 		$feed.data('id'),
				'expanded': 	value
			};
		}
		
		var $arrow 	= $feed.find('.arrow:first');
		var $ul 	= $feed.find('ul:first');

		if (value != true) {
			$arrow.html('&#9658;')
			$ul.stop().hide('fast', function() { $(this).hide()});
			return;
		}

		$arrow.html('&#9660;');
		$ul.stop().show('fast', function() { $(this).show()});
	},
	
	expandEntries: function() {
		var marginLeft = (parseInt(this.$ulEntries.css('margin-left')) == 0 ? this.$ulEntries.data('margin-left') : 0);  
			
		this.$ulEntries.stop().animate(
			{ 'margin-left': marginLeft }, 
			{ 
				duration: 100 ,
			 	complete: function() {
					cloneReader.scrollToEntry(cloneReader.$ulEntries.find('li.selected'), false);
			}
		});				
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
			cloneReader.reloadFeeds();
		});				
	},
	
	addToSave: function(entry) {
		this.aUserEntries[entry.entryId] = {
			'entryId': 		entry.entryId,	
			'entryRead': 	entry.entryRead,
			'starred': 		entry.starred				
		};
	},
	
	saveData: function(async){
		if (this.$ulEntries.getNiceScroll()[0].scrollrunning == true) {
			return;
		}
		if (Object.keys(this.aUserEntries).length == 0 && Object.keys(this.aUserFeeds).length == 0) {
			return;
		}
		
		$.countProcess--; // para evitar que muestre el loading a guardar datos en brackground
		$.ajax({
			'url': 		base_url + 'entries/saveData',
			'data': 	{ 
					'entries': 	$.toJSON(this.aUserEntries),
					'tags': 	$.toJSON(this.aUserFeeds) 
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
		this.aUserFeeds		= {};
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
			cloneReader.reloadFeeds();
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
			cloneReader.reloadFeeds();
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
			cloneReader.reloadFeeds();
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
		if (entry.entryDate == null) { return; }
		
		if (this.aFilters.viewType == 'detail') {
			$entry.find('.entryDate').text(entry.entryDate + ' (' + this.humanizeDatetime(entry.entryDate) + ')');
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
			{ 'html': 'Unsubscribe', 	'callback': function() { cloneReader.unsubscribeFeed(cloneReader.aFilters.id);  } },
			{ 'html': 'New tag', 		'class': 'newTag', 'callback': 
				function(event) { 
					event.stopPropagation(); 
					cloneReader.showPopupForm('enter tag name', function() { cloneReader.addTag(); }, cloneReader.$ulMenu.find('.feedSettings'));
				}
			}
		];

		var aLi = this.$ulFeeds.find('li[data-type=tag][data-id=' + TAG_ALL + '] li[data-type=tag]');
		for (var i=0; i<aLi.length; i++) {
			var $feed 	= $(aLi[i]);
			var check 	= '';
			var hasTag 	= this.$ulFeeds.find('li[data-type=tag][data-id=' + $feed.data('id') + '] li[data-type=feed][data-id=' + feedId + ']').length != 0;
			if (hasTag == true) {
				check = '&#10004';
			}
			aItems.push( { 'html': '<span class="check">' + check + '</span>' + $feed.data('name'),  'class': (hasTag == true ? 'selected' : ''), 'data': { 'feedId': feedId, 'tagId': $feed.data('id')} , 'callback': function() {  var $feed = $(this); cloneReader.saveUserFeedTag($feed.data('feedId'), $feed.data('tagId'), !$feed.hasClass('selected') ); } } );

		}

		this.createMenu(aItems, this.$popupFeedSettings);
		this.$popupFeedSettings.find('li.newTag .arrow').hide();
	},

	showPopupForm: function(placeholder, callback, $li){
		if (this.$popupForm == null) {
			this.$popupForm = $('<form> <input /> <button> add</button></form>').addClass('popUpWindow').addClass('popupForm');
			
			this.$popupForm.find('input').keydown(function(event) {
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
		$('.content').width('auto');
		
		this.$ulFeeds.height(1);
		
		$('.nicescroll-rails').hide(); 
		
		this.$ulEntries
			.height(1)
			.height($(document).outerHeight(true) - 1 - this.$ulEntries.offset().top - $('#footer').outerHeight(true)); // TODO: revisar el -1

		this.$ulFeeds.height(this.$ulEntries.height());
		
		$('.nicescroll-rails').show();
			
		this.scrollToEntry(this.$ulEntries.find('li.selected'), false);
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
	
	humanizeDatetime: function(datetime) {
		datetime = moment(datetime, 'YYYY-MM-DDTHH:mm:ss').add('ms', -this.fixDatetime);
		if (datetime >= moment()) {
			datetime = moment().add('ms', -1);
		} 
		
		return datetime.fromNow();
	}	
};
