<?php
  /**
  This file contains utitilies for finding grammatical patterns within
  two positions in a given sentence
  */
  include 'dbsetup.php';
  include 'util.php';
  $information = array();
  $sentence = array_key_exists('sentence', $_GET);
  $start = array_key_exists('start', $_GET);
  $end = array_key_exists('end', $_GET);
  if($sentence && $start && $end){
    $sentence = $_GET['sentence'];
    $start = $_GET['start'];
    $end = $_GET['end'];
    $query = "SELECT * FROM dependency, dependency_xref_sentence  WHERE sentence_id =".$sentence." AND dependency_id = dependency.id AND gov_index <=".$end." AND dep_index <= ".$end." AND gov_index >= ".$start." AND dep_index >= ".$start." ;";
    $result = mysql_query($query);
    $dependency = array();
    while($row = mysql_fetch_array($result)){
      $dependency = array();
      $dependency['id'] = $row['dependency_id'];
      $dependency['gov'] = $row['gov_index'];
      $dependency['dep'] = $row['dep_index'];
      $dependency['relation'] = $row['relationship'];
      array_push($information, $dependency);
    }
  }
  echo json_encode($information);
?>