$.Feedback = {
	init: function() { 

//		var options	 	= $('#frmCommentEdit').jForm('options');
//		options.callback = $.proxy(function(response) { this.onSaveFeedback(); }, this);
		
//			'frmId'		=> 'frmCommentEdit',
//			'callback' 	=> 'function(response) { $.Feedback.onSaveFeedback(); };',
		
		
	},
	
	onSaveFeedback: function() {
		alert('aasdfa sdf');
	}
};

$(document).ready(function() {
	$.Feedback.init();
});	
