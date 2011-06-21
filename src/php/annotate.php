<?php
	include 'dbsetup.php';
	include 'util.php';
	
	$start = $_GET['start'];
	$end = $_GET['end'];
	$startPos = $_GET['startpos'];
	$endPos = $_GET['endpos'];
	$narrativeID = $_GET['narrative'];
	$user = "'anonymous'";
	if($_GET['username']){
		$user = "'".$_GET['username']."'";
	}
	//get highlight ID
	$query = "SELECT id from highlight WHERE start = ".$start." AND end =".$end." AND start_index = ".$startPos." AND end_index = ".$endPos." AND narrative_id = ".$narrativeID.";";
	$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
		<br/> Query: " . $query . "
		<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	if(mysql_num_rows($result) == 0){
		$insert = "INSERT INTO highlight (narrative_id, start, end, start_index, end_index, user) VALUES(".$narrativeID.", ".$start.", ".$end.", ".$startPos.", ".$endPos.", ".$user.");";
		mysql_query($insert) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $insert . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	}
	$result = mysql_query($query);
	$id = mysql_fetch_array($result);
	$id = $id['id'];
	//insert note
	if($_GET['note']){
		$note = $_GET['note'];
		$insert = "INSERT INTO note (text, user) values ('".$note."', ".$user.")";
		mysql_query($insert) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $insert . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		$query = "SELECT * from note where text = '".$note."';";
		$result = mysql_query($query);
		$noteID = mysql_fetch_array($result);
		$noteID = $noteID['id'];
		$insert = "INSERT INTO highlight_xref_note (highlight_id, note_id, narrative) VALUES(".$id.", ".$noteID.", ".$narrativeID.")";
		mysql_query($insert) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $insert . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	}
	//insert tags
	if($_GET['tags']){
		$tags = split(",", $_GET['tags']);
		foreach($tags as $tag){
			$query = "SELECT id from tag where name = '".trim($tag)."';";
			$result = mysql_query($query);
			if(mysql_num_rows($result) == 0){
				$insert = "INSERT INTO tag (name) VALUES('".trim($tag)."')";
				mysql_query($insert) or die("<b>A fatal MySQL error occured</b>.
					<br/> Query: " . $query . "
					<br/> Error: (" . mysql_errno() . ") " . mysql_error());
			}
			$result = mysql_query($query);
			$tagID = mysql_fetch_array($result);
			$tagID = $tagID['id'];
			$insert = "INSERT INTO highlight_xref_tag (highlight_id, tag_id, narrative, user) VALUES(".$id.",  ".$tagID.", ".$narrativeID.", ".$user.");" or die("<b>A fatal MySQL error occured</b>.
				<br/> Query: " . $insert . "
				<br/> Error: (" . mysql_errno() . ") " . mysql_error());
			mysql_query($insert);
		}
	}
	echo json_encode($_GET);
?>
