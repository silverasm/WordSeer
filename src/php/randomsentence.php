<?php
/** 
  Utilities and functions for generating a random sentence
*/

/** Returns a random sentence ID 
*/
function getRandomSentence(){
  $min = 43106;
  $max = 194487;
  $randomID = rand($min, $max);
  $query = "SELECT id, sentence from sentence where id = ".$randomID.";";
  $results =  mysql_query($query);
  $row =  mysql_fetch_array($results);
  return $row;
}

function categorizeDependencies($d){
  $excluded = array("", "csubj", "parataxis","negcc","auxpass","complm", "predet", "cc", "prep","rcmod", "dep", "advcl", "cop", "aux", "prt", "xcomp", "nn", "det", "poss", "partmod", "-", "mark", "ccomp", "expl");
  $dependencies = array();
  while($row = mysql_fetch_array($d)){
    if(!in_array($row['relationship'], $excluded )){
      $dependency = array();
       if(!array_key_exists($row['relationship'],$dependencies)){
         $dependencies[$row['relationship']] = array();
       }
       $dependency['gov_index'] = $row['gov_index'];
       $dependency['gov'] = $row['gov'];
       $dependency['dep_index'] = $row['dep_index'];
       $dependency['dep'] = $row['dep'];
       array_push($dependencies[$row['relationship']], $dependency);
      
    }
  }
  return $dependencies;
}

//if used as a script
if($_GET['random']=="on"){
  include 'dbsetup.php';
  include 'util.php';
  
  $sentenceInfo = getRandomSentence();  
  $dependencies = getDependencyIDs('', '', '', false, true, $sentenceInfo['id'], false);
  while(mysql_num_rows($dependencies) == 0 || count(categorizeDependencies)==0){
      $sentenceInfo = getRandomSentence();
      $dependencies = getDependencyIDs('', '', '', false, true, $sentenceInfo['id'], false);  
  }
  $data = array();
  $sentence = $sentenceInfo['sentence'];
  $data['randomSentence'] = $sentence;
  $data['randomDependencies']=categorizeDependencies($dependencies);
  echo json_encode($data);
}


?>
