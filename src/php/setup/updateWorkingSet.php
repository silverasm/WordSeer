#! /usr/bin/env php
<?php
include "../dbsetup.php";

$query = "SELECT * from highlight;";
$result = mysql_query($query);
while($row = mysql_fetch_array($result)){
	$query = "INSERT INTO highlight_xref_working_set (highlight_id, working_set_id) VALUES (".$row['id'].", 0);";
	mysql_query($query);
}
?>