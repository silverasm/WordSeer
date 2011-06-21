<?php
/************************************************************** 
synonym groups:

given pairwise similarity scores, groups words into synonym sets
***********************************************************/

include 'priorityqueue.php';

/** dispatch procedure **/
if($_GET['id']){
	include 'dbsetup.php';
	include 'util.php';
	display(synset(mysql_real_escape_string($_GET['id']), "", ""));
} else if($_GET['word']&&$_GET['pos']){
	include 'dbsetup.php';
	include 'util.php';
	display(synset(false, mysql_real_escape_string($_GET['word']),
	mysql_real_escape_string($_GET['pos'])));
} else if($_GET['words'] && $_GET['type']=="context"){
	include 'dbsetup.php';
	include 'util.php';
	$words = decode($_GET['words']);
	$answer = getContext($words);
	display($answer);
}

function synset($id, $word, $pos){
	$query = "SELECT * from word where word = '".$word."' and pos = '".$pos."';";
	if($id){
		$query = "SELECT * from word where id = ".$id.";";
	}
	$result = mysql_query($query);
	$row = array();
	$row2 = array();
	$answer = array();
	while($row = mysql_fetch_array($result)){
		$id = $row['id'];
		$query = "SELECT * from synsets where word1_id = ".$id." ORDER BY similarity DESC;";
		$result2 = mysql_query($query);
		while($row2 = mysql_fetch_array($result2)){
			array_push($answer, array("word"=>$row2['word2'], "id"=>$row2['word2_id'], "similarity"=>$row2['similarity']));
		}
	}
	// if no matching words found for the ID, then try for at least
	// the same surface form
	if(count($answer) == 0 && mysql_num_rows($result) > 0){
		$query = "SELECT * from word where id = ".$id.";";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		$query = "SELECT * from word where word = '".mysql_real_escape_string($row['word'])."';";
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)){
		$query = "SELECT * from synsets where word1_id = ".$row['id']." ORDER BY similarity DESC;";
		$result2 = mysql_query($query);
		while($row2 = mysql_fetch_array($result2)){
			array_push($answer, array("word"=>$row2['word2'], "id"=>$row2['word2_id'], "similarity"=>$row2['similarity']));
		}
	}
	}
	return $answer;
}

/** get the group of words most similar to this word **/
function old_synset($id, $word, $pos){
	$query = "SELECT * from word where word = '".$word."' and pos = '".$pos."';";
	if($id){
		$query = "SELECT * from word where id = ".$id.";";
	}
	$result = mysql_query($query);
	$row = array();
	$friends = array();
	$similarities = array();
	$ids = array();
	$poss = array();
	$best = new PriorityQueue;
	$best->clear();
	$row;
	$word;
	$pos;
	$friend;
	$friends_of_friends;
	while($row = mysql_fetch_array($result)){
		$id = $row['id'];
		$word = $row['word'];
		$_GET['word'] = $word;
		$pos = $row['pos'];
		$_GET['pos'] = $pos;
		$friends = getMostSimilar($id);
		foreach($friends as $friend){
			if($friend['word'] != $word){
				if(!array_key_exists($friend['id'], $similarities)){
					$similarities[$friend['id']] = 0;
					$ids[$friend['id']] = $friend['word'];
					$poss[$friend['id']] = $friend['pos'];
				}
				$similarities[$friend['id']] += $friend['similarity'];
				$friends_of_friends = getMostSimilar($friend['id']);
				foreach($friends_of_friends as $ff){
					if($ff['word'] != $word){
						if(!array_key_exists($ff['id'], $similarities)){										
							$similarities[$ff['id']] = 0;
							$ids[$ff['id']] = $ff['word'];
							$poss[$ff['id']] = $ff['pos'];							
						}
						$similarities[$ff['id']] += $ff['similarity']*$friend['similarity'];	
					}
				}
			}
		}
	}
	foreach(array_keys($similarities) as $id){
		$best->push($id, $similarities[$id]);
	}
	$answer = array();
	$max = 0;
	$next;
	$w;
	$pos;
	$sim;
	while(!$best->IsEmpty()){
		$next = $best->pop();	
		$w = $ids[$next];
		$pos = $poss[$next];
		$sim = $similarities[$next];
		if($sim>$max){
			$max = $sim;
		}
		if($sim >= $max/2){
		array_push($answer, array("word"=>$w, "id"=>$next, "similarity"=>$sim, "pos"=>$pos));
		}
	}
	return $answer;
}

/** get the 10 most similar words to this word **/
function old_getMostSimilar($id){
	$query = "SELECT word1_id, word.word, word.pos, lin_similarity 
		from similarity, word
		WHERE word.id = word1_id
		AND word2_id = ".$id."
		ORDER BY lin_similarity desc
		LIMIT 10;";
	$result = mysql_query($query);
	$friends = array();
	$row = array();
	while($row = mysql_fetch_array($result)){
		array_push($friends, array("id"=>$row['word1_id'], "similarity"=>$row['lin_similarity'], "word"=>$row['word'], "pos"=>$row['pos']));
	}
	return $friends;
}

function getMostSimilar($id){
	$query = "SELECT word1_id, lin_similarity 
		from similarity
		WHERE word2_id = ".$id."
		ORDER BY lin_similarity desc;";
	$result = mysql_query($query);
	$friends = array();
	$row = array();
	$word;
	$result2;
	$row2;
	$index = 0;
	while($row = mysql_fetch_array($result)){
		if($index < 10){
		$query = "SELECT * from word WHERE id = ".$row['word1_id'].";";
		$result2 = mysql_query($query);
		$row2 = mysql_fetch_array($result2);
		array_push($friends, array("id"=>$row['word1_id'], "similarity"=>$row['lin_similarity'], "word"=>$row2['word'], "pos"=>$row2['pos']));
		$index += 1;	
		}else{
			break;
		}
	}
	return $friends;
}

/** print out the web page, or display json **/
function display($synset){
	if(!$_GET['json']){ // if not JSON-format request
		echo '<!DOCTYPE html>
		<html>
		<head>
			<title> Lin Similarity tester </title>
		</head>
		<body>
			<h1> Enter a word and part of speech </h1>
			<form action="">
				<label>Word</label><input name="word" value="';
		if($_GET['word']){echo $_GET['word'];}else{echo "mother";}
		echo '"></input><br>
				<label>POS</label><input name="pos" value="';
		if($_GET['pos']){echo $_GET['pos'];}else{echo "NN";}

		echo '"></label>
				<input type="submit" value="Go">
			</form>
		'; 
		echo '<ul>';
		foreach($synset as $s){
			echo '<li><a href="?id='.$s['id'].'">';
			echo $s['word'];
			echo '</a></li>';
		}
		echo '</ul>';
		echo '</body></html>';
	}else{
		echo json_encode($synset);
	}
}

/** unpacks the recieved words **/
function decode($words){
	$components = split(" ", $words);
	$data = array();
	foreach($components as $component){
		$c = split("_", $component);
		$c = array("word"=>$c[0], "id"=>$c[1], "sentence"=>$c[2]);
		array_push($data, $c);
	}
	return $data;
}

/** find all the relations between the given words and expand them to include other words **/
function getContext($words){
	$contexts = array();
	$information;
	$word;
	foreach($words as $word){
		$information = array("synonyms"=>synset($word['id']));
		$information["context"] = array();
		$information["word"] = $word['word'];
		array_push($contexts, $information);
	}
	return $contexts;
}

?>