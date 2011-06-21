<?php
/***
matchingsentences.php
Called by the function heatMap in heatmap.js in service of heatmap.php
Utilities for getting all the sentences in all the narratives
that match a set of words or (todo) grammatical pattern or (todo) tag
***/

include 'dbsetup.php';
include 'util.php';
/**
Return the list of sentences that  contain the given words
in the given subset of narratives
**/
$query = $_GET['q'];
$sortBy= $_GET['sort'];
$type = $_GET['type'];
$unit = $_GET['unit'];
$direction= "ASC";
if($_GET['direction'] == 'descending'){
	$direction = "DESC";
}
if($type == "all"){
	if($unit == "sentences"){
		$data = getAllMatching('sentences', $query, $sortBy, $direction, 'all', false);
	}
	else{
		$data = getAllMatching('paragraphs', $query, $sortBy, $direction, 'all', false);
	}
}
else if($type == "tag"){
	$tag = $_GET['tag'];
	if($unit == "sentences"){
		$data = getAllMatching('sentences', $query, $sortBy, $direction, 'tag', mysql_real_escape_string($tag));
	}else{
		$data = getAllMatching('paragraphs', $query, $sortBy, $direction, 'tag', mysql_real_escape_string($tag));
		
	}

}
echo json_encode($data);


/**
@param	unit - the unit of text. Either 'sentences' or 'paragraphs'.
@param	matchExpression	The expression to match
@param	sortBy	The field on which to sort the narratives
@param	direction	'ASC' (ascending) or 'DESC' (descending)
@param	filter	The subset of narratives to consider, set to 'all' 
by default
@param  tag		The tag on which to filter narratives, set to false 
by default
*/
function getAllMatching($unit, $matchExpression, $sortBy, $direction, $filter,  $tag){
	$tableName = 'sentence';
	$field = 'sentence';
	$count = "sentence_count";
	$id_field = "sentence.id";
	if($unit == 'paragraphs'){
		$tableName = 'paragraph';
		$field = 'text';
		$count = "paragraph_count";
		$id_field = "paragraph.id";
	}
	if($filter == 'all'){
		$q = "SELECT * from narrative  WHERE sentence_count > 100 ORDER BY ".$sortBy." ".$direction.";";
	}
	else if($filter == 'tag'){
		$q = "SELECT id from tag where name = '".$tag."';";
		$result = mysql_query($q) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $q . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		$row = mysql_fetch_array($result);
		$tagID = $row['id'];
		$q = "SELECT * from narrative, highlight_xref_tag WHERE narrative.id = highlight_xref_tag.narrative AND tag_id = ".$tagID.";";
	}
	$result = mysql_query($q) or die("<b>A fatal MySQL error occured</b>.
		<br/> Query: " . $q . "
		<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	$narratives = array();
	$metadata = array();
	while($row = mysql_fetch_array($result)){
		$metadata = array();
		$metadata['id'] = $row['id'];
		$metadata['title'] = utf8_encode($row['title']);
		$metadata['date'] = $row['date'];
		array_push($narratives, $metadata);
	}
	$data = array();
	if($filter == 'all'){
		$q = "SELECT narrative.id as id, ".$count.",".$id_field." as unit_id, number, title, date FROM (narrative JOIN ".$tableName." on narrative.id = narrative_id) WHERE sentence_count > 100 AND MATCH ".$field." AGAINST( '".$matchExpression."' IN BOOLEAN MODE) ORDER BY ".$sortBy." ".$direction.", ".$tableName.".id ASC;";
		$results = mysql_query($q) or die("<b>A fatal MySQL error occured</b>.<br/> Query: ". $q ."<br/> Error: (".mysql_errno().") ".mysql_error());
		$narrativeData = array();
		$narrativeData['sentences'] = array();
		$narrativeData['narrative'] = -1;
		$narrativeData['length'] = -1;
		$narrative = -1;
		mysql_data_seek($results, 0);
		while($row = mysql_fetch_array($results)){
			if($narrative != $row['id']){
				if($narrative != -1){
					array_push($data, $narrativeData); // push the old data in.
				}
				$narrative = $row['id'];
				while($narrative != $narratives[0]['id']){ // you skipped one
					$narrativeData = array();
					$narrativeData['sentences'] = array();
					$narrativeData['narrative'] = $narratives[0]['id'];
					$narrativeData['length'] = -1;
					$narrativeData['title'] = utf8_encode($narratives[0]['title']);
					$narrativeData['date'] = $narratives[0]['date'];
					array_push($data, $narrativeData);
					$narratives = array_slice($narratives, 1);
				}
				$narratives = array_slice($narratives, 1);	
				$narrative = $row['id'];
				$narrativeData = array();
				$narrativeData['narrative'] = $row['id'];
				$narrativeData['length'] = $row[$count];
				$narrativeData['sentences'] = array();
				$narrativeData['title'] = utf8_encode($row['title']);
				$narrativeData['date'] = $row['date'];
			}
			array_push($narrativeData['sentences'], array("number"=>$row['number'], "id"=>$row['unit_id']));
		}
		array_push($data, $narrativeData); // push the old data in.
		while(count($narratives) > 0){ // you skipped one
			$narrativeData = array();
			$narrativeData['sentences'] = array();
			$narrativeData['narrative'] = $narratives[0]['id'];
			$narrativeData['length'] = -1;
			$narrativeData['title'] = utf8_encode($narratives[0]['title']);
			$narrativeData['date'] = $narratives[0]['date'];
			array_push($data, $narrativeData);
			$narratives = array_slice($narratives, 1);
		}
	} 
	else if($filter == 'tag'){
		foreach($narratives as $narrative){
			$q = "SELECT narrative.id as id, ".$count.", number, title, date FROM (narrative JOIN ".$tableName." on narrative.id = narrative_id) WHERE narrative.id = ".$narrative['id']." AND sentence_count > 100 AND MATCH ".$field." AGAINST( '".$matchExpression."' IN BOOLEAN MODE) ORDER BY ".$sortBy." ".$direction.", ".$tableName.".id ASC;";
			$results = mysql_query($q) or die("<b>A fatal MySQL error occured</b>.<br/> Query: ". $q ."<br/> Error: (".mysql_errno().") ".mysql_error());
			if(mysql_num_rows($results) > 0){
				$narrativeData = array();
				$narrativeData['sentences'] = array();
				while($row = mysql_fetch_array($results)){
					$narrativeData['narrative'] = $row['id'];
					$narrativeData['length'] = $row[$count];
					$narrativeData['title'] = utf8_encode($row['title']);
					$narrativeData['date'] = $row['date'];
					array_push($narrativeData['sentences'], $row['number']);
				}
			}
			else{
				$narrativeData = array();
				$narrativeData['sentences'] = array();
				$narrativeData['narrative'] = $narrative['id'];
				$narrativeData['length'] = -1;
				$narrativeData['title'] = utf8_encode($narrative['title']);
				$narrativeData['date'] = $narrative['date'];
			}
			array_push($data, $narrativeData);
		}
	}
	return $data;
}

?>