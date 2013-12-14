$.Feedback = {
	init: function() { 	},
	
	onSaveFeedback: function() {
		var $alert = $('<div class="alert alert-success"> <strong>' + _msg['Thanks for contacting us'] + ' </strong> </div>');
		$('#frmCommentEdit')
			.hide()
			.parent().append($alert);
			
		$alert.hide().fadeIn();
	}
};
