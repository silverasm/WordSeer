<?php
/** 
Utilities and functions for getting a specific set of sentences
*/

/** Returns a the sentence with the given number in the given narrative
*/
function getUnit($unit, $narrative, $number){
	$tableName = "sentence";
	$field = "sentence";
	$index = "id";
	if($unit =="paragraphs"){
		$tableName = "paragraph";
		$field = "text";
		$index = "start_sentence";
	}
	$query = "SELECT * from ".$tableName." where number = ".$number." AND narrative_id =".$narrative.";";
	$results =  mysql_query($query);
	$row =  mysql_fetch_array($results);
	$results  = array();
	$sent = $row[$field];
	$sent = str_replace("-LRB-", "(", $sent);
	$sent = str_replace("-RRB-", ")", $sent);
	$results['sentence'] = utf8_encode($sent);
	$results['narrative'] = $row['narrative_id'];
	$results['sentence_id'] = $row[$index];
	return $results;
}

//if used as a script
if($_GET['narrative']){
	include 'dbsetup.php';
	include 'util.php';
	$narrative = $_GET['narrative'];
	$unit = $_GET['unit'];
	$sentenceNumbers = explode(" ", $_GET['numbers']);
	$sentences = array();
	foreach($sentenceNumbers as $number){
		$sent = getUnit($unit, $narrative, $number);
		array_push($sentences, $sent);
	}
	echo json_encode($sentences);
}
?>
