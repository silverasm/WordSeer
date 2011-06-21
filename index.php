<!DOCTYPE html>
<html>
<head>
	<title>WordSeer: Slave Narratives Explorer</title>
	<script src="src/js/params.js" type="text/javascript"></script>
	<script src="src/js/util.js" type="text/javascript"></script>
	<script src="src/js/paging.js" type="text/javascript"></script>
	<script src="src/js/tokenize.js" type="text/javascript"></script>
	<script src="src/js/getsearchresults.js" type="text/javascript"></script>
	<script src="src/js/searchresults.js" type="text/javascript"></script>
	<script src="src/js/user.js" type="text/javascript"></script>
	<script src="lib/sorttable.js" type="text/javascript"></script>
	<script src="lib/raphael.js" type="text/javascript"></script>
	<script src="lib/json2.js" type="text/javascript"></script>
	<script src="lib/jquery/jquery.js" type="text/javascript"></script>
	<script src="lib/jquery/jquery.scrollTo.js" type="text/javascript"></script>
	<script src="lib/jquery/jquery.url.js" type="text/javascript"></script>
	<script src="lib/jquery/jquery.ui.js" type="text/javascript"></script>
	<script src="lib/protovis-3.2/protovis-r3.2.js" type="text/javascript"></script>
	<!--[if IE]><script src="lib/excanvas.js"></script><![endif]--> 
	<link rel='stylesheet' href="style/jquery-ui-smoothness.css">
	<link rel='stylesheet' href="style/fonts.css">
	<link rel='stylesheet' href="style/bubbles.css">
	<link rel='stylesheet' href="style/searchresults.css">
	<script type="text/javascript">
	$('document').ready(init)
	</script>
