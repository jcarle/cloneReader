window.fbAsyncInit = function() {
	FB.init({
		appId: 			SERVER_DATA.fbApi, // App ID
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
						'url': 		base_url + 'login/loginRemote',
						'type': 	'post',
						'data': 	{
							'provider':			'facebook',
							'remoteUserId':		response.id,
							'userLastName': 	response.last_name,
							'userFirstName': 	response.first_name,
							'userEmail': 		response.email,
						},
						'success': 	function ( data ) {
							$.showWaiting(true);
							location.href = base_url;
						}
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
