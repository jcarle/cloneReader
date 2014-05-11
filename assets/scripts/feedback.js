$.Feedback = {
	init: function() { 	},
	
	onSaveFeedback: function(response) {
		if ($.hasAjaxDefaultAction(response) == true) { return; }

		var $alert = $('<div class="alert alert-success"> <strong>' + crLang.line('Thanks for contacting us') + ' </strong> </div>');
		$('#frmCommentEdit')
			.hide()
			.parent().append($alert);
			
		$alert.hide().fadeIn();
	}
};
