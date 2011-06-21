<?php
/**************************************
makeparagraphtext.php
I only ran this once, to make paragraphs searchable as a unit.
**************************************/
include '../dbsetup.php';

//makeParagraphText();
function makeParagraphText(){
	$query = "SELECT * from paragraph;";
	$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
		<br/> Query: " . $query . "
		<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	while($row = mysql_fetch_array($result)){
		$query = "SELECT sentence from sentence WHERE paragraph_id = ".$row['id'].";";
		$sentences = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $query . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		$paragraph = array();
		while($sentence = mysql_fetch_array($sentences)){
			array_push($paragraph, $sentence['sentence']);
		}
		$paragraph = join(" ", $paragraph);
		$query = "UPDATE paragraph  SET text = '".mysql_real_escape_string($paragraph)."' WHERE id = ".$row['id'].";";
		mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $query . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		echo $row['id'],"\n";
	}
}

//updateParagraphCount();
function updateParagraphCount(){
	$query = "SELECT max(number) as paragraph_count, narrative_id from paragraph GROUP BY narrative_id;";
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)){
		$query = "UPDATE narrative SET paragraph_count = ".$row['paragraph_count']." WHERE id = ".$row['narrative_id'].";";
		mysql_query($query);
		echo $row['narrative_id'],"\n";
	}
	
}

setStartSentenceID();
function setStartSentenceID(){
	$query = "SELECT min(id) as start, paragraph_id from sentence GROUP BY paragraph_id;";
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)){
		$query = "UPDATE paragraph SET start_sentence = ".$row['start']." WHERE id =".$row['paragraph_id'].";";
		mysql_query($query);
		echo $row['paragraph_id'],"\n";
	}
}

?>