</head>
<body>
	<div id="wrapper">
	<nav>
		<ul>
		<li class="menu"><a href="http://eecs.berkeley.edu/~aditi/projects/wordseer.html">About</a></li>
		<li class="menu"><a href="index.php">Search</li>
		<li class="menu"><a href="view.php">Read and Annotate</li>
		<li class="menu"><a href="heatmap.php">Heat Maps</a></li>
		<li id="examples-menu" class="menu"> Examples </li>
		<ul class="examples">
			<li class="submenu"><a href="http://bebop.berkeley.edu/wordseer/index.php?grammatical=on&gov=&relation=agent+subj+nsubj+csubj+nsubjpass+csubjpass&dep=God&results=&page=0&pagelength=100">God</a></li>
			<li class="submenu"><a href="http://bebop.berkeley.edu/wordseer/index.php?grammatical=on&gov=&relation=obj+dobj+iobj+pobj&dep=child+children+daughter+son&results=&page=0&pagelength=100">Children</a></li>
			<li class="submenu"><a href="http://bebop.berkeley.edu/wordseer/index.php?grammatical=on&gov=&relation=amod+advmod&dep=cruel&results=&page=0&pagelength=100">Cruelty</a></li>
			<li class="submenu"><a href="http://bebop.berkeley.edu/wordseer/index.php?grammatical=on&gov=escaped+escape+ran&relation=prep_to&dep=&results=&page=0&pagelength=100">Escape</a></li>
		</ul></li>
		<li class="menu" id="user"> </li>
	</ul>
	</nav>
	<div id="debug"></div>
	<div id="header">
		<a href="index.php"><img class="logo" src="img/wordseer.png"></a>
		<h1 class="title"> Search Slave Narratives</h1>  
		<div id="search-form">
			<form name="grammatical" class="search" action="index.php">
				<fieldset id="grammatical">
					<label id="help-text" style="display:none">Type in words to get matching sentences, or leave blank to see what matches.</label>
					<input class="hidden-input" type="checkbox" name="grammatical" checked="checked">
					<br>
					<input id="gov" type="text" name="gov" 
					<?php 
				if($_GET['relation']=="none"){
					echo "value='".str_replace("  ", " ", str_replace('\"','"', $_GET["gov"]))."'";
					} else if($_GET['gov']){
						echo 'value="'.$_GET['gov'].'"';
					}else{ 
						echo 'value=""';
					} 
					?>></input>
					<select name="relation" class="select">
						<option class="option" value="none" <?php if($_GET['relation']=="none"){echo 'selected="selected"';} ?> >search</option>
						<option class="option" value="" <?php if($_GET['relation']==""){echo 'selected="selected"';} ?> >(any relation to)</option>
						<option class="option" value="amod advmod" <?php if($_GET['relation']=="amod advmod"){echo 'selected="selected"';} ?> >described as</option>
						<option class="option" value="agent subj nsubj csubj nsubjpass csubjpass" <?php if($_GET['relation']=='agent subj nsubj csubj nsubjpass csubjpass'){echo 'selected="selected"';} ?> >done by</option>
						<option class="option" value="obj dobj iobj pobj" <?php if($_GET['relation']=='obj dobj iobj pobj'){echo 'selected="selected"';} ?> >done to</option>
						<option class="option" value="prep_because prep_because_of prep_on_account_of prep_owing_to prepc_because prepc_because_of prepc_on_account_of prepc_owing_to" <?php if($_GET['relation']=='prep_because prep_because_of prep_on_account_of prep_owing_to prepc_because prepc_because_of prepc_on_account_of prepc_owing_to'){echo 'selected="selected"';} ?> >because</option>
						<option  class="option"value="conj_and" <?php if($_GET['relation']=='conj_and'){echo 'selected="selected"';} ?> >and</option>
						<option class="option" value="purpcl"  <?php if($_GET['relation']=='purpcl'){echo 'selected="selected"';} ?>> in order to </option>
						<option class="option" value="prep_with prepc_with prep_by_means_of prepc_by_means_of" <?php if($_GET['relation']=='prep_with prepc_with prep_by_means_of prepc_by_means_of'){echo 'selected="selected"';} ?> >with</option>
						<option class="option" value="prep_to" <?php if($_GET['relation']=='prep_to'){echo 'selected="selected"';} ?> >to</option>
						<option class="option" value="prep_from" <?php if($_GET['relation']=='prep_from'){echo 'selected="selected"';} ?> >from</option>

						<option class="option" value="prep_of" <?php if($_GET['relation']=='prep_of'){echo 'selected="selected"';} ?> >of</option>
						<option class="option" value="prep_on" <?php if($_GET['relation']=='prep_on'){echo 'selected="selected"';} ?> >on</option>

						<option class="option" value="prep_by" <?php if($_GET['relation']=='prep_by'){echo 'selected="selected"';} ?> >by</option>
						<option  class="option"value="prep_in" <?php if($_GET['relation']=='prep_in'){echo 'selected="selected"';} ?> >in</option>

						<option class="option" value="poss" <?php if($_GET['relation']=='poss'){echo 'selected="selected"';} ?> >possessed by</option>
					</select>
					<input id="dep" type="text" name="dep" <?php if($_GET['dep']){echo 'value="'.$_GET['dep'].'"';} else{echo 'value=""';} ?> ></input>

					<input type="text" class="hidden-input" name="results" value="">
					<input type="text" class="hidden-input" name="page" <?php if($_GET['page']){echo 'value="'.$_GET['page'].'"';}else{echo 'value="0"';}?>>
					<input type="text" class="hidden-input" name="pagelength" value="100">

					<input type="submit" value="Go" class="button"></input>
				</fieldset>
			</form>
		</div>
	</div>
	<div id="random">
	</div>

	<div id="frequencies">
		<div class="graph" id="relationship-frequency"></div>
		<div class="graph" id="gov-frequency"></div>
		<div class="graph" id="dep-frequency"></div>
	</div>
	<div id="search-results">
		<form id="submit-results" action="view.php">
			<?php
		//includes
		include 'src/php/dbsetup.php';
		include 'src/php/randomsentence.php';
		include 'src/php/util.php';

		// query processing
		$q = str_replace('\"', '"', $_GET['q']);
		$q = str_replace("  ", " ", $q);
		$query = ""; 
		$toHighlight = array();   
		//what kind of query
		$fulltext = ($_GET['fulltext'] == 'on' || $_GET['relation']=="none");
		$grammatical = ($_GET['grammatical'] == 'on' && $_GET['relation']!="none");
		$sequence = ($_GET['sequence']=="on");
		if($_GET['relation']=="none"){
			$q = str_replace('\"', '"', $_GET['gov']);
		}
		//what is the scope of results?
		$withinSentence = ($_GET['within']=='sentence');
		$withinNarrative = ($_GET['within']=='narrative');
		$within = numberList($_GET['results']);
		//paging
		$page = $_GET['page'];  
		$pageLength = $_GET['pagelength'];
		//if the query is a sequence
		if($sequence){
			$lhs = $_GET['lhs'];
			$rhs = explode(' ', $_GET['rhs']);
			$distance = $_GET['distance'];
			$toHighlight = $rhs;
			$pos_list = array();
			if(strlen($_GET['rhs'])>0){
				$rhs_ids = "";
				foreach($rhs as $rh){
					$rhs_ids += " ".getWordID($rh);
				}
				if($lhs != "anything"){
					$pos_list = listFormat($lhs);
				}
				$rhs_ids = numberList($rhs_ids);
				if($distance == 'sentence'){
					if($lhs=="anything"){             
						$query = "SELECT sentence_id FROM sentence_xref_word WHERE word_id in (".$rhs_ids."); ";
						$sentence_ids = mysql_
							($query);
					}
					else{             
						$query = "SELECT sentence_id, word as lhs FROM word, sentence_xref_word WHERE word_id in (".$rhs_ids.") AND lhs.id = sentence_xref_word.word_id AND word.pos in (".$pos_list.");";
						$sentence_ids = mysql_query($query);
					}
				}
				else{            
					if($lhs == "anything"){              
						$query = "SELECT s.sentence_id, s.word_id as lhsid, s.position as lhspos from sentence_xref_word as s, (SELECT sentence_id, position from sentence_xref_word WHERE word_id in (".$rhs_ids.")) AS positions WHERE
							s.sentence_id = positions.sentence_id AND
							(positions.position - s.position)*(positions.position - s.position) <=".pow(intval($distance),2).";";
						$sentence_ids = mysql_query($query);
					}
					else{          
						$query = "SELECT s.sentence_id, s.word_id as lhsid, s.position as lhspos FROM 
						(SELECT * from sentence_xref_word, word as lh WHERE lh.pos in  (".$pos_list.") ) as s,
						(SELECT sentence_id, position from sentence_xref_word WHERE word_id in (".$rhs_ids.")) AS positions
						WHERE  s.sentence_id = positions.sentence_id AND
						(positions.position - s.position)*(positions.position - s.position) <=".pow(intval($distance),2).";";
					$sentence_ids = mysql_query($query);
				}
			}
			echo $query;
			$count += mysql_num_rows($sentence_ids);
			if($count > 0){
				echo '<h3 class="num-results">'.$count;
				if($count==1){
					echo ' result';
				}else{
					echo ' results';
				}
				echo '</h3>';
				echo '<input class="view-button" type="submit" value="View">';
				//paging
				if($page > 0){
					echo '<input id="prev-page" value="Previous" class="view-button" type ="button" thispage="'.$page.'" pagelength="'.$pageLength.'">';
				}
				if($page < $count/$pageLength){
					echo '<input id="next-page" value="Next" class="view-button" type="button" thispage="'.$page.'" pagelength="'.$pageLength.'">';
				}
				echo '<table class="search-results sortable">';
				echo '<tr class="search-result-header">';
				echo '<td class="select-all-checkbox">';
				echo '<input id="select-all-checkbox" type="checkbox" name="select-all""></td>';
				echo '<td class="header" id="sentence"> Sentence</td>';
				echo '<td class="header" id="paragraph-type"> Type</td>';
				echo '<td class="header" id="title"> Title</td>';
				echo '<td class="header" id="full"> Author</td>';
				echo '<td class="header" id="date"> Date</td>';
				echo '<td class="header" id="publisher"> Publisher</td>';
				echo '<td class="header" id="pubPlace"> Place Published </td>';
				echo '</tr>';
				$numResults = 0;
				while($sent_info = mysql_fetch_array($sentence_ids) && numResults < 50){
					$numResults += 1;
					$sent_id = $sent_info["sentence_id"];
					$metadata = getMetadta($sentenceID);
					$row2 = mysql_fetch_array($metadata);
					$highlightPositions = array();
					$toHighlight = array();
					if($sent_info["lhspos"]){
						array_push($highlightPositions, $sent_info["lhspos"]);
					}
					if($sent_info["lhs"]){
						array_push($toHighlight, $sent_info["lhs"]);
					}
					if(strlen($row2['sentence'])>0){
						echo '<tr class="search-result">';
						// hiddent inputs
						echo '<td class="hidden-id">';
						echo '<input class="hidden-id" name="id" type="checkbox" value="'.$row2['id'].'_'.$sent_id.'">';
						echo '</td>';
						// rest of result
						echo '<td>';
						$index = 0;
						foreach(explode(' ', $row2['sentence']) as $word){
							$highlight = indexOf(strtolower($word), $toHighlight);
							if($highhlight <0){
								$highlight = indexOf($index, $highlightPositions);
							}
							if($highlight >= 0){
								echo '<span class="highlight'.($highlight%10).'">'.$word.' </span>';
							}else{
								echo $word.' ';
							}
							$index += 1;
						}
						echo'</td>';
						echo '<td>'.$row2['type'].'</td>';
						echo '<td>'.$row2['title'].'</td>';
						echo '<td>'.$row2['full'].'</td>';
						echo '<td>'.$row2['date'].'</td>';
						echo '<td>'.$row2['publisher'].'</td>';
						echo '<td>'.$row2['pubPlace'].'</td>';
						echo '</tr>';
					}
				}
				echo '</table>';
				echo '<input class="view-button" type="submit" value="View">';
			}else{
				echo "No results found!";
			}    
		}
	}
	//if the query is a grammatical search
	else if($grammatical){   
		$gov = str_replace('\"', '"', $_GET['gov']);
		$dep = str_replace('\"', '"', $_GET['dep']);
		$relationship = str_replace('\"', '"', $_GET['relation']);
		$log = fopen("/projects/wordseer/logs/query.log", "a");
		$data = 'gov='.$gov.' dep='.$dep.' relation='.$relation.' ';
		fwrite($log, $data);
		fclose($log);
		$count = 0; 
		$sentence_dependencies = array();
		$govs = wordIDList($gov);
		$deps = wordIDList($dep);
		$rels = relationshipIDList($relationship);
		$dependency_ids = getDependencyIDs($govs, $deps, $rels, $withinNarrative, $withinSentence, $within, true);
		$count = mysql_num_rows($dependency_ids);
		// print out graph data in JSON format
		if ($count > 0){
			//printStatistics(old_getStatistics($dependency_ids));
			printStatistics(getStatistics($deps, $govs, $rels));
			mysql_data_seek($dependency_ids,0); 
		}
		//print out search results
		if($count>0){
			echo '<h3 class="num-results">'.$count;
			if($count==1){
				echo ' result';
			}else if ($count < $pageLength){
				echo ' results';
			}else{
				echo ' results, '.$pageLength.' results per page, page '.strval(intval($page)+1).' of '.strval(intval($count/$pageLength)+1).' ';
			}
			echo '</h3>';
			echo '<input class="view-button button" type="submit" value="View" style="display:none">';
			//paging
			if($page > 0){
				echo '<input class="button" id="prev-page" value="Previous" class="button" type ="button" thispage="'.$page.'" pagelength="'.$pageLength.'">';
			}
			if($page < $count/$pageLength-1){
				echo '<input id="next-page" value="Next" class="button" type="button" thispage="'.$page.'" pagelength="'.$pageLength.'">';
			}
			echo '<table class="search-results sortable">';
			echo '<tr class="search-result-header">';
			echo '<td class="select-all-checkbox"> View</td>';
			echo '<td class="header" id="sentence"> Sentence</td>';
			echo '<td class="header" id="paragraph-type"> Type</td>';
			echo '<td class="header" id="title"> Title</td>';
			echo '<td class="header" id="full"> Author</td>';
			echo '<td class="header" id="date"> Date</td>';
			echo '<td class="header" id="publisher"> Publisher</td>';
			echo '<td class="header" id="pubPlace"> Place Published </td>';
			echo '</tr>';

			mysql_data_seek($dependency_ids,0); 
			// for each sentence ID associated with the matching dependencies
			// fetch metadata and print it out
			$counter = 0;
			while($row=mysql_fetch_array($dependency_ids)){
				$counter += 1;
				// only display( the correct page
					if($counter <= $pageLength*(1+$page)  && $counter > $pageLength*$page){
						$toHighlight = explode(' ', strtolower($row['dep'].' '.$row['gov']) );
						$sent_id = $row['sentence_id'];
						$metadata = getMetadata($sent_id);
						$row2 = mysql_fetch_array($metadata);
						if(strlen($row2['sentence'])>0){
							echo '<tr class="search-result">';
							//hidden inputs
							echo '<td class="hidden-id">';
							echo '<a href="view.php?id='.$row2['id'].'_'.$sent_id.'"><img class="view" src="img/view.png" dep="'.$row['id'].'" value="'.$row2['id'].'_'.$sent_id.'">';
							echo '</a></td>';
							// rest of result
							echo '<td>';
							foreach(explode(' ', $row2['sentence']) as $word){
								$highlight = indexOf(strtolower($word), $toHighlight);
								if($highlight >= 0 or strtolower($word)==strtolower($row['gov']) or strtolower($word)==strtolower($row['dep'])){
									echo '<span class="highlight'.($highlight%10).'">'.$word.' </span>';
								}else{
									echo $word.' ';
								}
							}
							echo'</td>';
							echo '<td>'.$row2['type'].'</td>';
							echo '<td>'.$row2['title'].'</td>';
							echo '<td>'.$row2['full'].'</td>';
							echo '<td>'.$row2['date'].'</td>';
							echo '<td>'.$row2['publisher'].'</td>';
							echo '<td>'.$row2['pubPlace'].'</td>';
							echo '</tr>';
						}
					}          
				}
				echo '</table>';
				echo '<input class="view-button button" type="submit" value="View">';
			} else{
				echo "No results found!";
			}
		}
		// if the query is a text search
		if($fulltext){
			// SQL: if it is within the current results or current narratives
			if(!$withinSentence && !$withinNarrative){
				$query = "SELECT DISTINCT(sent_id), sentence, title, publisher, full, pubPlace, para.narrative_id as id, paragraph.id as pid, type, date 
					FROM paragraph 
					JOIN
				(SELECT DISTINCT(sent_id), sentence, title, publisher, full, pubPlace, N.narrative_id, date, paragraph_id as id
			FROM (author 
				JOIN author_xref_narrative 
			ON author.id = author_xref_narrative.author_id) 
		JOIN(SELECT distinct(sent_id), sentence, title, publisher, pubPlace, narrative_id, paragraph_id, date 
		from narrative JOIN  
	(SELECT DISTINCT id as sent_id, sentence, narrative_id, paragraph_id 
		from sentence
	WHERE MATCH(sentence) AGAINST('".$q."' IN BOOLEAN MODE)) as S 
	ON narrative.id = S.narrative_id) as N
	ON author_xref_narrative.narrative_id = N.narrative_id) AS para
	ON paragraph.id = para.id ;";
}else if ($withinSentence){
	$query = "SELECT DISTINCT sent_id, sentence, title, publisher, full, pubPlace, para.narrative_id as id, paragraph.id as pid, type, date 
		FROM paragraph 
		JOIN
	(SELECT sent_id, sentence, title, publisher, full, pubPlace, N.narrative_id, date, paragraph_id as id
	FROM (author 
		JOIN author_xref_narrative 
	ON author.id = author_xref_narrative.author_id) 
JOIN(SELECT sent_id, sentence, title, publisher, pubPlace, narrative_id, paragraph_id, date 
	from narrative JOIN  
(SELECT DISTINCT id as sent_id, sentence, narrative_id, paragraph_id 
	from sentence
WHERE MATCH(sentence) AGAINST('".$q."' IN BOOLEAN MODE)
AND sentence.id in (".$within.")
) as S 
ON narrative.id = S.narrative_id) as N
ON author_xref_narrative.narrative_id = N.narrative_id) AS para
ON paragraph.id = para.id LIMIT 500;";
}else if ($withinNarrative){
	$query = "SELECT DISTINCT sent_id, sentence, title, publisher, full, pubPlace, para.narrative_id as id, paragraph.id as pid, type, date 
		FROM paragraph 
		JOIN
	(SELECT sent_id, sentence, title, publisher, full, pubPlace, N.narrative_id, date, paragraph_id as id
	FROM (author 
		JOIN author_xref_narrative 
	ON author.id = author_xref_narrative.author_id) 
JOIN(SELECT sent_id, sentence, title, publisher, pubPlace, narrative_id, paragraph_id, date 
FROM (SELECT * from narrative WHERE id in (".$within.") ) as narr JOIN  
(SELECT DISTINCT id as sent_id, sentence, narrative_id, paragraph_id 
	from sentence
	WHERE MATCH(sentence) AGAINST('".$q."' IN BOOLEAN MODE) ) as S 
	ON narr.id = S.narrative_id) as N
	ON author_xref_narrative.narrative_id = N.narrative_id) AS para
	ON paragraph.id = para.id LIMIT 500;";
}
$toHighlight = explode(' ', strtolower(str_replace('"', '', $q)));
}else{
	if(!$withinSentence && !$withinNarrative){
		$query = "SELECT DISTINCT title, full, date, pubPlace, publisher, narrative.id as id from 
		((author JOIN author_xref_narrative 
		ON author.id = author_xref_narrative.author_id) 
		JOIN narrative 
	ON author_xref_narrative.narrative_id = narrative.id)  
WHERE MATCH(full, title, pubPlace, publisher) AGAINST('".$q."' IN BOOLEAN MODE);";
}else if ($withinNarrative){
	$query = "SELECT title, full, date, pubPlace, publisher, narrative.id as id from 
	((author JOIN author_xref_narrative 
	ON author.id = author_xref_narrative.author_id) 
	JOIN narrative 
ON author_xref_narrative.narrative_id = narrative.id)  
WHERE narrative.id in (".$within.") AND MATCH(full, title, pubPlace, publisher) AGAINST('".$q."' IN BOOLEAN MODE);";
}
$toHighlight = explode(' ', str_replace('"', '', $q));
}
if(!$grammatical && strlen($q)>0){
	//echo $query;
	$result = mysql_query($query);
	$count = mysql_num_rows($result);
	if($count>0){
		echo '<h3 class="num-results">'.$count;
		if($count==1){
			echo ' result';
		}else if ($count <= $pageLength){
			echo ' results';
		}else{
			echo ' results, '.$pageLength.' results per page, page '.strval(intval($page)+1).' of '.strval(intval($count/$pageLength)+1).' ';
		}
		echo '</h3>';
		echo '<input class="view-button button" type="submit" value="View">';
		//paging
		if($page > 0){
			echo '<input class="button" id="prev-page" value="Previous" class="button" type ="button" thispage="'.$page.'" pagelength="'.$pageLength.'">';
		}
		if($page < $count/$pageLength-1){
			echo '<input class="button" id="next-page" value="Next" class="view-button" type="button" thispage="'.$page.'" pagelength="'.$pageLength.'">';
		}
		echo '<table class="search-results sortable">';
		echo '<tr class="search-result-header">';
		echo '<td class="select-all-checkbox"> View</td>';
		if($fulltext && strlen(q)>0){
			echo '<td class="header" id="sentence"> Sentence</td>';
			echo '<td class="header" id="paragraph-type"> Type</td>';
		}
		echo '<td class="header" id="title"> Title</td>';
		echo '<td class="header" id="full"> Author</td>';
		echo '<td class="header" id="date"> Date</td>';
		echo '<td class="header" id="publisher"> Publisher</td>';
		echo '<td class="header" id="pubPlace"> Place Published </td>';
		echo '</tr>';
		$highlight = false;
		$sentence_ids = array();
		$counter = 0;
		while($row = mysql_fetch_array($result)){
			$counter += 1;
			if($counter <= (1+$page)*$pageLength && $counter > $pageLength*$page){
				echo '<tr class="search-result">';
				echo '<td class="hidden-id">';
				echo '<a href="view.php?id='.$row['id'].'_'.$row['sent_id'].'"><img class="view" src="img/view.png" value="'.$row['id'].'_'.$row['sent_id'].'">';
				echo '</a></td>';
				if($fulltext){
					echo '<td>';
					foreach(explode(' ', $row['sentence']) as $word){
						$highlight = indexOf(strtolower($word), $toHighlight);
						if($highlight >= 0){
							echo '<span class="highlight'.($highlight%10).'">'.$word.' </span>';
						}else{
							echo $word.' ';
						}
					}
					echo'</td>';
					echo '<td>'.$row['type'].'</td>';
				}
				echo '<td>'.$row['title'].'</td>';
				echo '<td>'.$row['full'].'</td>';
				echo '<td>'.$row['date'].'</td>';
				echo '<td>'.$row['publisher'].'</td>';
				echo '<td>'.$row['pubPlace'].'</td>';
				echo '</tr>';
			}
		}
		echo '</table>';
		echo '<input class="view-button" type="submit" value="View">';

	}else{
		echo 'no search results found!';
	}
}
mysql_close();
?>
</form>
</div>
</div>
</body>
</html>

