<!DOCTYPE html>
<html>
<head>
<title>Slave Narratives Explorer (Prototype)</title>
<script src="src/js/view.js" type="text/javascript"></script>
<script src="src/js/util.js" type="text/javascript"></script>
<script src="src/js/visualize.js" type="text/javascript"></script>
<script src="src/js/search.js" type="text/javascript"></script>
<script src="src/js/save.js" type="text/javascript"></script>
<script src="src/js/annotate.js" type="text/javascript"></script>
<script src="src/js/user.js" type="text/javascript"></script>
<script src="src/js/viewinteractions.js" type="text/javascript"></script>
<script src="src/js/params.js" type="text/javascript"></script>
<script src="lib/sorttable.js" type="text/javascript"></script>
<script src="lib/raphael.js" type="text/javascript"></script>
<script src="lib/json2.js" type="text/javascript"></script>
<script src="lib/jquery/jquery.js" type="text/javascript"></script>
<script src="lib/jquery/jquery.scrollTo.js" type="text/javascript"></script>
<script src="lib/jquery/jquery.url.js" type="text/javascript"></script>
<script src="lib/jquery/jquery.ui.js" type="text/javascript"></script>
<script src="lib/jquery/jquery.hoverintent.js" type="text/javascript"></script>
<script src="lib/jquery/jquery.rightClick.js" type="text/javascript"></script>
<script src="lib/protovis-3.2/protovis-r3.2.js" type="text/javascript"></script>
<link rel='stylesheet' href="style/jquery-ui-smoothness.css">
<link rel="stylesheet" href="style/bubbles.css">
<link rel='stylesheet' href="style/fonts.css">
<link rel='stylesheet' href="style/searchresults.css">
<link rel='stylesheet' href="style/default.css">
<script type="text/javascript">
$(window).load(init); // in src/js/view.js
</script>
</head>
<body>
<?php include 'src/php/dbsetup.php';?>
<div id="wrapper">
	<!--<div id="savePattern" class="dialog overlay"> 
		<div class="dialog-controls-top">
			<h3 class="windowtitle">Detected Patterns </h3>
			<img id="closeDialog" class="button close" src="img/close.png">
		</div>
		<div class="textpattern"></div>
		<div class="listing"> </div>
		<div class="dialog-controls-bottom">
			<form class="dialog">
				<input type="button" name="save" value="Save">
				<input type="button" name="search" value = "Search">
				<input type="button" name="within" value = "Search within results">
				<input type="button" name="visualize" value="See in text">
				<input type="button" class="close" name="close" value="Close">
			</form>
		</div>
	</div> !-->
	<nav>
		<ul>
			<li class="menu"><a href="http://eecs.berkeley.edu/~aditi/projects/wordseer.html">About</a></li>
			<li class="menu"><a href="index.php">Search</a></li>
			<li class="menu"><a href="view.php">Read and Annotate</a></li>
			<li class="menu"><a href="heatmap.php">Heat Maps</a></li>
			<li class="menu" id="user"> </li>
		</ul>
	</nav>
	<div id="debug"></div>
	<div id="header"> 
		<a href="index.php"><img class="logo" src="img/wordseer.png"></a>
		<h1 class="title"> View Slave Narratives</h1>
	</div>
	<div id="tabs">
		<h2 panel="listing" class="tabs <?php if(!$_GET['id']){echo 'selected';}else{echo 'unselected';}?>">Select a narrative</h2> 
		<h2 panel ="viewing" class="tabs <?php if($_GET['id']){echo 'selected';}else{echo 'unselected';}?>">Read and Annotate</h2> 
		<h2 panel="annotations" class="tabs unselected">View Annotations</h2>
	</div>
	<div id="listing" <?php if($_GET['id']){echo 'class="hidden"';}?>>
	</div>
	<div id="viewing" <?php if(!$_GET['id']){echo 'class="hidden"';}?>>
	<div id="annotate" class="dialog triangle-border left">
			<img class="close button" src="img/close.png">
			<h3>Annotate</h3>
			<form id="submit-annotation">
				<label class="title"><img class="icon" src="img/note.png"> Note</label>
				<textarea name="node" class="note"></textarea>
				<label><img class="icon" src="img/tag.png"> Tags (comma separated)</label>
				<input name="tags" class="tags autocomplete"></input>
				<input type="submit" value="OK"></input>
			</form>
		</div>
		<div id="display-annotations"></div>
		<div id="heat-map-query" class="dialog triangle-border left">
			<img class="close button" src="img/close.png">
			<h3> Heat Map Query </h3>
			<ul class="heat-map-words"></ul>
			<a href="" target="_blank"><input type="button" value="Go"></input></a>
		</div>
		<div id = "related-words-container" class="dialog triangle-border top">
			<img class="close button" src="img/close.png">
			<h4>Related Words</h4>
			<div id="related-words"></div>
		</div>
		<?php
		$query  = explode('&', $_SERVER['QUERY_STRING']);
		$params = array();
		foreach( $query as $param )
		{
			list($name, $value) = explode('=', $param);
			$params[urldecode($name)][] = urldecode($value);
		}
		$allIDs = $params['id'];
		$components = array();
		foreach($allIDs as $ids){
			$components = explode("_", $ids);
			$id = $components[0];
			$sent_id = intval($components[1]);
			$lowerlimit = max(0, $sent_id-250);
			$upperlimit = $sent_id+250;
			echo '<div class="narrative" id="'.$id.'">';
			// title
			$metadata = "SELECT title, authorLast, authorRest FROM (author 
				JOIN
			(SELECT * 
				FROM author_xref_narrative 
				JOIN 
			(SELECT * FROM
			narrative where id = ".$id.") as N
			ON author_xref_narrative.narrative_id = N.id) as NN
			ON author.id = NN.author_id);";
		$result = mysql_query($metadata);
		$row = mysql_fetch_array($result);
		echo '<div class="metadata">';
		echo '<div class="title"><h2>'.$row['title'].'</h2></div>';
		echo '<div class="author"><h5>'.$row['authorRest'].' '.$row['authorLast'].'</h5></div>';
		echo '	<div class="controls">';
		echo '  <img class="highlight pattern-control" src="img/highlight.png">';
		//echo '	<img class="tag pattern-control" src="img/tag.png" >';
		//echo '	<img class="note pattern-control" src="img/note.png" >';
		echo '	<img class="save pattern-control" src="img/info.png" >';
		echo '	</div>';
		echo '</div>';
		echo '<div class="contents">';
		echo '<h2> Contents </h2>';
		echo '<ul class="section-nav"></ul>';
		echo '</div>';
		// words
		$wordQuery = "SELECT sentence_id, paragraph_id, word, position, word_id, type from paragraph join (SELECT sentence_id, paragraph_id, word, position, word_id from 
		(word JOIN 
		(SELECT word_id, sentence_id, paragraph_id, position 
			from sentence_xref_word ";
		if(count($components) == 2){
			$wordQuery = $wordQuery." JOIN (SELECT id, paragraph_id from sentence where narrative_id = ".$id." AND id <= ".$upperlimit." AND id >= ".$lowerlimit.") as S";
		}else{
			$wordQuery = $wordQuery." JOIN (SELECT id, paragraph_id from sentence where narrative_id = ".$id.") as S";
		}
		$wordQuery = $wordQuery." ON S.id = sentence_id
			ORDER BY sentence_id, position) AS WS
			ON word.id = WS.word_id)) AS wds 
			ON (paragraph.id = wds.paragraph_id);";
		//echo $wordQuery;
		$result = mysql_query($wordQuery);
		$oldParagraph = 0;
		$oldType = "";
		$oldSentence = 0;
		echo '<div class="sentence-visual"></div>';
		echo '<div class="words">';
		while($row = mysql_fetch_array($result)){
			$paragraph = $row['paragraph_id'];
			$sentence = $row['sentence_id'];
			$type = $row['type'];
			if($type != $oldType){
				$oldType = $type;
				echo '<h3 class="section-type"><a class="section-type">'.$row['type'].'</a></h3>';
			}
			if($paragraph != $oldParagraph){
				$oldParagraph = $paragraph;
				echo '</p><p class="paragraph '.$row['type'].'" paragraph="'.$row['paragraph_id'].'">';
			}
			if($sentence != $oldSentence){
				$oldSentence = $sentence;
				if($sentence == $sent_id){
					echo '</span><span class="sentence searched" id="'.$sentence.'">';
				}else{
					echo '</span><span class="sentence" id="'.$sentence.'"> ';
				}
			}
			echo '<span class="word" narrative="'.$id.'" sentence="'.$row['sentence_id'].'" word="'.$row['word_id'].'" position="'.$row['position'].'"> '.$row['word'].'</span>';

		}
		echo '</div>';// end of div.words
		// navigation buttons
		echo '<div class="navigation">';
		echo '<input type="button" narrative="'.$id.'" name="f" value="|<">';
		echo '<input type="button" narrative="'.$id.'" name="p" value="<">';
		echo '<input type="button" narrative="'.$id.'" name="n" value=">">';
		echo '<input type="button" narrative="'.$id.'" name="l" value=">|">';
		echo '</div>';//end of navigation
		echo '</div>';// end of narrative
	}
	?>
	</div>
	<div id="annotations" class="hidden">
	</div>
</div>
</div>
</body>
</html>
