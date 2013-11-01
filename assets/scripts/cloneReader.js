cloneReader = {
	init: function(aFilters) {
		this.$container = $('#cloneReader');
		this.$toolbar 	= $('<nav class="navbar navbar-default navbar-fixed-top" role="navigation" />').appendTo(this.$container);
		this.$ulFilters	= $('<ul class="ulFilters"/>').appendTo(this.$container);
		this.$ulEntries	= $('<ul class="ulEntries"  />').appendTo(this.$container);		 		
		
		this.fixDatetime = moment(datetime, 'YYYY-MM-DDTHH:mm:ss').diff(moment(), 'ms'); // guardo en memoria la diferencia de tiempo entre la db y el cliente, para mostrar bien las fechas
		moment.lang('es'); // TODO: harckodeta!

		this.minUnreadEntries 	= 2;
		this.isLastPage			= false;
		this.currentEntries		= []; // para guardar las entries visibles y no volver a pedir al servidor si solo se cambia el tipo de vista
		this.aEntries	 		= {};
		this.filters			= null;
		this.tags				= null;
		this.lastScrollTop 		= 0;
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
		
		
		if (this.isMobile == true) {
			this.aFilters.isMaximized = false;
		}
		
		this.getIsMobile();
		this.buildCache();
		this.renderToolbar();
		this.loadFilters(false);
		this.initEvents();
		this.resizeWindow();
	},

	initEvents: function() {
		setInterval(function() { cloneReader.saveData(true); }, (FEED_TIME_SAVE * 1000)); 
		setInterval(function() { cloneReader.loadFilters(true); }, (FEED_TIME_RELOAD * 60000));
		setInterval(function() { cloneReader.updateEntriesDateTime(); }, (FEED_TIME_RELOAD * 60000));

//		document.addEventListener('touchstart', function(){}, false);
		
		$('body').css('background', '#EEEEEE');
		$('#header').css( { 'border-bottom': '1px #ccc solid', 'box-shadow': '0 0px 7px #666' });
		

		this.$ulEntries.on({ 'tap' : 
			function(event){
				var $entry = $(event.target);
				cloneReader.selectEntry($entry, false, false);
	 		} 
		});
    
		$(window).scrollStopped(function(){
console.time("t1");	
    		cloneReader.checkScroll();
		    cloneReader.getMoreEntries();
console.timeEnd("t1");	    
		});
		
		this.maximiseUlEntries(this.aFilters.isMaximized, false, false);
		
		$(window).resize(function() {
			cloneReader.resizeWindow();
			if (cloneReader.isMobile != true) {
				cloneReader.scrollToEntry(cloneReader.$ulEntries.find('li.selected'), false);
			}
			cloneReader.maximiseUlEntries(cloneReader.aFilters.isMaximized, false, true);
		});
		
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
						this.maximiseUlEntries(!this.aFilters.isMaximized, true, false);
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
				if ($(event.target).parents('.modal').length != 0) {
					return;
				}
				if ($('.jAlert:visible').length != 0) {
					return;
				}
				
				var $popupForm = cloneReader.$container.find('.popupForm:visible');
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
			cloneReader.maximiseUlEntries(!cloneReader.aFilters.isMaximized, true, false);
		} );		
	},
	
	checkScroll: function() {
		if (this.aFilters.viewType == 'list') {
			return;
		}
		if (this.$ulEntries.find('li.selected').length && $(window).scrollTop() == 0) { 
			return;
		}
		if (this.$ulEntries.is(':animated') == true) {
			return;
		}		
		
		var top 		= this.$ulEntries.offset().top;
		var height	 	= $(window).height() - top - 10;
		var aLi			= this.$ulEntries.find('.entry');
		var scrollTop	= $(window).scrollTop();
		
		
		for (var i=0; i<aLi.length; i++) {
			var $entry 	= $(aLi[i]);
			var offset 	= $entry.find('p:first').offset();
			offset.top	= offset.top - scrollTop;
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
			'url': 		base_url + 'entries/buildCache',
			'async':	true //false
		})		
	},
	
	renderToolbar: function() {
		this.$toolbar.html(' \
			<ul class="nav navbar-nav navbar-left"> \
				<li> \
					<button title="Expand" class="expand"> \
						<i class="icon-exchange"  /> \
						<span class="btnLabel">Expandir</span> \
					</button> \
				</li> \
			</ul> \
			<ul class="nav navbar-nav navbar-right mainToolbar"> \
				<li> \
					<button title="Add feed" class="add" > \
						<i class="icon-plus" /> \
						<span class="btnLabel">Add Feed</span> \
					</button> \
				</li> \
				<li> \
					<div class="btn-group feedSettings" > \
						<button class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Feed settings"> \
							<span> Feed settings </span> \
							<span class="caret" /> \
						</button> \
						<ul class="dropdown-menu popupFeedSettings" /> \
					</div> \
				</li> \
				<li> \
					<div class="btn-group filterSort" > \
						<button class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Sort" > \
							<span/> \
							<span class="caret" /> \
						</button> \
						<ul class="dropdown-menu" > \
							<li class="filterNewestSort"> <a> sort by newest </a> </li> \
							<li class="filterOldestSort"> <a> sort by oldest </a> </li> \
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
							<li class="filterAllItems"> <a> all items </a> </li> \
							<li class="filterOnlyUnread" > <a> <span class="count" /> new items </a> </li> \
						</ul> \
					</div> \
				</li> \
				<li> \
					<div class="btn-group" data-toggle="buttons-radio" > \
						<button class="viewList" title="List view" > \
							<i class="icon-align-justify" /> \
						</button> \
						<button class="viewDetail" title="Detail view" > \
							<i class="icon-th-list" /> \
						</button> \
					</div> \
				</li> \
				<li> \
					<button title="Reload" class="reload" > <i class="icon-refresh" /> \
						<span class="btnLabel">Reload</span> \
					 </button> \
				</li> \
				<li> \
					<div class="btn-group"  > \
						<button title="Prev" class="prev" > <i class="icon-caret-up" /> </button> \
						<button title="Next" class="next" > <i class="icon-caret-down" /> </button> \
					</div> \
				</li> \
			</ul> \
		');
		
		this.$mainToolbar 		= this.$toolbar.find('.mainToolbar');
		this.$popupFeedSettings = this.$toolbar.find('.popupFeedSettings');

		
		this.$toolbar.find('ul button').addClass('btn').addClass('btn-default').addClass('navbar-btn');
		
		this.$toolbar.find('.expand').click(function() { cloneReader.maximiseUlEntries(!cloneReader.aFilters.isMaximized, true, false) } );
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
				cloneReader.showPopupForm('Add new feed', 'Add feed url', function() { cloneReader.addFeed(); }, $(event.target)); 
			}
		);
		this.$mainToolbar.find('.feedSettings').click(function() { cloneReader.showPopupFeedSettings(); });
		
		this.$mainToolbar.find('.filterUnread, .filterSort, .feedSettings').hide();
		this.$mainToolbar.find('.dropdown-toggle').click(
			function(event) {
				cloneReader.hidePopupWindow();
			}
		);
		
