<?php
/**************************************************************
dependencygraph.php

NOT USED by wordseer at present, but will be used by wordseer 
later.

Utilities for returning the network of relationships between words in a given 
section of text in the form of a JSON object with named nodes and edges between 
the nodes.


**************************************************************/

include 'dbsetup.php';
include 'util.php';

if($_GET['start'] && $_GET['end']){
	dependencyGraph($_GET['start'], $_GET['end']);
}

/** get the dependency graph from all the sentences in the span **/
function dependencyGraph($startSentID, $endSentID){
	$data = array("start"=>$startSentID, "end"=>$endSentID, "nodes"=>array(), "edges"=>array());
	$nodes = array();
	$nodesList = array();
	for($sentID = $startSentID; $sentID <= $endSentID; $sentID++){
			$query = "SELECT count(id) as count, gov, gov_pos, dep, dep_pos, relationship from dependency, dependency_xref_sentence WHERE id = dependency_id AND sentence_id =".$sentID." GROUP BY id;";
			$result = mysql_query($query);
			while($row = mysql_fetch_array($result)){
				if(!in_array($row['gov'].$row['gov_pos'], $nodes)){
					array_push($nodes, $row['gov'].$row['gov_pos']);
					$info = array('name'=>$row['gov'], 'weight'=>0, 'type'=>$row['gov_pos']);
					$data['nodes'][$row['gov']] = $info;
				}
				if(!in_array($row['dep'].$row['dep_pos'], $nodes)){
					array_push($nodes, $row['dep'].$row['dep_pos']);
					$info = array('name'=>$row['dep'], 'weight'=>0, 'type'=>$row['dep_pos']);
					$data['nodes'][$row['dep']] = $info;
				}
				$data['nodes'][$row['gov']]['weight'] += $row['count'];
				$data['nodes'][$row['dep']]['weight'] += $row['count'];
				array_push($data['edges'], array('start'=>$row['gov'], 'end'=>$row['dep'], 'weight'=>$row['count']));
			}
	}
	foreach($data['nodes'] as $node){
		array_push($nodesList, $node);
	}
	$data['nodes'] = $nodesList;
	echo json_encode($data);
}



?>