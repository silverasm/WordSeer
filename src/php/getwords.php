<?php
  /**
  THIS SCRIPT CONTAINS UTILITIES FOR PAGING ACROSS NARRATIVES.
  THE SCRIPTS HERE FETCH SECTIONS OF THEM
  */

/**
  Setup
*********/

  include 'dbsetup.php';
  include 'util.php';
  
/**
  Script
*********/
  $id = $_GET['narrative'];
  $sent_id = $_GET['sentence'];
  if($sent_id>0){
  $upperlimit = $sent_id+250;
  $lowerlimit = $sent_id-250;
  $wordQuery = "SELECT sentence_id, paragraph_id, word, position, word_id, type from paragraph join (SELECT sentence_id, paragraph_id, word, position, word_id from 
 (word JOIN 
      (SELECT word_id, sentence_id, paragraph_id, position 
      from sentence_xref_word ";
  $wordQuery = $wordQuery." JOIN (SELECT id, paragraph_id from sentence where narrative_id = ".$id." AND id <= ".$upperlimit." AND id >= ".$lowerlimit.") as S";
  $wordQuery = $wordQuery." ON S.id = sentence_id
           ORDER BY sentence_id, position) AS WS
  ON word.id = WS.word_id)) AS wds 
  ON (paragraph.id = wds.paragraph_id);";
  }else if($sent_id == -1){
    //getFirst
    $wordQuery = "SELECT sentence_id, paragraph_id, word, position, word_id, type from paragraph join (SELECT sentence_id, paragraph_id, word, position, word_id from 
   (word JOIN 
        (SELECT word_id, sentence_id, paragraph_id, position 
        from sentence_xref_word ";
    $wordQuery = $wordQuery." JOIN (SELECT id, paragraph_id from sentence where narrative_id = ".$id." LIMIT 500) as S";
    $wordQuery = $wordQuery." ON S.id = sentence_id
             ORDER BY sentence_id, position) AS WS
    ON word.id = WS.word_id)) AS wds 
    ON (paragraph.id = wds.paragraph_id);"; 
  }else if($sent_id == -2){
    //getLast
    $wordQuery = "SELECT sentence_id, paragraph_id, word, position, word_id, type from paragraph join (SELECT sentence_id, paragraph_id, word, position, word_id from 
   (word JOIN 
        (SELECT word_id, sentence_id, paragraph_id, position 
        from sentence_xref_word ";
    $wordQuery = $wordQuery." JOIN (SELECT id, paragraph_id from sentence where narrative_id = ".$id."  ORDER BY id DESC LIMIT 500) as S";
    $wordQuery = $wordQuery." ON S.id = sentence_id
             ORDER BY sentence_id, position) AS WS
    ON word.id = WS.word_id)) AS wds 
    ON (paragraph.id = wds.paragraph_id);";
  }
  
  $result = mysql_query($wordQuery);
  while($row = mysql_fetch_array($result)){
    $paragraph = $row['paragraph_id'];
    $sentence = $row['sentence_id'];
    $type = $row['type'];
     if($type != $oldType){
        $oldType = $type;
        echo '<h3 class="section-type">'.$row['type'].'</h3>';
      }
    if($paragraph != $oldParagraph){
      $oldParagraph = $paragraph;
      echo '</p><p class="paragraph '.$row['type'].'">';
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
?>