/** Check if a user is signed in 
**/
function isSignedIn(){
	if (typeof(localStorage) == 'undefined' ) {
		alert('Your browser does not support this feature, try upgrading.');
	}
	else{
		if(localStorage["username"]) return true;
	}
	return false;
}

/** Update the menu bar: sign a user in if they were already signed in from a previous
session **/
function signUserIn(){
	if(isSignedIn()){
		$("#user").html(localStorage["username"]);
		$("#user").after('<li class="menu" id="logout">(logout)</li>');
		$("#logout").click(signOut);
	}else{
		$("#user").html("Sign in");
		$("#logout").remove();
		$("#user").click(function(){signIn("sign-in");});
	}
}

/** Sign out **/
function signOut(){
	localStorage.removeItem("username");
	signUserIn(); //update menu bar
}

/** Show a dialog to sign a user up, or sign a user in **/
function signIn(type){
	html = '<div class="dialog triangle-border top" id="user-forms"><img class="button close" src="img/close.png">';
	if(!type){ //sign up for an account
		html += '<form id="signup" class="user-forms">';
		html += '<label> Pick a username </label>';
		html += '<input name="username">';
		html += '<label> Pick a password </label>';
		html += '<input type="password" name="password">';
		html += '<label> Confirm password </label>';
		html += '<input type="password" name="confirm-password">';
		html += '<input type="submit" value="OK">';
		html += '<a class="sign-in"> Already have a username? </a>';
		html +='</form>';
	}else if(type=="sign-in"){
		html += '<form id="sign-in" class="user-forms">';
		html += '<label> Username </label>';
		html += '<input name="username">';
		html += '<label> Password </label>';
		html += '<input type="password" name="password">';
		html += '<input type="submit" value="OK">';
		html += '<a class="sign-up"> Don\'t have a username? </a>';
		html += '</form>'
	}
	html +='</div>';
	$("#user-forms").remove();
	$('#wrapper').prepend(html);
	$("#user-forms").css("left", $("#user").offset()["left"]-40);
	$("a.sign-in").click(function(){signIn("sign-in")});
	$("a.sign-up").click(function(){signIn()});
	$("#signup").submit(function(event){
		event.preventDefault();
		if($('input[name="password"]').val() != $('input[name="password"]').val().replace(/\W/g, "")){
			alert("Please only use alphanumeric characters");
		}else if($('input[name="password"]').val().replace(/\W/g, "")!=$('input[name="confirm-password"]').val().replace(/\W/g, "")){
			alert("Passwords do not match.");
		}else if($('input[name="username"]').val().replace(/\W/g, "").length < 3){
			alert("Please pick a longer username");
		}else if($('input[name="password"]').val().replace(/\W/g, "").length < 3){
			alert("Please pick a longer password");
		}else{
			$.getJSON("src/php/user.php", {
				username:$('input[name="username"]').val().toLowerCase().replace(/\W/g, ""),
				password:$('input[name="password"]').val().toLowerCase().replace(/\W/g, ""),
				type:'signup',
				}, processUserData)	
			}
		}
	);	
	$("#sign-in").submit(function(event){
		event.preventDefault();
		$.getJSON("src/php/user.php", {
			username:$('input[name="username"]').val().toLowerCase().replace(/\W/g, ""),
			password:$('input[name="password"]').val().toLowerCase().replace(/\W/g, ""),
			type:'sign-in',
			}, processUserData)	
		}
	);
	$('.close').click(closeParent);
	$('#user-forms').show();
}

/** Check if the sign in or sign up has been successful **/
function processUserData(data){
	if(data.error == "no-error"){
		$("#user-forms").remove();
		if (typeof(localStorage) == 'undefined' ) {
			alert('Your browser does not support this feature, try upgrading.');
		}
		else{
			localStorage["username"] =  data.username;
			signUserIn();
		}
	}else{
		if(data.error == "already-exists"){
			alert("Signup failed: an account with that username already exists.");
		}else if(data.error == "wrong-password"){
			alert("You entered the wrong password.");
		}else if(data.error == "no-user"){
			alert("Sign in failed: no such username.");
		}else if(data.error == "stranger"){
			alert("You are not on the list of authorized users.");
		}else if(data.error = 'no-more'){
			alert("All authorized users are already signed up.")
		}
	}
}
