<?php
/**
user.php
Called by signIn in user.js in service of 
all main application web pages, to handle user authentication.
Returns a JSON object containing user data or information about
errors.
**/
include 'dbsetup.php';
include 'util.php';

// toggle this to allow or disallow new users to sign up.
$allowSignups = false;

function signUp($username, $password){
	$returnData  = array('error'=>'no-error');
	if($allowSignups){
	$query = "SELECT * from user where username = '".$username."';";
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 0){
		$query = "INSERT into user (username, password) VALUES('".$username."', '".$password."')";
		$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $query . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		$returnData['username'] = $username;
	}else{
		$returnData['error'] = 'already-exists';
	}
	}else{
		$returnData = array('error'=>'no-more');
	}
	echo json_encode($returnData);
}

function signIn($username, $password){
	$returnData  = array('error'=>'no-error');
	$query = "SELECT * from user where username = '".$username."';";
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 0){
		$returnData['error'] = 'no-user';
	}else{
		$query = "SELECT * from user where username = '".$username."' AND password = '".$password."';";
		$result = mysql_query($query);
		if(mysql_num_rows($result) == 0){
			$returnData['error'] = 'wrong-password';
		}else{
			$returnData['username'] = $username;
		}
	}
	echo json_encode($returnData);
}

if($_GET['type'] == 'signup'){
	signUp($_GET['username'], $_GET['password']);
	}else if ($_GET['type'] == 'sign-in'){
		signIn($_GET['username'], $_GET['password']);
	}

	?>