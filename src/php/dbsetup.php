<?php
$user="narratives";
$password = "cill;alto";
$database="narratives";
mysql_connect('localhost',$user,$password);
@mysql_select_db($database) or die( "Unable to connect");

/** Global Variables
*/
$gov_result = array();
$dep_result = array();
$rel_result = array();

function getRelationID($relation){
  $query = "SELECT id FROM relationship WHERE relationship ='".$relation."';";
  //echo $query.'
  //';
  $result = mysql_query($query);
  if(mysql_num_rows($result)>0){
    $row =  mysql_fetch_array($result);
    return $row['id'];
  }else{
    return -1;
  }
}

/** 
	Even though this function is named "getWordID", it
	actually returns all the ID's that correspond to a 
	given surface word. A word can have multiple id's 
	if it has different parts of speech.
*/
function getWordID($word){
  $query = "SELECT id FROM word WHERE word ='".trim($word)."';";
  $result = mysql_query($query);
  if(mysql_num_rows($result)>0){
    $ids = array();
    while($row =  mysql_fetch_array($result)){
      array_push($ids, $row['id']);
    }
    return join(", ", $ids);
  }else{
    return -1;
  }
}

function getWord($id){
  $query = "SELECT word from word where id = ".$id.";";
  $result = mysql_query($query);
  if(mysql_num_rows($result)>0){
    return $row['id'];
  }else{
    return '??';
  }
}

function getDependencyID($relationID, $govID, $depID){
  $query = "SELECT id FROM dependency WHERE relation_id =".$relationID." AND gov_id =".$govID." and dep_id = ".$depID.";";
  $result = mysql_query($query);
  if(mysql_num_rows($result)>0){
    $row= mysql_fetch_array($query);
    return $row['id'];
  }else{
    return -1;
  }
}


function getMetadata($sentenceID){
   $query="SELECT sentence, title, publisher, full, pubPlace, para.narrative_id as id, paragraph.id as pid, type, date 
   FROM paragraph 
   JOIN
   (SELECT sentence, title, publisher, full, pubPlace, N.narrative_id, date, paragraph_id as id
     FROM (author 
     JOIN author_xref_narrative 
     ON author.id = author_xref_narrative.author_id) 
     JOIN(SELECT sentence, title, publisher, pubPlace, narrative_id, paragraph_id, date 
         from narrative JOIN  
         (SELECT DISTINCT sentence, narrative_id, paragraph_id 
         from sentence
         WHERE sentence.id = ".$sentenceID.") as S 
       ON narrative.id = S.narrative_id) as N
   ON author_xref_narrative.narrative_id = N.narrative_id) AS para
   ON paragraph.id = para.id;";
    $result = mysql_query($query);
    //cho '<br>'.$query;
    return $result;
}

