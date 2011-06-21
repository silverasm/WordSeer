<?php
/*******************************************
	getnarrativeannotations.php
	Called by displayAnnotations() in annotate.js in service of view.js
	Gives a JSON object of all the annotations associated with a narrative
********************************************/
include 'dbsetup.php';
include 'util.php';

/** The main function */
function getAnnotations($narrativeID){
	$query = "SELECT * from highlight where narrative_id = ".$narrativeID.";";
	$result = mysql_query($query);
	$data = array();
	while($row = mysql_fetch_array($result)){
		$info = array();
		$highlightID = $row['id'];
		$info['id'] = $highlightID;
		$info['start'] = $row['start'];
		$info['end'] = $row['end'];
		$info['startpos'] = $row['start_index'];
		$info['endpos'] = $row['end_index'];
		$info['username'] = $row['user'];
		$info['notes'] = array();
		$info['tags'] = array();
		$query = "SELECT * from highlight_xref_note, note WHERE highlight_id = ".$highlightID." AND note_id = note.id;";
		$notes = mysql_query($query);
		while($note = mysql_fetch_array($notes)){
			array_push($info['notes'], array("note"=>nl2br($note['text']), "id"=> $note['note_id'], "username" => $note['user']));
		}
		$query = "SELECT * from highlight_xref_tag, tag WHERE highlight_id = ".$highlightID." AND tag_id = tag.id;";
		$tags = mysql_query($query);
		while($tag = mysql_fetch_array($tags)){
			array_push($info['tags'], array('tag'=>$tag['name'], 'username'=>$tag['user'], 'id'=>$tag['tag_id']));
		}
		array_push($data, $info);
	}
	echo json_encode($data);
}

getAnnotations($_GET['narrative']);
?>