crMain = {
	aPages: [],
	
	init: function() {
		$('.navbar-brand.logo').attr('href', base_url + 'app#' + PAGE_HOME);
		
		$.ajax({
			'url': 		base_url + 'app/selectMenuAndTranslations',
			'async':	false,
			'success': 
				function(result) {
					_msg = result['result']['aLangs']; // TODO: meter _msg en algun lado, que no sea global
		
					var aMenu = result['result']['aMenu'];
					for (var menuName in aMenu) {
						var $menu = $(aMenu[menuName]['parent']);
						crMenu.renderMenu(aMenu[menuName]['items'], aMenu[menuName]['className'], $menu);
					}
				}
		});

		$(window).on('hashchange',function(){
			var controller = location.hash.slice(1);
			if (controller.trim() == '') {
				controller = PAGE_HOME;
			}
			crMain.loadUrl(controller);
		});		
		

		var hash = PAGE_HOME;
		if (location.hash.slice(1) != '') {
			hash = location.hash.slice(1);
		}		
		if (hash != location.hash.slice(1)) {
			$.goToHashUrl(hash);
		}
		else {
			crMain.loadUrl(hash);
		}
	},
	
	loadUrl: function(controller) {
		var pageName = this.getPageName();		
		if (this.aPages[pageName] == null) {
			this.aPages[pageName] = $('<div class="page ' + pageName + '"/>').appendTo($('.container'));
		}
		$('.container .page').hide();
		this.aPages[pageName].stop().show();

		if (this.ajax) {
			this.ajax.abort();
			this.ajax = null;
		}
				
		this.ajax = $.ajax({
			'url': 		base_url + controller,
			'data': 	{ 'appType': 'ajax' },
			'async':	true,
			'success': 
				function(response) {
					if (response['code'] != true) {
						return $(document).crAlert(response['result']);
					}
					
					var data 	= response['result'];
					var $page 	= crMain.aPages[pageName];
					
					$page.children().remove();
					
					crMain.renderPageTitle(data, $page);
					
					switch (data['js']) {
						case 'crList':
							data['$parentNode'] = $(crMain.aPages[pageName]);
							$(null).crList(data);
							break;
						case 'crForm':
							var $form = crMain.renderCrForm(data, $page);
							$form.crForm(data);
							break;
					}
				}
		})		
	},
	
	
	renderPageTitle: function(data, $page) {
		$('title').text(data['title'] + ' | ' + siteName);
		
		if (data['breadcrumb'] != null) {
			$('<ol class="breadcrumb">').appendTo($page);
// TODO: implementar!			
			/*
			for ($breadcrumb as $link) {
				if (element('active', $link) == true) {
					echo '<li class="active"> '.$link['text'].'</li>';
				}
				else {
					echo '<li><a href="'.$link['href'].'">'.$link['text'].'</a></li>';
				} 
			}*/
		}

		if (data['showTitle'] == null) {
			data['showTitle'] = true;
		}
		if (data['showTitle'] == true) {
			$pageTitle = $('\
				<div class="pageTitle">\
					<h2> <small> </small></h2>\
				</div>\
			').appendTo($page);
			
			$pageTitle.find('h2').text(data['title']);
		}

	},
	
	renderCrForm: function(data, $page) {
// TODO: revisar si en modo webPage hace falta pasar el param urlList por decodeURIComponent		
		var params 		= $.getUrlVars();
		var urlList 	= $.base64Decode(decodeURIComponent(params['urlList']));
		var buttons 	= [
			'<button type="button" class="btn btn-default" onclick="$.goToHashUrl(\'' + urlList + '\');"><i class="icon-arrow-left"></i> ' + _msg['Back'] + ' </button> ',
			'<button type="button" class="btn btn-danger"><i class="icon-trash"></i> ' + _msg['Delete'] + ' </button>',
			'<button type="submit" class="btn btn-primary" disabled="disabled"><i class="icon-save"></i> ' + _msg['Save'] + ' </button> '	
		];
		if (data['urlDelete'] == null) {
			delete buttons[1];
		}

		data = $.extend({
			'action': 	this.getFormAction(), 
			'frmId': 	'frmId',
			'buttons': 	buttons
		}, data);
					

		var $form = $('<form action="' + data['action'] + '" />')
			.attr('id', data['frmId'])
			.addClass('panel panel-default crForm form-horizontal')
			.attr('role', 'form')
			.appendTo($page);

		var $div = $('<div class="panel-body" />').appendTo($form); 

// TODO: renderear los fields con js, para transmitir menos datos
		for(var i=0; i<data['aFields'].length; i++)  {
			$(data['aFields'][i]).appendTo($div);
		}

		if (data['buttons'].length != 0) {
			$div = $('<div class="form-actions panel-footer" > ').appendTo($form);
			for (var i=0; i<data['buttons'].length; i++) {
				$div
					.append($(data['buttons'][i]))
					.append(' ');
			}
		}

/* 
// TODO: revisar la galeria
$fieldGallery = getCrFieldGallery($form);
if ($fieldGallery != null) {
	$this->load->view('includes/uploadfile', array(
		'fileupload' => array ( 
			'entityName' 	=> $fieldGallery['entityName'],
			'entityId'		=> $fieldGallery['entityId']
		) 
	));
}*/

		return $form;
	},

	getFormAction: function() {
		var pageName = location.hash.slice(1);		
		if (pageName.indexOf('?') != -1){
			pageName = pageName.substr(0, pageName.indexOf('?'));
		}
		return pageName;
	},
	
	getPageName: function() {
		var pageName = location.hash.slice(1);		
		if (pageName.indexOf('?') != -1){
			pageName = pageName.substr(0, pageName.indexOf('?'));
		}
		var position = pageName.indexOf('/edit/');
		if (position != -1){
			pageName = pageName.substr(0, position + 5);
		}
		return pageName;
	}
};



$(document).ready( function() { 
	crMain.init(); 
});
