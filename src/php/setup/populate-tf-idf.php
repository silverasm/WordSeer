<?php
/************************************************************** 
populate-tf-idf.php

Called once, after the main dataabse has been created to populate tables of
TF-IDF values for words in paragraphs.
***********************************************************/
include 'dbsetup.php';
//populateWordIDFs();
populateParagraphTFs();

function populateWordIDFs(){
	$query = "SELECT * from word;";
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)){
		echo $row['word']."\t ";
		$query = "SELECT COUNT(distinct paragraph_id) as p, COUNT(distinct sentence_id) as s, word_id FROM sentence, sentence_xref_word WHERE sentence.id = sentence_id AND word_id = ".$row['id'].";";
		$counts = mysql_query($query);
		$counts = mysql_fetch_array($counts);
		$query = "INSERT INTO word_idf (word_id, word, paragraph_frequency, sentence_frequency) VALUES(".$row['id'].", '".$row['word']."', ".$counts['p'].", ".$counts['s'].");";
		mysql_query($query);
		echo $counts['p'],"\n";
	}
}

function populateParagraphTFs(){
	$query = "SELECT * from paragraph;";
	$result = mysql_query($query);
	$N = mysql_num_rows($result);
	while($row = mysql_fetch_array($result)){
		echo $row['id'];
		$query = "SELECT paragraph_id, COUNT(word_id) as count, word_id FROM sentence, sentence_xref_word WHERE sentence.id = sentence_id AND paragraph_id = ".$row['id']." GROUP BY word_id;";
		$counts = mysql_query($query);
		while($count = mysql_fetch_array($counts)){
			$query = "SELECT paragraph_frequency, word from word_idf WHERE word_id = ".$count['word_id'].";";
			$df = mysql_query($query);
			$df = mysql_fetch_array($df);
			$word = mysql_real_escape_string($df['word']);
			$df = $df['paragraph_frequency'];
			if($df){
				$idf = log($N/$df);
				$tf_idf = $count['count']*$idf;
				$query = "INSERT INTO paragraph_tf (paragraph_id, word_id, tf, tf_idf, word) VALUES (".$row['id'].", ".$count['word_id'].", ".$count['count'].", ".$tf_idf.",'".$word."');";
				mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
					<br/> Query: " . $query . "
					<br/> Error: (" . mysql_errno() . ") " . mysql_error());
			}
		
		}
		echo "\n";
	}
}

?>