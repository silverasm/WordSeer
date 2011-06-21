<?php
/************************************************************** 
linSimilarity.php

Calculates statistics relating words to each other
acoording to the lin similarity metric described in 
Dekang Lin, 1998, Automatic Retrieval and Clustering of Similar Words

All the functions needed to calculate lin similarity are in this file
albeit in a little disorganized way that needs supervision to execute

1. Calculate the information of dependency relationships.
	The formula is:
	I(w, r, w') = log(|w, r, w'|x|*,r,*|/|w, r, *|x|*,r,w'|)
	in order to be fast, this requires you to expand dependency to include
	counts of how often each relationship occurs and how often each 
	(dep, rel) and (gov, rel) occurs. 
	
	>> expand()
	
	Then, you can run
	
	>> calculateDependencyInformation()
	
2. Calculate word information: the denominator in the lin similarity calculation
	
	>> calculateWordInformation(); 
	
	This takes a few hours

3. Calcualte similarity.

	>> calculateSimilarity(); 
	
	This takes 2 days, excluding stopwords and infrequent words

4. Group words together into synsets for easy access later on.

	>> makeSynsets()
***********************************************************/
include '../dbsetup.php';
include '../util.php';
include '../synonym_groups.php';
gc_enable();

function expand(){
	$gov_id = 0;
	$dep_id = 0;
	$relation_id = 0;
	$row = 0;
	$query = 0;
	$id = 0;
	$result = 0;
	$count = 0;
	$r = false;
	while($id < 3000000){
		$query = "SELECT * from dependency WHERE id =".$id.";";
		$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $query . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		while($row = mysql_fetch_array($result)){
			if($row['relation_id'] != 11){
				if($row['gov_count']>0){}else{
					$query = "SELECT COUNT(*) as c from dependency_xref_sentence WHERE gov_id = ".$row['gov_id']." AND relation_id = ".$row['relation_id'].";";
					$r = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
						<br/> Query: " . $query . "
						<br/> Error: (" . mysql_errno() . ") " . mysql_error());
					$count = mysql_fetch_array($r);
					$count = $count['c'];
					$query = "UPDATE dependency SET gov_count = ".$count.' WHERE gov_id = '.$row['gov_id'].' AND relation_id = '.$row['relation_id'].';';
					$r = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
						<br/> Query: " . $query . "
						<br/> Error: (" . mysql_errno() . ") " . mysql_error());
				}
				if($row['dep_count']>0){}else{
					$query = "SELECT COUNT(*) as c from dependency_xref_sentence WHERE dep_id = ".$row['dep_id']." AND relation_id = ".$row['relation_id'].";";
					$r = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
						<br/> Query: " . $query . "
						<br/> Error: (" . mysql_errno() . ") " . mysql_error());
					$count = mysql_fetch_array($r);
					$count = $count['c'];
					$query = "UPDATE dependency SET dep_count = ".$count.' WHERE dep_id = '.$row['dep_id'].' AND relation_id = '.$row['relation_id'].';';
					$r = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
						<br/> Query: " . $query . "
						<br/> Error: (" . mysql_errno() . ") " . mysql_error());
				}
			}
		}
		echo $id."\n";
		$id++;	
	}
}
//expand();

function calculateRelationshipCounts(){
	$count = 0;
	$row = array();
	$query = "SELECT id from relationship;";
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)){
		$query = "SELECT count(sentence_id) as c from dependency_xref_sentence, dependency WHERE dependency_id = dependency.id AND relation_id = ".$row['id'].";";
		$count = mysql_query($query);
		$count = mysql_fetch_array($count);
		$count = $count['c'];
		$query = "UPDATE relationship SET count =".$count." WHERE id = ".$row['id'].";";
		mysql_query($query);
		echo $row['id']."
			";
	}
}

