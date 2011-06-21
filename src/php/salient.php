<?php
/****************************************************************
salient.php
called by displaySalientWords() in salient.js, in service of 
view.js, in service of view.php.

Utilities for displaying a list of words with the highest TF-IDF's
****************************************************************/
include 'dbsetup.php';
include 'util.php';

$paragraph = $_GET['paragraph'];
$number = $_GET['number'];
if($paragraph){
	getSalientWords($paragraph, $number);
}

function getSalientWords($paragraph, $number){
	$query = "SELECT * from paragraph_tf WHERE paragraph_id = ".$paragraph." ORDER BY tf_idf DESC LIMIT ".$number.";";
	$data = array("paragraph"=>$paragraph, "words" => array());
	$results = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
		<br/> Query: " . $query . "
		<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	while($row = mysql_fetch_array($results)){
		array_push($data['words'], $row['word']);
	}
	echo json_encode($data);
}

?>