<?php
/**
	listnarratives.php
	Called by view.js in service of view.php
	Lists all the narratives in a table
**/
include 'dbsetup.php';
include 'util.php';

	$query = "SELECT * from narrative, author_xref_narrative, author WHERE narrative.id = narrative_id and author_id = author.id order by date;";
	$results = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
		<br/> Query: " . $q . "
		<br/> Error: (" . mysql_errno() . ") " . mysql_error());
	echo '<table class="sortable listing">';
	echo '<tr><td>'.mysql_num_rows($results).'</td><td class="column-name">Title</td><td>Tags</td><td class="column-name">Length (sents)</td><td class="column-name">Date</td><td class="column-name">Author</td></tr>';
	while($row = mysql_fetch_array($results)){
		$query = "SELECT name from tag, highlight_xref_tag WHERE tag.id = tag_id AND narrative = ".$row['narrative_id']." ORDER BY name;"  or die("<b>A fatal MySQL error occured</b>.
			<br/> Query: " . $insert . "
			<br/> Error: (" . mysql_errno() . ") " . mysql_error());
		$tags = mysql_query($query);
		$narrativeTags = array();
		while($tag = mysql_fetch_array($tags)){
			array_push($narrativeTags, $tag['name']);
		}
		$narrativeTags = join(", ", $narrativeTags);
		echo '<tr>';
		echo '<td><a href="view.php?id='.$row['narrative_id'].'"><img src="img/view.png"></a></td><td>'.$row['title'].'</td><td>'.$narrativeTags.'</td><td>'.$row['sentence_count'].'</td><td>'.$row['date'].'</td><td>'.$row['full'].'</td></tr>';
		echo '</tr>';
	}
	echo '</table>';
?>