/*		
		if (this.isMobile != true) {
			this.$mainToolbar.find('a').tooltip( { placement: 'bottom', container: 'body', delay: { show: 500, hide: 100 }  });
		}*/
	},
	
	loadEntries: function(clear, forceRefresh, aFilters) {
		this.hidePopupWindow();
		
		var lastFilters = $.toJSON(this.aFilters);
		this.aFilters 	= $.extend(this.aFilters, aFilters);
		
		if (this.$ulEntries.children().length == 0) { // Para la primera carga
			forceRefresh = true;
		}
		
		if (clear == true && this.isMobile == true && this.$ulEntries.children().length != 0) { // Si no es la primera carga y es mobile, maximizo al cambiar el filtro
			this.maximiseUlEntries(true, false, false);
		}
				
		if (forceRefresh != true && $.toJSON(this.aFilters) === lastFilters) {
			return;
		}
		if (clear == true) {
			this.aFilters['page'] = 1;
			this.$ulEntries.children().remove();
			
			this.lastScrollTop = 0;
			$('html,body').scrollTop(0);
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
				return $(document).jAlert(response['result']);
			}
			cloneReader.isLastPage 		= (response.result.length < ENTRIES_PAGE_SIZE);
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
//					.addClass('clean')
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
/*		if ($entry.visible( true ) == false) {
			$entry.addClass('clean').children().remove();
			return false;
		}*/
/*		if ($entry.hasClass('clean') == false) {
			return false;
		}*/
		
		$entry.children().remove();		

		var entryId = $entry.data('entryId');
		var entry 	= this.aEntries[entryId];

//		$entry.removeClass('clean');
		
		if (this.aFilters.viewType == 'detail') {
			this.renderDetailEntry($entry, entry);
		}
		else {
			this.renderListEntry($entry, entry);
		}
//cn('renderEntry');		
//cn($entry);		
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
//		$entry.stop().css('opacity', 0).animate( {'opacity': 1}, 'fast');
		
		setTimeout( function() { cloneReader.updateEntryDateTime($entry); } , 0);
		
		return true;
	},
	
	renderDetailEntry: function($entry, entry) {
		var $header = $('<div/>').addClass('header').appendTo($entry);

var aaa = this.$ulEntries.find(' > li ').index($entry);

		$('<a />')
			.addClass('entryTitle')
			.attr('href', entry.entryUrl)
			.css('background-image', 'url(' + base_url + (entry.feedIcon == null ? 'assets/images/default_feed.png' : 'assets/favicons/' + entry.feedIcon) + ')')
			.html(aaa + ' - ' + entry.entryTitle || '&nbsp;')
			.appendTo($header);

		$('<label><i /></label>').addClass('star').appendTo($header);
		$('<span />').addClass('entryDate').appendTo($header);
					
		var $div = $('<div />').html('from <a >' + entry.feedName + '</a>').appendTo($header);
		if (entry.entryAuthor != '') {
			$div.html($div.html() + ' by ' + entry.entryAuthor ).appendTo($header);				
		}	
		$div.find('a').click(
			function() {
				cloneReader.loadEntries(true, false, { 'type': 'feed', 'id': cloneReader.aEntries[$(this).parents('.entry').data('entryId')]['feedId'] });
			}
		);
	
		var $entryContent = $('<div/>'); // TODO: revisar esta parte, chequear que elimine bien los <scripts>
		$entryContent.text(entry.entryContent); //$entryContent[0].innerHTML = entry.entryContent;
		$entryContent.find('script').remove();
		$entryContent.find('iframe').remove();
$entryContent.find('br').remove();

		$('<p/>').html($entryContent.text()).appendTo($entry);

		var $footer = $('<div class="form-actions panel-footer form-inline navbar-form navbar-inner " />').addClass('footer ').appendTo($entry);

		$('<label class="star checkbox" > <i/> </label>').appendTo($footer);
		$('<label class="read checkbox" > <i/> <span> keep unread </span> \
		</label>').appendTo($footer);


		$entry.find('.read, .read i').click(function(event) {
			event.stopPropagation();
			$checkbox = $(event.target);
			if ($checkbox.hasClass('read') == false) {
				$checkbox = $checkbox.parents('.read:first');
			}
			cloneReader.readEntry($checkbox.parents('.entry'), $checkbox.hasClass('selected'));
		});				
						
//		$entry.css('min-height', $entry.height());
//		$entry.css('min-height', 1).css('min-height', $entry.height());
		$entry.find('img').load(
			function(event) {
				var $entry = $(event.target).parents('.entry');
				if ($entry.visible(true) != true) {
					return;
				}
//				$entry.css('min-height', $entry.height());
//				$entry.css('min-height', 1).css('min-height', $entry.height());
	/*			
if (cloneReader.$ulEntries.is(':animated') == true) {				
	cloneReader.scrollToEntry(cloneReader.$ulEntries.find('li.selected'), false);

	cn('img.load!');
}*/
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

		$('<label><i /></label>').addClass('star').appendTo($div);
		$('<span />').addClass('feedName').html($.stripTags(entry.feedName, '')).appendTo($div);
		$('<span />').addClass('entryContent').html($.stripTags(entry.entryContent, ''))
			.appendTo($div)
			.prepend($('<h2 />').html($.stripTags(entry.entryTitle, '')));
		$('<span />').addClass('entryDate').appendTo($div);

		$entry.find('.feedName, .entryContent').click(function(event) {
			var $entry = $(event.target).parents('.entry');
			
			if ($entry.hasClass('expanded') == true) {
				$entry
					.removeClass('expanded')
					.removeClass('selected')
					.find('.detail').remove();
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
	},
	
	renderNotResult: function(loading) {
		if (this.$noResult == null) {
			this.$noResult = $('<li/>').addClass('noResult');
		}
		
		this.$noResult.appendTo(this.$ulEntries).show();
		
		if (loading == true) {
			this.$noResult.html('<div class="alert alert-info"> <i class="icon-spinner icon-spin icon-large"></i> loading ...</div>').addClass('loading');
		}
		else {
			this.$noResult.html('<div class="well well-lg"> no more entries </div>').removeClass('loading');
		}
		
		this.resizeNoResult();
	},

	starEntry: function($entry, value) {
		$entry.find('.star').removeClass('selected');
		$entry.find('.star i').removeAttr('class');
		if (value == true) {
			$entry.find('.star').addClass('selected');
			$entry.find('.star i').addClass('icon-star');
		}
		else {
			$entry.find('.star i').addClass('icon-star-empty');
		}
		$entry.find('.star i').addClass('icon-large');
		
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
			$entry.find('.read i').addClass('icon-check-sign');
		}
		else {
			$entry.find('.read i').addClass('icon-check-empty');
		}
		$entry.find('.read i').addClass('icon-large');
		
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
	
	updateToolBar: function() {
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
		if (!(this.aFilters.type == 'tag' && this.aFilters.id == TAG_STAR)) {
			this.$mainToolbar.find('.filterUnread').show();
		}
		
		this.$mainToolbar.find('.filterSort').show();
	},

	updateMenuCount: function() {
		var count = this.getCountFilter(this.getFilter(this.aFilters));
		if (count > FEED_MAX_COUNT) {
			count = FEED_MAX_COUNT + '+';
		}
		this.$mainToolbar.find('.filterUnread .count').text(count);
		this.$container.find('.filterOnlyUnread .count').text(count);
	},
	
	selectEntry: function($entry, scrollTo, animate) {
//cn('selectEntry');		
//cn($entry);		

		if ($entry.length == 0) { return; }
		if ($entry.hasClass('noResult') == true) { return; }
		if (this.$ulEntries.find('> li.entry.selected:first').is($entry)) { return; }
	
		/*if ($entry.hasClass('clean')) {
			this.renderEntry($entry);
		}*/
		
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

//cn($entry);		

		var top = $entry.offset().top - this.$ulEntries.offset().top - 5;
//cn(top);		

		if (animate == true) { 
			 $('html,body').stop().animate( 
				{  scrollTop: top  }
				,
				$.proxy(
					function($entry, animate) {
// TODO: revisar esta parte; si durante la aminación se lodearon imagenes, la $entry queda mal posicionada...
/*cn($entry);
cn(animate);
cn(this);						*/
//var $entry 	= cloneReader.$ulEntries.find('li.selected');
//						var top 	= $entry.offset().top - this.$ulEntries.offset().top + this.$ulEntries.scrollTop() - 10;
						var top 	= $entry.offset().top - this.$ulEntries.offset().top +  $('body').scrollTop() - 10;
						if (top != /*this.$ulEntries*/  $('body').scrollTop()) {
/*cn('aaa');
cn(animate);		
cn(top);	
cn(cloneReader.$ulEntries.scrollTop());*/
//							this.scrollToEntry($entry, false);
//cloneReader.$ulEntries.stop().scrollTop(top);
						}
					}
				, this, $entry, animate) 
			);
		}
		else {
			$('html,body').stop().scrollTop(top);
		}
	},
	
	goToEntry: function(next) {
		this.hideMobileNavbar();
		
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
		$.ajax({ url: base_url + 'entries/selectFilters' })
		.done($.proxy(
			function(reload, response) {
				if (response['code'] != true) {
					return $(document).jAlert(response['result']);
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
			$('.tooltip').remove(); 
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
			
		if (this.isMobile == false) {
			$filter.find('a').tooltip({ placement: 'bottom', container: 'body', delay: { show: 500, hide: 100 }  });
		}


		this.renderCounts(filter);

		if (filter.icon != null) {
			$filter.find('.icon').css('background-image', 'url(' + filter.icon + ')');
		}
		
		if (filter.childs != null) {
			$filter.append('<ul />').find('.icon').addClass('arrow');
			$filter.find('.icon')
				.addClass('icon-caret-right icon-large')
				.click(
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
	
	isVisible: function(filter, parentIsVisible) { // TODO: renombrar a filterIsVisible
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
		var tmp = this.indexFilters[filter.type][filter.id];
		if (tmp != null) {
			return tmp;
		}	
		return this.indexFilters['tag'][TAG_ALL];	
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
		
		$arrow.removeClass('icon-caret-right').removeClass('icon-caret-down');

		if (value != true) {
			$arrow.addClass('icon-caret-right');
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

		$arrow.addClass('icon-caret-down');
		$ul.stop().show('fast', function() { 
			$(this).show();
		});
		
		this.getFilter(this.aFilters).$filter.addClass('selected');
	},
	
	maximiseUlEntries: function(value, animate, isResize) {
		this.aFilters.isMaximized = value;
		
		if (isResize == false) {
			this.hideMobileNavbar();
		}

		var speed = 100;
		
		if (this.isMobile == true) {
			if (value == true) {
				this.$ulEntries.show();	
				if (isResize != true) {
					$('html,body').scrollTop(this.lastScrollTop);			
					this.$ulFilters.css('top', $(window).scrollTop());
				}				
				this.$ulFilters.stop().animate(
					{ 'left': '-100%' },
					{
						duration: speed ,
						complete: function() {
							cloneReader.$ulFilters.hide();
							if (isResize != true) {
								$('html,body').scrollTop(cloneReader.lastScrollTop);
							}
						}
					}
				);
			}
			else {
				this.$ulFilters.show();
				this.lastScrollTop = $(window).scrollTop();
				$('html,body').scrollTop(this.lastScrollTop);			
				this.$ulFilters.css('top', $(window).scrollTop());
				this.$ulFilters.stop().animate(
					{ 'left': '0'  },
					{
						duration: speed ,
						complete: function() {
							cloneReader.$ulEntries.hide();
							cloneReader.$ulFilters.css('top', 0);	
						}
					}
				);
			}
			return;
		}		
		
		var left = 0;
		if (value == false) {
			left = this.$ulFilters.outerWidth();
		}


		this.$ulFilters.show().css({'left': 0, 'top': 101 }); // TODO: desharckodear!
		this.$ulEntries.show();

// TODO: revisar		
//		this.updateUserFilters();
		
		if (animate == true) {
			this.$ulEntries.stop().animate(
				{ 'margin-left': left }, 
				{
					duration: speed ,
				 	complete: function() {
						cloneReader.scrollToEntry(cloneReader.$ulEntries.find('li.selected'), false);
						if (cloneReader.aFilters.isMaximized == true) {
							cloneReader.$ulFilters.hide();
						}
				}
			});
		}
		else {
			this.$ulEntries.stop().css(	{ 'margin-left': left } );
			if (this.aFilters.isMaximized == true) {
				this.$ulFilters.hide();
			}
		}				
	},

	addFeed: function() {
		var feedUrl = this.$popupForm.find('input').val();
		if (feedUrl == '') {
			return this.$popupForm.find('input').jAlert('enter a url');
		}
		if ($.validateUrl(feedUrl) == false) {
			return this.$popupForm.find('input').jAlert('enter a valid url');
		}

		this.hidePopupWindow();

		$.ajax({
			'url': 		base_url + 'entries/addFeed',
			'data': 	{  'feedUrl': feedUrl },
			'type':	 	'post',
		})
		.done(function(response) {
			if (response['code'] != true) {
				return $(document).jAlert(response['result']);
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
	
	saveData: function(async){
/*		if (this.$ulEntries.getNiceScroll().length != 0) {
			if (this.$ulEntries.getNiceScroll()[0].scrollrunning == true) {
				return;
			}
		}*/
		
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
				return $(document).jAlert(response['result']);
			}
		});			
		
		this.aUserEntries 	= {};
		this.aUserTags		= {};
	},

	addTag: function() {
		var tagName = this.$popupForm.find('input').val();
		if (tagName.trim() == '') {
			return this.$popupForm.find('input').jAlert('enter a tag name');
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
				return $(document).jAlert(response['result']);
			}
			
			cloneReader.loadEntries(true, true, { 'type': 'tag', 'id': response['result']['tagId'] }); 
			cloneReader.loadFilters(true);
		});				
	},

	saveUserFeedTag: function(feedId, tagId, append) {
		this.hidePopupWindow();
		this.hideMobileNavbar();

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
				return $(document).jAlert(response['result']);
			}
			cloneReader.saveData(false);
			cloneReader.loadFilters(true);
		});
	},
	
	markAsReadFeed: function(feedId) {
		this.hidePopupWindow();

		$(document).jAlert( {
			'msg': 			'Mark all as read?',
			'isConfirm': 	true,
			'callback': 	$.proxy(
				function() {
					$.ajax({
						'type':	 	'post',
						'url': 		base_url + 'entries/markAsReadFeed',
						'data': 	{ 'feedId':	feedId 	},
					})
					.done(function(response) {
						if (response['code'] != true) {
							return $(document).jAlert(response['result']);
						}
						cloneReader.aEntries = {}
						cloneReader.loadEntries(true, true, {});
						cloneReader.loadFilters(true);
					});
				}
			, this)
		});	
	},
	
	unsubscribeFeed: function(feedId) {
		this.hidePopupWindow();
		
		$(document).jAlert( {
			'msg': 			'Unsubscribe feed?',
			'isConfirm': 	true,
			'callback': 	$.proxy(		
				function () {
					$.ajax({
						'type':	 	'post',
						'url': 		base_url + 'entries/unsubscribeFeed',
						'data': 	{ 'feedId':	feedId 	},
					})
					.done(function(response) {
						if (response['code'] != true) {
							return $(document).jAlert(response['result']);
						}
						cloneReader.loadEntries(true, true, { 'type': 'tag', 'id': TAG_ALL });
						cloneReader.loadFilters(true);
					});
				}
			, this)
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
				return $(document).jAlert(response['result']);
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
			{ 'html': 'Mark all as read', 	'callback': function() { cloneReader.markAsReadFeed(cloneReader.aFilters.id); } },
			{ 'html': 'Unsubscribe', 		'callback': function() { cloneReader.unsubscribeFeed(cloneReader.aFilters.id);  } },
			{ 'html': 'New tag', 			'class': 'newTag', 'callback': 
				function(event) {
					event.stopPropagation(); 
					cloneReader.showPopupForm('Add new tag', 'enter tag name', function() { cloneReader.addTag(); }, cloneReader.$mainToolbar.find('.feedSettings'));
				}
			},
			{ 'class': 'divider' }
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
				aItems.push( { 'html': (hasTag == true ? '<i class="icon-ok icon-fixed-width" />' : '') + tag.tagName, 'data': { 'feedId': feedId, 'tagId': tag.tagId} , 'callback': function() {  
					var $filter = $(this); 
					cloneReader.saveUserFeedTag($filter.data('feedId'), $filter.data('tagId'), $filter.find('i.icon-ok').length == 0 ); 
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
							<button class="btn btn-primary"> <i class="icon-ok" /> </button> \
						</span> \
					</div> \
				</form>\
			');
			
			this.$popupForm.find('input').keyup(function(event) {
				event.stopPropagation();
			});
		}
		
		this.hidePopupWindow();
		this.hideMobileNavbar();
		
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
		var left 	= $element.offset().left - this.$container.offset().left;
		
		this.$popupForm
			.css({ 'top': top,  'left': left, 'position': 'fixed' })
			.appendTo(this.$container)
			.stop()
			.fadeIn();
			
		if (this.isMobile == false) {
			this.$popupForm.find('input').focus();
		}
	},
	
	resizeWindow: function() {
		this.getIsMobile();

		if (this.isMobile == true) {
			this.$mainToolbar.appendTo($('#header .navbar-collapse'));
			this.$toolbar.hide();
			$('#header .logo').removeAttr('href');
		}
		else {
			this.$mainToolbar.appendTo( this.$toolbar );
			this.$toolbar.show();
			$('#header .logo').attr('href', base_url);
			this.hidePopupWindow();
		}

		$('.content > div > h1').hide();
		$('.content').css( { 'max-width': '100%' });
		$('#header').addClass('navbar-fixed-top').css( { 'max-width': '100%' } );
		$('.menu').remove();
		

		var height = $(window).height() - (this.isMobile == true ? 50 : 100); //FIXME: harckodeta! // - this.$ulEntries.offset().top - $('#footer').outerHeight(true); // TODO: revisar el -1
		this.$ulFilters.height(height);
		$('.navbar-ex1-collapse').css('max-height', height);
		
		this.$container.css('min-height', this.$ulFilters.height());
		
		this.resizeNoResult();
	},
	
	resizeNoResult: function() {
		if (this.$noResult == null) { return; }
		
		this.$noResult
			.css('min-height', 
				$(window).height() 
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
		this.$container.find('.popupForm').hide();
		this.$mainToolbar.find('.open').removeClass('open');
	},
	
	hideMobileNavbar: function() {
		if (this.isMobile == true) {
			if ($('.navbar-ex1-collapse').is(':visible') == true) {
				$('.navbar-ex1-collapse').collapse('hide');
			}
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
		return datetime.format('LLL');
	},
	
	getIsMobile: function() {
		$('body').css('overflow', 'hidden');
		this.isMobile = $(window).width() < 768;		
		$('body').css('overflow', 'auto');
cn(this.isMobile);		
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