/** calculate the information carried by a dependency relationship 
I(w, r, w') = log(|w, r, w'|x|*,r,*|/|w, r, *|x|*,r,w'|)
**/
function calculateDependencyInformation(){
	$countBoth = 0;
	$countDep = 0;
	$countGov = 0;
	$countRel = 0;
	$count;
	$information = 0;
	$row = array();
	$row_gov = array();
	$row_dep = array();
	$rel_id = 0;
	$dep_id = 0;
	$gov_id = 0;
	$id = 0;
	$query = 0;
	$results = 0;
	$dep_results = 0;
	$gov_results = 0;
	$i = 0;
	while($rel_id < 368){
		echo "-----------------------".$rel_id."----------------------";
		if($rel_id != 11){
			// get rel count
			$query = "SELECT * from relationship WHERE id = ".$rel_id.";";
			$count = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
				<br/> Query: " . $query . "
				<br/> Error: (" . mysql_errno() . ") " . mysql_error());
			$count = mysql_fetch_array($count);
			$countRel = $count['count'];
			if($countRel > 0){
				$query = "SELECT id, dep_count, gov_count, frequency from dependency WHERE relation_id = ".$rel_id.";";
				$results = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
					<br/> Query: " . $query . "
					<br/> Error: (" . mysql_errno() . ") " . mysql_error());
				if(mysql_num_rows($results) > 0){
					while($row = mysql_fetch_array($results)){
						$countBoth = $row['frequency'];
						$countGov = $row['gov_count'];
						$countDep = $row['dep_count'];
						$id = $row['id'];
						if($countBoth>0){
							$i += 1;
							if($i%10000==0) echo $i."\n";
							$query = "UPDATE dependency SET information =".log(($countBoth*$countRel)/($countDep*$countGov))." WHERE id = ".$id.";" ;
							mysql_query($query) or die("<b>A fatal MySQL error occured</b>\n<br/> Query: " . $query . "\n<br/> Error: (" . mysql_errno() . ") ". mysql_error());

						}	
					}
				}
			}
		}
		$rel_id++;
	}
}


/** calculate word information **/
function calculateWordInformation(){
	$query = " SELECT * from word WHERE pos NOT IN ('X', '-LRB-', '-RRB-', '.', ',');";
	$result = mysql_query($query);
	$row = array();
	$r = array();
	$information = 0;
	while($row = mysql_fetch_array($result)){
		$query = "SELECT SUM(information) as i FROM dependency where gov_id = ".$row['id']." OR dep_id = ".$row['id']." AND information > 0;";
		$r = mysql_query($query) or die("<b>A fatal MySQL error occured</b>\n<br/> Query: " . $query . "\n<br/> Error: (" . mysql_errno() . ") ". mysql_error());
		$r = mysql_fetch_array($r);
		$information = $r['i'];
		$query = "UPDATE word SET information = ".$information." WHERE id = ".$row['id'].";" or die("<b>A fatal MySQL error occured</b>\n<br/> Query: " . $query . "\n<br/> Error: (" . mysql_errno() . ") ". mysql_error());
		$r = mysql_query($query);
		echo $row['word'],": ",$query,"\n";
	}
}

