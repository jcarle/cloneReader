<script src="https://apis.google.com/js/client.js"></script>


<script type="text/javascript">
var clientID 	= SERVER_DATA.googleApi;
var scopes 		= 'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email';
	
$(window).bind('load', function() {
	checkAuth();
});

function checkAuth() {
	gapi.auth.authorize({
		'client_id': clientID,
		'scope': scopes,
		'immediate': true,
		'response_type': 'token id_token'
	}, handleAuthResult);
}

function handleAuthResult(authResult) {
	if (authResult && !authResult.error) { // Si el usuario es legítimo
		makeApiCall();
	}
	else {
		myLogin();
	}
}



function myLogin()
{
 gapi.auth.authorize({'client_id': clientID,
  'scope': scopes,
  'immediate': false,
  'response_type': 'token id_token'
 }, handleAuthResult);
   return false;
}


function makeApiCall() {
	gapi.client.load('oauth2', 'v2', function() { // Cargamos la librería de autentificación
		var request = gapi.client.oauth2.userinfo.get();  // Solicitamos info del usuario
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
//					$.showWaiting(true);
//					location.href = base_url;
				})				
				
				//token.access_token = token.id_token;
				//gapi.auth.setToken(token);
				//accessMail = response.email;  // Guardamos el email para usarlo en la siguiente función
				//gapi.client.load('blogger', 'v3', runApi); // Cargamos el API de Blogger v3
			}
			else {
				myLogin();
			}
		});
	}); 
}

</script>