/**
@param	withinSentence	True if you want to search within
						a set of sentences
@param	withinNarrative	True if you want to search within a
						set of narratives 
@param	within			a list of sentence or narrative id's
						if either <withinSentence> or 
						<withinNarrative> is marked as True. 
						Otherwise, false.
*/
function getDependencyIDs($gov, $dep, $relation, $withinNarrative, $withinSentence, $within, $getStatistics){
  $tablenames = "dependency, dependency_xref_sentence ";
  $where = "";
  if($withinSentence && strlen($within)>0){
      $where = "AND sentence_id in (".$within.")";
  }else if ($withinNarrative && strlen($within)>0){
    $tablenames = "dependency, dependency_xref_sentence, sentence ";
    $where = " AND sentence.id = sentence_id AND narrative_id in (".$within.")";
  }
  $r = strlen($relation)>0;
  $g = strlen($gov)>0;
  $d = strlen($dep)>0;
  $rel_w = "";
  $gov_w  ="";
  $dep_w = "";
  if($r){
    $rel_w = "relation_id IN (".$relation.")";
  }
  if($g){
    $gov_w = "gov_id IN (".$gov.")"; 
  }
  if($d){
    $dep_w = "dep_id IN (".$dep.")";
  }
  if($r || $g || $d ){
    $query = "SELECT * FROM ".$tablenames." WHERE ";
	$statistics_query = "SELECT * FROM (SELECT *, COUNT(sentence_id) as value FROM ".$tablenames." WHERE ";
    if($r && $g && $d){
      $query = $query.$rel_w." AND ".$gov_w." AND ".$dep_w;
	  $statistics_query = $statistics_query.$rel_w." AND ".$gov_w." AND ".$dep_w;
    }
    else if($r && $g){
      $query = $query.$rel_w." AND ".$gov_w;
	  $statistics_query = $statistics_query.$rel_w." AND ".$gov_w;
    }
    else if($r && $d){
      $query = $query.$rel_w." AND ".$dep_w;
	  $statistics_query = $statistics_query.$rel_w." AND ".$dep_w;
    }
    else if($g && $d){
      $query = $query.$gov_w." AND ".$dep_w;
	  $statistics_query = $statistics_query.$gov_w." AND ".$dep_w;
    }
    else if ($r){
        $query = $query.$rel_w;
		$statistics_query = $statistics_query.$rel_w;
    }
    else if ($g){
        $query = $query.$gov_w;
		$statistics_query = $statistics_query.$gov_w;
    }
    else{
        $query = $query.$dep_w;
		$statistics_query = $statistics_query.$dep_w;
    }
    $query = $query."AND dependency_id = dependency.id ";
    $query = $query.$where.";";
	//statistics
	$statistics_query = $statistics_query."AND dependency_id = dependency.id ";
    $statistics_query = $statistics_query.$where;
	
  }
  else if(withinSentence){
    $query = "SELECT * FROM ".$tablenames." WHERE dependency_id = dependency.id ".$where.";";
	$statistics_query = "SELECT *, COUNT(sentence_id) FROM ".$tablenames." WHERE dependency_id = dependency.id ".$where;
  }
  $relationship_query = $statistics_query." GROUP BY(relation_id)) as S ORDER BY value DESC;";
  $gov_query = $statistics_query." GROUP BY(gov)) as S ORDER BY value DESC;";
  $dep_query = $statistics_query." GROUP BY(dep)) as S ORDER BY value DESC;";
  
  $t1 = time();
  $result = mysql_query($query);
  $t = time() - $t1;
  $log = fopen("/projects/wordseer/logs/query.log", "a");
  $data = 'query='.$query.' time='.$t." s\n";
  fwrite($log, $data);
  fclose($log);
  //echo '"'.$query.' '.$t.' seconds"';
  //statistics
  if($getStatistics){
    global $rel_result, $gov_result, $dep_result;
     $t1 = time();
     $rel_result = mysql_query($relationship_query);
     $gov_result = mysql_query($gov_query);
     $dep_result = mysql_query($dep_query);
     $t = time() - $t1;
     //echo '"Time to fetch statistics: '.$t.' seconds."';
     //echo '"'.$gov_query.'"';
     //echo $dep_query;
     //echo $relationship_query;
  }
  return $result;
}


function relationshipIDList($words){
  if(strlen($words) > 0){
  $exploded = explode(' ', trim($words));
  $listformat = "";
  $i = 0;
  foreach($exploded as $word){
    if($i==0){
      $listformat = $listformat.getRelationID($word);
    }else{
      $listformat = $listformat.", ".getRelationID($word)." ";
    }
    $i+=1;
  }
  return $listformat;
  }else{
    return "";
  }
}

/**
Converts a list of words to a list of word ID's
*/
function wordIDList($words){
    if(strlen($words) > 0){
	$exploded = explode(' ', trim($words));
	if(strpos($words, ',')){
		$exploded = explode(',',trim($words));
	}
    $listformat = "";
    $i = 0;
    foreach($exploded as $word){
      if($i==0){
        $listformat = $listformat.getWordID($word);
      }else{
        $listformat = $listformat.", ".getWordID($word)." ";
      }
      $i+=1;
    }
    return $listformat;
    }else{
      return "";
    }
}

/**
	a.k.a join(', ', $words)
	my bad.
*/
function listFormat($words){
  if(strlen($words) > 0){
  $exploded = explode(' ', trim($words));
  $listformat = "";
  $i = 0;
  foreach($exploded as $word){
    if($i == 0){
      $listformat = $listformat."'".$word."'";
    }else{
      $listformat = $listformat.", '".$word."' ";
    }
    $i += 1;
  }
  return $listformat;
  }else{
    return "";
  }
}

function numberList($numbers){
  if(strlen($numbers) > 0){
  $exploded = explode(' ', trim($numbers));
  $listformat = "";
  $i = 0;
  foreach($exploded as $word){
    if($i==0){
      $listformat = $listformat.$word;
    }else{
      $listformat = $listformat.", ".$word." ";
    }
    $i+=1;
  }
  return $listformat;
  }else{
    return "";
  }
}

