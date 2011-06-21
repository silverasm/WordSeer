<?php
/******************************************************************
	editannotations.php
	Called by deleteTag, deleteNote, editNote, deleteNote, and
	deleteAnnotation  in annotate.js in service of view.js
	in service of view.php
	Contains utilities for deleting and editing annotations
*******************************************************************/
include 'dbsetup.php';
include 'util.php';

if($_GET['event']=='delete-tag'){
	deleteTag($_GET['tag'], $_GET['highlight']);
}
else if($_GET['event'] == 'delete-note'){
	deleteNote($_GET['note'], $_GET['highlight']);
}
else if($_GET['event'] == 'edit-note'){
	editNote($_GET['note'], mysql_real_escape_string($_GET['text']));
}
else if($_GET['event'] == 'add-note'){
	addNote(mysql_real_escape_string($_GET['text']), $_GET['highlight'], $_GET['user']);
}
else if($_GET['event'] == 'add-tags'){
	addTags(mysql_real_escape_string($_GET['tags']), $_GET['highlight'], $_GET['user']);
}
else if($_GET['event'] == 'delete-annotation'){
	deleteAnnotation($_GET['highlight']);
}
/** delete a tag **/
function deleteTag($tagID, $highlightID){
	$query = "DELETE from highlight_xref_tag WHERE tag_id =".$tagID." AND highlight_id = ".$highlightID.";";
	$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
		<br/> Query: " . $query . "
		<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	echo json_encode(array('error'=>'no-error'));
}

/** delete a note **/
function deleteNote($noteID, $highlightID){
	//delete cross reference
	$query = "DELETE from highlight_xref_note WHERE note_id =".$noteID." AND highlight_id = ".$highlightID.";";
	$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
		<br/> Query: " . $query . "
		<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	if($result){
		// delete note
		$query = "DELETE from note where id = ".$noteID.";";
		$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $query . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		echo json_encode(array('error'=>'no-error'));
	}
}

/** edit a note **/
function editNote($noteID, $text){
	$query = "UPDATE note SET text = '".$text."' where id = ".$noteID.";";
	$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
		<br/> Query: " . $query . "
		<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	echo json_encode(array('error'=>'no-error'));
}

/** add a note **/
function addNote($text, $highlightID, $user){
	$query = "INSERT into note (text, user) VALUES('".$text."', '".$user."');";
	$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
		<br/> Query: " . $query . "
		<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	if($result){
		$query = "SELECT id from note where text = '".$text."' AND user = '".$user."';";
		$result = mysql_query($query)  or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $query . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		$note = mysql_fetch_array($result);
		$noteID = $note['id'];
		$query = "SELECT narrative_id from highlight where id = ".$highlightID.";";
		$result = mysql_query($query)  or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $query . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		$narrative = mysql_fetch_array($result);
		$narrativeID = $narrative['narrative_id'];
		$query = "INSERT into highlight_xref_note (highlight_id, note_id, narrative) VALUES(".$highlightID.", ".$noteID.", ".$narrativeID.");";
		$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $query . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		echo json_encode(array('error'=>'no-error'));	
	}
}

/** add tags **/
function addTags($tags, $highlightID, $user){
	foreach(split(",", $tags) as $tag){
		$query = "INSERT into tag (name) VALUES('".$tag."');";
		$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $query . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		if($result){
			$query = "SELECT id from tag where name = '".$tag."';";
			$result = mysql_query($query)  or die("<b>A fatal MySQL error occured</b>.
				<br/> Query: " . $query . "
				<br/> Error: (" . mysql_errno() . ") " . mysql_error());
			$t = mysql_fetch_array($result);
			$tagID = $t['id'];
			$query = "SELECT narrative_id from highlight where id = ".$highlightID.";";
			$result = mysql_query($query)  or die("<b>A fatal MySQL error occured</b>.
				<br/> Query: " . $query . "
				<br/> Error: (" . mysql_errno() . ") " . mysql_error());
			$narrative = mysql_fetch_array($result);
			$narrativeID = $narrative['narrative_id'];
			$query = "INSERT into highlight_xref_tag (highlight_id, tag_id, narrative, user) VALUES(".$highlightID.", ".$tagID.", ".$narrativeID.", '".$user."');";
			$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
				<br/> Query: " . $query . "
				<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		}
	}
	echo json_encode(array('error'=>'no-error'));	
}

/** delete an annotation and all associated content **/
function deleteAnnotation($highlightID, $user){
	$query = "SELECT * from highlight_xref_note WHERE highlight_id = ".$highlightID.";";
	$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
		<br/> Query: " . $query . "
		<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	if(mysql_num_rows($result) > 0){
		echo json_encode(array('error'=>'There are attached notes!'));	
	}else{
		$query = "SELECT * from highlight_xref_tag WHERE highlight_id = ".$highlightID.";";
		$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $query . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		if(mysql_num_rows($result) > 0){
			echo json_encode(array('error'=>'There are attached tags!'));	
		}else{
		$query = "DELETE from highlight WHERE id = ".$highlightID.";";
		$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $query . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		echo json_encode(array('error'=>'no-error'));
		}	
	}
}

?>