/** calculate the similarity between two words 
	only do the top 20 - 40 most similar words
**/
function calculateSimilarity(){
	// variable declarations
	$w1 = array();
	$pos;
	$id;
	$sim;
	$information1;
	$information2; 
	$numerator;
	$similarity;
	$r;
	$i = 0;
	$result2;
	//get word 1. excluding stop words, proper nouns and words that only occur once.
	/*Batch 1: < 645 - >= 300
	Batch 2: < 300  >= 25*/
	$query = "SELECT id, word.word, pos, information, sentence_frequency 
		from word, word_idf 
		WHERE pos IN ('JJ', 'JJR', 'JJS', 'NN', 'NNS', 'RB', 'RBS', 'RP', 'VB', 'VBD', 'VBG', 'VBN', 'VBP', 'VBZ') 
		AND word.id = word_id
		AND sentence_frequency >= 25
		AND word.id <= 40000
		AND word.id >= 3518
		AND sentence_frequency < 300
	  AND sentence_frequency >= 2
		ORDER BY sentence_frequency desc, word_id asc;"; 
	$result1 = mysql_query($query);
	while($w1 = mysql_fetch_array($result1)){
		$pos = $w1['pos'];
		$id = $w1['id'];
		$information1 = $w1['information'];
		if(!stopword($w1['word'] && !done($id))){
			echo $w1['sentence_frequency'],", ",$id,": ",$w1['word'], " ",$pos," ";
			// get dep numerator
			$query = "SELECT SUM(w1.information + w2.information) as numerator, w2.dep_id as word2, word.information as information2 from word,
						(SELECT gov_id, relation_id, information from dependency WHERE relation_id != 11 AND information > 0 AND dep_id = ".$id.") as w1
						JOIN 
						(SELECT gov_id, relation_id, information, dep_id from dependency WHERE relation_id != 11 AND information > 0 AND dep_id != ".$id." AND dep_pos = '".$pos."') as w2
						ON
						(w1.relation_id = w2.relation_id AND w1.gov_id = w2.gov_id) WHERE word.id = w2.dep_id GROUP BY w2.dep_id ORDER BY numerator desc;";
			$r = mysql_query($query) or die("<b>A fatal MySQL error occured</b>\n<br/> Query: " . $query . "\n<br/> Error: (" . mysql_errno() . ") ". mysql_error());
			echo "g:",mysql_num_rows($r)," d:";
			//clear any previous result
			//$query = "DELETE FROM similarity where word1_id = $id;";
			//$result2 = mysql_query($query) or die("<b>A fatal MySQL error occured</b>\n<br/> Query: " . $query . "\n<br/> Error: (" . mysql_errno() . ") ". mysql_error());
			$i = 0;
			while($sim = mysql_fetch_array($r)){
				$i += 1;
				if($i <= 100){
				//calculate similarity
				$similarity = $sim['numerator']/($information1+$sim['information2']);
				if($similarity > 0){
				$query = "INSERT IGNORE INTO similarity (word1_id, word2_id, lin_similarity) VALUES (".$id.", ".$sim['word2'].", ".$similarity.");";
				$result2 = mysql_query($query) or die("<b>A fatal MySQL error occured</b>\n<br/> Query: " . $query . "\n<br/> Error: (" . mysql_errno() . ") ". mysql_error());
				}else{
					break;
				}
				}
			}
			// get gov numerator
			$query="SELECT SUM(w1.information + w2.information) as numerator, w2.gov_id as word2, word.information as information2 from word,
			(SELECT dep_id, relation_id, information from dependency WHERE relation_id != 11 AND information > 0 AND gov_id = ".$id.") as w1
			JOIN 
			(SELECT dep_id, relation_id, information, gov_id from dependency WHERE relation_id != 11 AND information > 0 AND gov_id != ".$id." AND gov_pos = '".$pos."') as w2
			ON
			(w1.relation_id = w2.relation_id AND w1.dep_id = w2.dep_id) WHERE w2.gov_id=word.id GROUP BY w2.gov_id ORDER BY numerator desc;";
			$r = mysql_query($query) or die("<b>A fatal MySQL error occured</b>\n<br/> Query: " . $query . "\n<br/> Error: (" . mysql_errno() . ") ". mysql_error());
			echo mysql_num_rows($r),"\n";
			$i = 0;
			while($sim = mysql_fetch_array($r)){
				$i += 1;
				if($i <= 100){
				//calculate similarity
				$similarity = $sim['numerator']/($information1+$sim['information2']);
				if($similarity > 0){
				$query = "INSERT INTO similarity (word1_id, word2_id, lin_similarity) VALUES (".$id.", ".$sim['word2'].", ".$similarity.") ON DUPLICATE KEY UPDATE lin_similarity = lin_similarity + VALUES(lin_similarity);";
				$result2 = mysql_query($query) or die("<b>A fatal MySQL error occured</b>\n<br/> Query: " . $query . "\n<br/> Error: (" . mysql_errno() . ") ". mysql_error());
				}
				}else{
					break;
				}	
			}	
		}
	}
}

