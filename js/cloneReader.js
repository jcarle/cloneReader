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
		// TODO: agregar un cron para que actualize la fecha de cada entry
		
		$(window).resize(function() {
			cloneReader.resizeWindow()
		});

		this.$ulEntries.scroll($.proxy(
			function(event) {
				this.$ulEntries.find('> li').each(
					function() {
						cloneReader.renderEntry($(this));
					}
				);
				
				if (this.$ulEntries.is(':animated') == true) {
					return;
				}				
				
				var top 	= this.$ulEntries.offset().top;
				var height 	= this.$ulEntries.outerHeight();
				var aLi		= this.$ulEntries.find('li .header').parent(); // recorro solos los visibles ( tienen header ;)
				for (var i=0; i<aLi.length; i++) {
					var $li 	= $(aLi[i]);
					var offset 	= $li.offset();
					if (top <= (offset.top + 10)) { // TODO: revisar el + 10 (al moverse con las flechas, selecciona el item siguiente)
						this.selectEntry($li, false, false);
						return;
					}
					if (top >= offset.top && (offset.top + $li.height())  >= height) {
						this.selectEntry($li, false, false);
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
						var $li = this.$ulEntries.find('li.selected');
						if ($li.length != 0) {
							this.starEntry($li, ($li.find('.star.selected').length == 0));
						}
						break;
					case 86: // V: open link
						var $li = this.$ulEntries.find('li.selected');
						if ($li.length != 0) {
							this.$ulEntries.find('li.selected .header a')[0].click();
						}
						break;
					case 77: // M read entry
						var $li = this.$ulEntries.find('li.selected');
						if ($li.length != 0) {					
							this.readEntry($li, $li.find('.read').hasClass('selected'));
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
			{ 'html': '&#9660;', 	'title': 'next', 	'callback': function() { cloneReader.goToEntry(true) }},
			{ 'html': '&#9650;', 	'title': 'prev', 	'callback': function() { cloneReader.goToEntry(false) }},
			{ 'html': '&#10226;', 	'title': 'reload', 	'class': 'reload', 'callback': function() { cloneReader.loadEntries(true, true, {}) }},
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
		
		this.$ulMenu.find('li.add .arrow').hide();
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
			this.renderNotResult();
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
		if (result.length == 0) {// && this.$ulEntries.find('> li').length == 0) {
			this.updateMenuCount();
			this.renderNotResult();
			return;
		}
		
		for (var i=0; i<result.length; i++) {
			var entry = result[i];
			if (this.aEntries[entry.entryId] == null) {
				this.aEntries[entry.entryId] = entry;
			}
			
			var $li = $('<li/>')
					.addClass('clean')
					.data({ 'entryId': entry.entryId } )
					.appendTo(this.$ulEntries);
					
			this.renderEntry($li);
		}
	
		this.updateMenuCount();
	},
	
	renderEntry: function($li) {
		if ($li.hasClass('noResult') == true) {
			return;
		}
		if ($li.visible( true ) == false) {
			$li.addClass('clean').children().remove();
			return;
		}

		var entryId = $li.data('entryId');
		var entry 	= this.aEntries[entryId];

		if ($li.hasClass('clean') == false) {
			return;
		}
		$li.removeClass('clean');
			
		var $header = $('<div/>').addClass('header').appendTo($li);

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
				cloneReader.loadEntries(true, false, { 'type': 'feed', 'id': cloneReader.aEntries[$(this).parents('li').data('entryId')]['feedId'] });
			}
		);

		$('<span />').addClass('entryDate').text(this.humanizeDatetime(entry.entryDate)).appendTo($header);
		$('<span />').addClass('star').appendTo($header);
			
		var $entryContent = $('<div/>'); // TODO: revisar esta parte, chequear que elimine bien los <scripts>
		$entryContent.text(entry.entryContent); //$entryContent[0].innerHTML = entry.entryContent;
		$entryContent.find('script').remove();
		$entryContent.find('iframe').remove();
		$('<p/>').html($entryContent.text()).appendTo($li);

		var $footer = $('<div/>').addClass('footer').appendTo($li);

		$('<span />').addClass('star').appendTo($footer);
		$('<span />').addClass('read').html('<span class="checkbox"/>keep unread').appendTo($footer);

		$li.find('.star').click(function(event) {
				event.stopPropagation();
				$star = $(event.target);
				cloneReader.starEntry($star.parents('li'), !$star.hasClass('selected'));
			});
		$li.find('.read .checkbox').click(function(event) {
				event.stopPropagation();
				$checkbox = $(event.target);
				cloneReader.readEntry($checkbox.parents('li'), $checkbox.parent().hasClass('selected'));
			});				
						
		this.starEntry($li, entry.starred);
		this.readEntry($li, (entry.entryRead == true));	
		
		$li.css('min-height', $li.height());
		$li.find('img').load(
			function(event) {
				var $li = $(event.target).parents('li');
				$li.css('min-height', $li.height());
			}
		);
		
		$li.stop().hide().fadeIn();
		
		$li.find('p').children().removeAttr('class');
		$li.find('a').attr('target', '_blank');
		
		$li.click(function(event) {
			var $li = $(event.target).parents('li');
			if ($li.hasClass('selected') == true) { return; }
			cloneReader.selectEntry($li, true, true);
		});		
	},
	
	renderNotResult: function() {
		if (this.$liNoResult == null) {
			this.$liNoResult = $('<li/>').text('no more entries').addClass('noResult');
		}
		this.$liNoResult.css('min-height', Math.max(200, this.$ulEntries.height() - this.$ulEntries.find('li:last').height())).appendTo(this.$ulEntries);			
	},

	starEntry: function($li, value) {
		$li.find('.star').removeClass('selected');
		if (value == true) {
			$li.find('.star').addClass('selected');
		}
		
		var entryId = $li.data('entryId');
		var starred = (this.aEntries[entryId].starred == 1);
		this.aEntries[entryId].starred = value;
		if (starred != value) {
			this.addToSave(this.aEntries[entryId]);
		}
	},

	readEntry: function($li, value) {
		$li.find('.read .checkbox').html('')
		$li.find('.read').removeClass('selected');
		if (value == false) {
			$li.find('.read .checkbox').html('&#10004;')
			$li.find('.read').addClass('selected');
		}
		
		var entryId		= $li.data('entryId');
		var entryRead	= (this.aEntries[entryId].entryRead == 1);
		this.aEntries[entryId].entryRead = value;
		if (entryRead != value) {
			this.addToSave(this.aEntries[entryId]);
			this.updateCounts(this.aEntries[entryId].feedId, (value == true ? -1 : +1));
		}

		$li.removeClass('readed');
		if (value == true) {
			$li.addClass('readed');
		}
	},
	
	renderCounts: function($li, count) {
		var $count = $li.find('.count:first');
		$count.text('(' + count + ')');
		$count.hide();
		if (count > 0) {
			$count.show();
		}
		$li.data('count', count);
	},
	
	updateCounts: function(feedId, sum){
		var $li = this.$ulFeeds.find('li[data-type=feed][data-id=' + feedId + ']');
		
		this.aFeeds[feedId].count = parseInt(this.aFeeds[feedId].count) + sum;
		if (this.aFeeds[feedId].count < 0) {
			this.aFeeds[feedId].count = 0;
		}
		this.renderCounts($li, this.aFeeds[feedId].count);
		
		
		this.updateTagsCount();
		this.updateMenuCount();
		this.updateUlFeeds();
	},

	updateUlMenu: function() {
		this.$ulMenu.find('.filterSort span:first').text(this.$container.find(this.aFilters.sortDesc == true ? '.popUpWindow .filterNewestSort' : '.popUpWindow .filterOldestSort').text());
		this.$ulMenu.find('.filterUnread span:first').html(this.$container.find(this.aFilters.onlyUnread == true ? '.popUpWindow .filterOnlyUnread' :  '.popUpWindow .filterAllItems').html());
		this.$ulMenu.find('.feedName').text(this.$ulFeeds.find('.selected a:first').text());

		this.$ulMenu.find('li.feedSettings').hide();
		if (this.aFilters.type == 'feed') {
			this.$ulMenu.find('li.feedSettings').show();
		}
	},

	updateUlFeeds: function() {
		this.$ulFeeds.find('li.selected').removeClass('selected');
		this.$ulFeeds.find('li[data-type=' + this.aFilters.type + '][data-id=' + this.aFilters.id + ']').addClass('selected');

		this.$ulFeeds.find('li[data-type=tag][data-id=' + TAG_ALL + '] li')
			.removeClass('empty')
			.each(function() {
				var $li = $(this);
				if ($li.data('count') == 0) {
					$li.addClass('empty');
				}
				cloneReader.renderFeedVisibility($li);
			});
	},

	renderFeedVisibility: function($li) {
		var visible = false;

		if (this.aFilters.onlyUnread == false) {
			visible = true;
		}
		else if (parseInt($li.data('count')) != 0) {
			visible = true;
		}
		else if ($li.hasClass('selected') == true || $li.find('li.selected').length != 0) {
			visible = true;
		}

		$li.hide().toggle(visible);
	},
	
	updateTagsCount: function() {
		var $li	= this.$ulFeeds.find('li[data-type=tag]');
		for(var z=0; z<$li.length; z++) { 
			var $childs 	= $($li[z]).find('ul > li[data-type=feed]');
			var count		= 0;
			for (var i=0; i<$childs.length; i++) {
				var feedId = $($childs[i]).data('id');
				count += parseInt(this.aFeeds[feedId].count);			
			}
			this.renderCounts($($li[z]), count);
		}		
	},
	
	updateMenuCount: function() {
		var count = this.$ulFeeds.find('.selected .count:first').text().replace('(', '').replace(')', '');
		this.$ulMenu.find('.filterUnread .count').text(count);
		this.$container.find('.popUpWindow .filterOnlyUnread .count').text(count);
	},
	
	selectEntry: function($li, scrollTo, animate) {
		if ($li.length == 0) { return; }
		if ($li.hasClass('noResult') == true) { return; }
		if (this.$ulEntries.find('li.selected:first').is($li)) { return; }
		
		this.$ulEntries.find('li.selected').removeClass('selected');
		$li.addClass('selected');
			
		if (scrollTo == true) {
			this.scrollToEntry($li, animate);
		}

		this.readEntry($li, true);

		var lastEntryId = this.$ulEntries.find('> li').last().data('entryId');
		if (this.aFilters.lastEntryId != lastEntryId &&  (this.$ulEntries.find('> li').length - 2) <= $li.index()) { // TODO: hacer una variable con el '2' !
			this.loadEntries(false, false, { 'lastEntryId': this.$ulEntries.find('> li').last().data('entryId') });
		}
	},
	
	scrollToEntry: function($li, animate) {
		if ($li.length == 0) { return; }

		var top = $li.offset().top - this.$ulEntries.offset().top + this.$ulEntries.scrollTop();
		if (animate == true) { 
			this.$ulEntries.stop().animate( {scrollTop: top } );
		}
		else {
			this.$ulEntries.stop().scrollTop(top);
		}
	},
	
	goToEntry: function(next) {
		var $li = this.$ulEntries.find('li.selected');
		if ($li.length == 0) {
			$li = this.$ulEntries.find('li').first();
		}
		else {
			$li = (next == true ? $li.nextAll('li:first') : $li.prevAll('li:first'));
		}
		this.selectEntry($li, true, true);		
	},

	loadFeeds: function() {
		$.ajax({ url: base_url + 'entries/selectFeeds' })
		.done(
			function(response) {
				if (response['code'] != true) {
					return $(document).alert(response['result']);
				}

				cloneReader.renderFeeds(response.result, cloneReader.$ulFeeds);
	
				var $li = cloneReader.$ulFeeds.find('li[data-type=' + cloneReader.aFilters.type + '][data-id=' + cloneReader.aFilters.id + '] a:first');
				if ($li.length == 0) {
					$li = cloneReader.$ulFeeds.find('li:first a');
				}
				$li.click();
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
				var $li = this.renderFeed('tag', feed, $parent);
				$li.data('expanded', feed.expanded);
				$li.append('<ul />').find('.icon').addClass('arrow');
				$li.find('.icon')
					.click(
						function(event) {
							var $li 	= $($(event.target).parents('li:first'));
							var $ul 	= $li.find('ul');
							cloneReader.expandFeed($li, !$ul.is(':visible'));
						}
					);

				if (feed.expanded == false) { $li.find('ul').hide(); }
				this.renderFeeds(feed.childs, $li.find('ul'));
				this.expandFeed($li, feed.expanded);
			}
			else {
				this.aFeeds[feed.id] = feed;
				var $li = this.renderFeed('feed', feed, $parent);
				this.renderCounts($li, feed.count);
			}
		}
		this.updateTagsCount();
	},

	renderFeed: function(type, feed, $parent) {
		var $li = $parent.find('li[data-type=' + feed.type + '][data-id=' + feed.id + ']');
		if ($li.length != 0) {
			return $li;
		}

		var $li = $('<li/>')
					.attr('data-type', feed.type)
					.attr('data-id', feed.id)
					.data({ 'type': feed.type, 'name': feed.name, 'id': feed.id, 'count': (feed.count || 0) })
					.html('<div><span class="icon" /><a>' + feed.name + '</a><span class="count" /></div>')
					.appendTo($parent);

		$li.find('a')
			.attr('title', feed.name)
			.click(function (event) {
				var $li = $($(event.target).parents('li:first'));
				cloneReader.loadEntries(true, false, { 'type': $li.data('type'), 'id': $li.data('id')});
			});
		if (feed.icon != null) {
			$li.find('.icon').css('background-image', 'url(' + feed.icon + ')');
		}

		return $li;
	},

	expandFeed: function($li, value){
		if ($li.data('expanded') != value) {
			$li.data('expanded', value);
			this.aUserFeeds[$li.data('id')] = {
				'tagId': 		$li.data('id'),
				'expanded': 	value
			};
		}
		
		var $arrow 	= $li.find('.arrow:first');
		var $ul 	= $li.find('ul:first');

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
			var $li 	= $(aLi[i]);
			var check 	= '';
			var hasTag 	= this.$ulFeeds.find('li[data-type=tag][data-id=' + $li.data('id') + '] li[data-type=feed][data-id=' + feedId + ']').length != 0;
			if (hasTag == true) {
				check = '&#10004';
			}
			aItems.push( { 'html': '<span class="check">' + check + '</span>' + $li.data('name'),  'class': (hasTag == true ? 'selected' : ''), 'data': { 'feedId': feedId, 'tagId': $li.data('id')} , 'callback': function() {  var $li = $(this); cloneReader.saveUserFeedTag($li.data('feedId'), $li.data('tagId'), !$li.hasClass('selected') ); } } );

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

		var top				= $li.offset().top + $li.height() - this.$container.offset().top + 1; // FIXME: revisar el '1'
		var left 			= $li.offset().left - this.$container.offset().left;
		var width 			= 300;

		this.showPopupWindow(this.$popupForm, top, left, width);
		
		this.$popupForm.find('input').attr('placeholder', placeholder).val('').focus();
	},
	
	resizeWindow: function() {
		this.hidePopupWindow();
		$('.content').width('auto');
		
		this.$ulFeeds.height(1);
		this.$ulEntries
			.height(1)
			.height($(document).outerHeight(true) - 1 - this.$ulEntries.offset().top - $('#footer').outerHeight(true)); // TODO: revisar el -1

		this.$ulFeeds.height(this.$ulEntries.height());
			
		this.scrollToEntry(this.$ulEntries.find('li.selected'), false);
	},

	showPopupWindow: function($popUpWindow, top, left, width) {
		$popUpWindow
			.css({ 'top': top,  'left': left, 'width': width })
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
