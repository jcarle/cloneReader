window.fbAsyncInit = function() {
	FB.init({
		appId: 			581547605212584, // App ID
		channelUrl: 	location.host,
		status: 		true, // check login status
		cookie:			true, // enable cookies to allow the server to access the session
		xfbml: 			true,  // parse XFBML,
		oauth: 			true

	});
};

function facebookLogin() {
	FB.login(
		function(response){
			if (response.status === 'connected') { // Esta conectado
				FB.api('/me', function(response) {
					$.ajax({
						url: 	base_url + 'login/loginFB',
						type: 	'post',
						data: 	{
							'oauth_uid': 		response.id,
							'userLastName': 	response.last_name,
							'userFirstsName': 	response.first_name,
							'userEmail': 		response.email,
						}
					})
					.done(function ( data ) {
						$.showWaiting(true);
						location.href = base_url;
					})
				});
			}
		}, 
		{ scope: 'email' }
	);
}


(function(d){
	var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement('script'); js.id = id; js.async = true;
	js.src = "//connect.facebook.net/en_US/all.js";
	ref.parentNode.insertBefore(js, ref);
}(document));