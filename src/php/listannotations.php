<?php
/**
 listannotations.php
 called by listAnnotations() in annotate.js (in service of view.js) to display a list of annotations.
**/
include 'dbsetup.php';
include 'util.php';

//notes
$query = "SELECT * from narrative, author, author_xref_narrative, highlight, note, highlight_xref_note WHERE narrative.id = highlight.narrative_id AND highlight_xref_note.highlight_id = highlight.id AND note_id = note.id AND author.id = author_id AND author_xref_narrative.narrative_id = narrative.id ORDER BY narrative.id;";
$results = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
	<br/> Query: " . $q . "
	<br/> Error: (" . mysql_errno() . ") " . mysql_error());
echo '<h3>Notes</h3>';
echo '<table id="notes-listing" class="sortable listing">';
echo '<tr><td>'.mysql_num_rows($results).'</td><td class="column-name">Note</td><td class="column-name">Creator</td><td class="column-name">Title</td></tr>';
while($row = mysql_fetch_array($results)){
	echo '<tr>';
	echo '<td><a href="view.php?id='.$row['narrative_id'].'_'.$row['start'].'"><img src="img/view.png"></a></td><td>'.$row['text'].'</td><td>'.$row['user'].'</td><td>'.$row['title'].'</td></tr>';
	echo '</tr>';
}
echo '</table>';

//tags
$query = "SELECT * from narrative, author, author_xref_narrative, highlight, tag, highlight_xref_tag WHERE narrative.id = highlight.narrative_id AND highlight_xref_tag.highlight_id = highlight.id AND tag_id = tag.id AND author.id = author_id AND author_xref_narrative.narrative_id = narrative.id ORDER BY narrative.id;";
$results = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.
	<br/> Query: " . $q . "
	<br/> Error: (" . mysql_errno() . ") " . mysql_error());
echo '<h3>Tags</h3>';
echo '<table id="tags-listing" class="sortable listing">';
echo '<tr><td>'.mysql_num_rows($results).'</td><td class="column-name">Tags</td><td class="column-name">Creator</td><td class="column-name">Title</td></tr>';
while($row = mysql_fetch_array($results)){
	echo '<tr>';
	echo '<td><a href="view.php?id='.$row['narrative_id'].'_'.$row['start'].'"><img src="img/view.png"></a></td><td>'.$row['name'].'</td><td>'.$row['user'].'</td><td>'.$row['title'].'</td></tr>';
	echo '</tr>';
}
echo '</table>';
?>