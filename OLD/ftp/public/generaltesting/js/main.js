(function(){
	// see the index.html file on how to configure FB.init
	// the most important bit is the appId AND that the URL that hosts
	// the page fits to the application domain set in the app settings
	// at facebook
	
	// little helper
	var output = undefined,
	print = function() {
		if(!output)
			output = $('#output').get(0);
		var args = [].slice.call(arguments);
		for (var i = 0; i<args.length; i++)
			output.appendChild(document.createTextNode(args[i] + '\n'));
	},
	// this will be populated with the successful response of FB.login
	// but its not really necessary to do so
	loginResponse = undefined,
	isLoggedIn = function() {
		return loginResponse !== undefined;
	},
	// trigger a login
	facebookLogin = function(event) {
		event.preventDefault();
		
		if(isLoggedIn(loginResponse)) {
			print('Logged in already.');
			return;
		}
		
		FB.login(function(response) {
			if (response.authResponse) {
				loginResponse = response;
				print('Welcome!  Fetching your information....');
				//this is already an API call!
				FB.api('/me', function(response) {
					print('Good to see you, ' + response.name + '.');
				});
			} else {
				print('User cancelled login or did not fully authorize.');
			}
		},
		/**
		 * we want these permissions for the app
 		 */
		{scope: 'email, user_photos, publish_stream, user_likes'});
	},
	// share via the API
	// see: http://developers.facebook.com/docs/reference/api/#publishing
	facebookShare = function(event) {
		event.preventDefault();
		if(!isLoggedIn()) {
			print('Log in first, please.');
			return;
		}
		/**
		 * setting the commented out fields would override what the og-tags
		 * provide at the page of the link.
		 * 
		 * "message" is not allowed to be filled with sth. The user did
		 * NOT write herself!
		 */
		var message = {
			link : 'http://www.facebook.com'
		};
		
		FB.api('/me/feed', 'POST', message, function(response) {
			if(response.error)
				print(response.error.message);
			else
				print('A post had just been published into the stream on your wall.');
		});
	},
	facebookLogout = function(event) {
		event.preventDefault();
		FB.logout(function(){
			print('logged out now');
			loginResponse = undefined;
		});
	}
	
	// on document ready
	$(function(){
		print('Hello World')
		$('a.login').on('click', facebookLogin);
		$('a.share').on('click', facebookShare);
		$('a.logout').on('click', facebookLogout);
	})
})()
