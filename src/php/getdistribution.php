<?php
/**
THIS SCRIPT CONTAINS UTILITIES FOR FINDING ALL THE OCCURRENCES
OF GIVEN GRAMMATICAL PATTERNS OR A TEXTUAL PATTERN IN A GIVEN NARRATIVE
*/


/**
  Setup
*********/

  include 'dbsetup.php';
  include 'util.php';
  $query  = explode('&', $_SERVER['QUERY_STRING']);
  $params = array();
  foreach( $query as $param )
  {
    list($name, $value) = explode('=', $param);
    $params[urldecode($name)][] = urldecode($value);
  }
  
/**
  Script
*********/

  $occurrences = array();
  $narrative = $_GET['narrative'];
  $row =  getDimensions($narrative);
  $occurrences['total'] =$row['length'];
  $occurrences['min'] = $row['min'];
  $occurrences['max'] = $row['max'];
  $occurrences['narrative'] = $narrative;
  
  $type = $_GET['type']; // grammatical or text
  if($type=="grammatical"){
    $ids = $params['id'];
    $occurrences["type"] = "grammatical";
    $occurrences["instances"] = array();
    foreach($ids as $id){
      $occurrences["instances"][strval($id)] = getGrammaticalOccurrences($narrative, $id);
    }
  }else if ($type=="text"){
    $occurrences["original"] = $_GET['q'];
    $q  = trim(str_replace('\"', '"', $_GET['q']));
    $occurrences["type"] = "text";
    $occurrences["instances"] = getTextOccurrences($narrative, $q);
  }
  echo json_encode($occurrences);
  
/**
 Supporting  functions
*************************************/

  /** 
   Return the number of sentences, min sentence id, and max
  sentence id in the given narrative
  **/
  function getDimensions($narrativeID){
    $query = "SELECT COUNT(id) as length, MIN(id) as min, MAX(id) as max  FROM sentence WHERE narrative_id = ".$narrativeID.";";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    return $row;
  }
  
  /** 
  Return a list of sentence id's from the given narrative
  in which the given dependency ID occurs.
  **/
  function getGrammaticalOccurrences($narrative, $dependencyID){
    $ids = array();
    $query = "SELECT sentence_id FROM dependency_xref_sentence, sentence WHERE (narrative_id=".$narrative.") AND (dependency_xref_sentence.dependency_id = ".$dependencyID.") AND (sentence.id = dependency_xref_sentence.sentence_id) ORDER BY sentence_id;";
    $result = mysql_query($query);
    while($row = mysql_fetch_array($result)){
      array_push($ids, $row['sentence_id']);
    }
    return $ids;
  }

  /** 
  Return a list of sentence id's from the given narrative
  in which the given pattern occurs exactly.
  **/
  function getTextOccurrences($narrative, $q){
    $ids = array();
    $q = ereg_replace("[ \t\n\r]+", " ", $q);
    $query = "SELECT id FROM sentence WHERE (narrative_id = ".$narrative.") AND (MATCH(sentence) AGAINST('".'"'.$q.'"'."' IN BOOLEAN MODE)) ORDER BY id;";
    $result = mysql_query($query);
    while($row = mysql_fetch_array($result)){
      array_push($ids, $row['id']);
    }
    return $ids;  
  }

?>