$stopwords = "a's, able, about, above, according, accordingly, across, actually, after, afterwards, again, against, ain't, all, allow, allows, almost, alone, along, already, also, although, always, am, among, amongst, an, and, another, any, anybody, anyhow, anyone, anything, anyway, anyways, anywhere, apart, appear, appreciate, appropriate, are, aren't, around, as, aside, ask, asking, associated, at, available, away, awfully, be, became, because, become, becomes, becoming, been, before, beforehand, behind, being, believe, below, beside, besides, best, better, between, beyond, both, brief, but, by, c'mon, c's, came, can, can't, cannot, cant, cause, causes, certain, certainly, changes, clearly, co, com, come, comes, concerning, consequently, consider, considering, contain, containing, contains, corresponding, could, couldn't, course, currently, definitely, described, despite, did, didn't, different, do, does, doesn't, doing, don't, done, down, downwards, during, each, edu, eg, eight, either, else, elsewhere, enough, entirely, especially, et, etc, even, ever, every, everybody, everyone, everything, everywhere, ex, exactly, example, except, far, few, fifth, first, five, followed, following, follows, for, former, formerly, forth, four, from, further, furthermore, get, gets, getting, given, gives, go, goes, going, gone, got, gotten, greetings, had, hadn't, happens, hardly, has, hasn't, have, haven't, having, he, he's, hello, help, hence, her, here, here's, hereafter, hereby, herein, hereupon, hers, herself, hi, him, himself, his, hither, hopefully, how, howbeit, however, i'd, i'll, i'm, i've, ie, if, ignored, immediate, in, inasmuch, inc, indeed, indicate, indicated, indicates, inner, insofar, instead, into, inward, is, isn't, it, it'd, it'll, it's, its, itself, just, keep, keeps, kept, know, knows, known, last, lately, later, latter, latterly, least, less, lest, let, let's, like, liked, likely, little, look, looking, looks, ltd, mainly, many, may, maybe, me, mean, meanwhile, merely, might, more, moreover, most, mostly, much, must, my, myself, name, namely, nd, near, nearly, necessary, need, needs, neither, never, nevertheless, new, next, nine, no, nobody, non, none, noone, nor, normally, not, nothing, novel, now, nowhere, obviously, of, off, often, oh, ok, okay, old, on, once, one, ones, only, onto, or, other, others, otherwise, ought, our, ours, ourselves, out, outside, over, overall, own, particular, particularly, per, perhaps, placed, please, plus, possible, presumably, probably, provides, que, quite, qv, rather, rd, re, really, reasonably, regarding, regardless, regards, relatively, respectively, right, said, same, saw, say, saying, says, second, secondly, see, seeing, seem, seemed, seeming, seems, seen, self, selves, sensible, sent, serious, seriously, seven, several, shall, she, should, shouldn't, since, six, so, some, somebody, somehow, someone, something, sometime, sometimes, somewhat, somewhere, soon, sorry, specified, specify, specifying, still, sub, such, sup, sure, t's, take, taken, tell, tends, th, than, thank, thanks, thanx, that, that's, thats, the, their, theirs, them, themselves, then, thence, there, there's, thereafter, thereby, therefore, therein, theres, thereupon, these, they, they'd, they'll, they're, they've, think, third, this, thorough, thoroughly, those, though, three, through, throughout, thru, thus, to, together, too, took, toward, towards, tried, tries, truly, try, trying, twice, two, un, under, unfortunately, unless, unlikely, until, unto, up, upon, us, use, used, useful, uses, using, usually, value, various, very, via, viz, vs, want, wants, was, wasn't, way, we, we'd, we'll, we're, we've, welcome, well, went, were, weren't, what, what's, whatever, when, whence, whenever, where, where's, whereafter, whereas, whereby, wherein, whereupon, wherever, whether, which, while, whither, who, who's, whoever, whole, whom, whose, why, will, willing, wish, with, within, without, won't, wonder, would, would, wouldn't, yes, yet, you, you'd, you'll, you're, you've, your, yours, yourself, yourselves, zero";
function stopword($w){
	global $stopwords;
	return strstr(" ".strtolower($w).",", $stopwords);
}

/** checks if an id is done */
function done($id){
	$query = "SELECT * from similarity where word1_id = ".$id." LIMIT 1;";
	$result = mysql_query($query);
	return(mysql_num_rows($result) > 0);
}

/** once all the lin similarities have been calculated, calculate 
synsets for easy access**/
function makeSynsets(){
	echo "MAKING SYNSETS
	";
	$query = "SELECT distinct word2_id, word 
	from similarity join word ON word2_id = id 
	WHERE word2_id >= 4625;";
	$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>\n<br/> Query: " . $query . "\n<br/> Error: (" . mysql_errno() . ") ". mysql_error());
	$row;
	$id;
	$synset;
	$result2;
	$s;
	while($row = mysql_fetch_array($result)){
		$id = $row['word2_id'];
		echo $row['word'],"
		";
		$synset = synset($id, "", "");
		foreach($synset as $s){
			$query = "INSERT IGNORE INTO synsets (word1_id, word1, word2_id, word2, similarity) 
			VALUES (".$id.", '".mysql_real_escape_string($row['word'])."', ".$s['id'].", '".mysql_real_escape_string($s['word'])."', ".$s['similarity'].");";
			$result2 = mysql_query($query) or die("<b>A fatal MySQL error occured</b>\n<br/> Query: " . $query . "\n<br/> Error: (" . mysql_errno() . ") ". mysql_error());
		}
	}
}
makeSynsets();
?>