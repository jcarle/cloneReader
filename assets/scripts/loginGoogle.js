(function() {
	var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/client:plusone.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();

function googleLogin(response) {
	if (!response['access_token']) {
		return;
	}
	
	$.ajax({
		url: 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' + response['access_token'],
		data: null,
		dataType: "jsonp",
		success: function(response) {
			$.ajax({
				url: 	base_url + 'login/loginRemote',
				type: 	'post',
				data: 	{
					'provider': 		'google',
					'remoteUserId': 	response.id,
					'userLastName': 	response.family_name,
					'userFirstName': 	response.given_name,
					'userEmail': 		response.email,
				}
			})
			.done(function ( data ) {
				$.showWaiting(true);
				location.href = base_url;
			})
		}
	});
}







function checkGoogleAuth(immediate) {
	gapi.auth.authorize({
		'client_id': 		SERVER_DATA.googleApi,
		'scope': 			'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email',
		'immediate': 		immediate,
		'response_type': 	'token id_token'
	}, handleGoogleAuth);
}

function handleGoogleAuth(authResult) {
	if (authResult && !authResult.error) {
		makeApiCall();
	}
	else {
		checkGoogleAuth(false);
	}
}


function makeApiCall() {
	gapi.client.load('oauth2', 'v2', function() {
		var request = gapi.client.oauth2.userinfo.get();
		request.execute(function (response) {
			if (!response.code) { // Si code == undefined : datos ok
cn(response);				
				$.ajax({
					url: 	base_url + 'login/loginRemote',
					type: 	'post',
					data: 	{
						'provider': 		'google',
						'remoteUserId': 	response.id,
						'userLastName': 	response.family_name,
						'userFirstName': 	response.given_name,
						'userEmail': 		response.email,
					}
				})
				.done(function ( data ) {
					$.showWaiting(true);
					location.href = base_url;
				})
			}
			else {
				checkGoogleAuth(false);
			}
		});
	}); 
}