function printStatistics($statistics){
  $dimensions = array("relationship", "dep", "gov");
  echo '<script type="text/javascript">
';
  foreach($dimensions as $dimension){
    echo 'var data_'.$dimension.' = ';
    echo json_encode($statistics[$dimension]);
    echo ';

';
  }
  echo '</script>

';
}

/** TODO: search within results
 */
function getStatistics($deps, $govs, $rels){
	$t1 = time();
	$statistics = array();
	global $rel_result, $dep_result, $gov_result;
	//dep statistics
	$counter = 0;
	$statistics['dep'] = array();
	$statistics['dep']['data'] = array();
	
	while($row = mysql_fetch_array($dep_result)){
		$counter +=1;
		if($counter == 1){
			$statistics['dep']['max'] = $row['value'];
		}
		$dataPoint = array();
		$dataPoint['label']  = $row['dep'];
		$dataPoint['value'] = $row['value'];
		$dataPoint['ids'] = array();
		//get the sentence ID's
		// $query = "SELECT sentence_id from dependency, dependency_xref_sentence WHERE dep_id=".$row['dep_id'];
		//    if(strlen($govs) >0){
		//        $query = $query." AND gov_id in (".$govs.")";
		//    }
		//    if(strlen($rels) > 0){
		//      $query = $query. " AND relation_id in (".$rels.")";
		//    }
		//    $query = $query." AND dependency.id = dependency_id;";
		//    $result = mysql_query($query);
		//    while($row2 = mysql_fetch_array($result)){
		//      array_push($dataPoint['ids'], $row2['sentence_id']);
		//    }
		if(strlen($dataPoint['label'])>0){
		  array_push($statistics['dep']['data'], $dataPoint);
		}
	}
	//gov statistics
	$counter = 0;
	$statistics['gov'] = array();
	$statistics['gov']['data'] = array();
	while($row = mysql_fetch_array($gov_result)){
		$counter +=1;
		if($counter == 1){
			$statistics['gov']['max'] = $row['value'];
		}
		$dataPoint = array();
		$dataPoint['label']  = $row['gov'];
		$dataPoint['value'] = $row['value'];
		$dataPoint['ids'] = array();
		//get the sentence ID's
	  // $query = "SELECT sentence_id from dependency, dependency_xref_sentence WHERE gov_id=".$row['gov_id'];
	 //  if(strlen($deps) >0){
	 //      $query = $query." AND dep_id in (".$deps.")";
	 //  }
	 //  if(strlen($rels) > 0){
	 //    $query = $query. " AND relation_id in (".$rels.")";
	 //  }
	 //  $query = $query." AND dependency.id = dependency_id;";
	 //  $result = mysql_query($query);
	 //  while($row2 = mysql_fetch_array($result)){
	 //    array_push($dataPoint['ids'], $row2['sentence_id']);
	 //  }
		if(strlen($dataPoint['label'])>0){
		  array_push($statistics['gov']['data'], $dataPoint);
	  }
	}
	//rel statistics
	$counter = 0;
	$statistics['relationship'] = array();
	$statistics['relationship']['data'] = array();
	while($row = mysql_fetch_array($rel_result)){
		$counter +=1;
		if($counter == 1){
			$statistics['relationship']['max'] = $row['value'];
		}
		$dataPoint = array();
		$dataPoint['label']  = $row['relationship'];
		$dataPoint['value'] = $row['value'];
		$dataPoint['ids'] = array();
		//get the sentence ID's
		// $query = "SELECT sentence_id from dependency, dependency_xref_sentence WHERE relation_id=".$row['relation_id'];
		//    if(strlen($govs) >0){
		//        $query = $query." AND gov_id in (".$govs.")";
		//    }
		//    if(strlen($depss) > 0){
		//      $query = $query. " AND dep_id in (".$deps.")";
		//    }
		//    $query = $query." AND dependency.id = dependency_id;";
		//    $result = mysql_query($query);
		//    while($row2 = mysql_fetch_array($result)){
		//      array_push($dataPoint['ids'], $row2['sentence_id']);
		//    }
		if(strlen($dataPoint['label'])>0){
		  array_push($statistics['relationship']['data'], $dataPoint);
	  }
	}
	//echo "Statistics time: ".strval(time()-$t1)." seconds <br>";
	return $statistics;
}

?>
