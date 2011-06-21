<?php
include 'dbsetup.php';
include 'util.php';

/********************************************************************
	listalltags.php
	Called by autocomplete functionality in service of annotate.js
	(in service of view.php) and by heatmap.js (in service of 
	heatmap.php)
	
	Returns a JSON object contatining a string, representing all
	the tags.
********************************************************************/

listAllTags();

/** list all tags **/
function listAllTags(){
	$query = "SELECT DISTINCT name from highlight_xref_tag, tag WHERE tag_id = tag.id ORDER BY name;";
	$result = mysql_query($query);
	$answer = array();
	while($row = mysql_fetch_array($result)){ array_push($answer, $row['name']);}
	echo json_encode(array('tags'=>$answer));
}

?>