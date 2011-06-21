<?php
/****************************************************************************
	getcontext.php
	Called by getContext() in heatmap.js in service of heatmap.php
	Gets the concordance and grammatical context in which a heat map
	query occurs.
****************************************************************************/
include 'dbsetup.php';
include 'util.php';

if($_GET['words']){
	$words = mysql_real_escape_string($_GET['words']);
	$context = array();
	$matches = getConcordance($words);
	$context["concordance"] = array();
	$context["concordance"]["num"] = $matches['numMatches'];
	$context["concordance"]["docs"] = $matches['numNarratives'];
	$context["concordance"]["matches"] = $matches['matches'];
	$context["grammatical"] = getGrammaticalContext($words);
	if($_GET['type'] == "tree"){
		$context['concordance']['lefts'] = getSentences($context['concordance']['matches'], "left");
		$context['concordance']['rights'] = getSentences($context['concordance']['matches'], "right");
	}
	echo json_encode($context);
}

//Part 1: concordance
function getConcordance($words){
	$query = "SELECT * FROM sentence WHERE match sentence against('".$words."' IN BOOLEAN MODE);";
	$results = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
		<br/> Query: " . $query . "
		<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	$pattern = "/".str_replace('"', "", $_GET['words'])."/";
	$matched = array();
	$matched['numMatches'] = mysql_num_rows($results);
	$matched['numNarratives'];
	$matched['matches'] = array();
	while($row = mysql_fetch_array($results)){
		$split = array();
		preg_match($pattern, $row['sentence'], $matches, PREG_OFFSET_CAPTURE);
		$left = substr($row['sentence'], 0, $matches[0][1]);
		$right = substr($row['sentence'], $matches[0][1]+strlen($matches[0][0]), $matches[0][1]+strlen($matches[0][0]));
		$match = $matches[0][0];
		$split['number'] = $row['number'];
		$split['narrative'] = $row['narrative_id'];
		$split['id'] = $row['id'];
		$split['left'] = $left;
		$split['right'] = $right;
		$split['match'] = $match;
		array_push($matched['matches'], $split);
	}
	$query = "SELECT COUNT(DISTINCT narrative_id) as c FROM sentence WHERE match sentence against('".$words."' IN BOOLEAN MODE);";
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)){
		$matched['numNarratives'] = $row['c'];
	}
	return $matched;
}

//Part 2: grammatical context (todo)
function getGrammaticalContext($words){
	return array();
}

//Part 3: word tree
function getSentences($matches, $which){
	$sentences = array();
	foreach($matches as $match){
		if($which == "left"){
			$sentence = array_reverse(split(" ", $match[$which]));
			if($sentence[0]==""){
				$sentence = array_slice($sentence, 1);
			}
			array_push($sentences, array("id"=>$match['id'], "sentence"=>$sentence));
		}else{
			$sentence = split(" ", $match[$which]);
			if($sentence[0]==""){
				$sentence = array_slice($sentence, 1);
			}
			array_push($sentences, array("id"=>$match['id'], "sentence"=>$sentence));
		}
	}
	return $sentences;
}

?>