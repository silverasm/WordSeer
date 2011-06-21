<?php
  include "dbsetup.php";
  include "util.php";
  $withinSentence = ($_GET['within']=='sentence');
  $withinNarrative = ($_GET['within']=='narrative');
  $within = numberList($_GET['results']);
  //paging
  $page = $_GET['page'];  
  $pageLength = $_GET['pagelength'];
  $gov = str_replace('\"', '"', $_GET['gov']);
  $dep = str_replace('\"', '"', $_GET['dep']);
  $relationship = str_replace('\"', '"', $_GET['relation']);
  $count = 0; 
  $sentence_dependencies = array();
  $t1 = time();
  $govs = wordIDList($gov);
  $deps = wordIDList($dep);
  $rels = relationshipIDList($relationship);
  $dependency_ids = getDependencyIDs($govs, $deps, $rels, $withinNarrative, $withinSentence, $within, false);
  $time = time() - $t1;
  //echo '"Time to fetch dependencies: "'.$time.' seconds"';
  $count = mysql_num_rows($dependency_ids);
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
    //paging
    if($page > 0){
      echo '<input id="prev-page" value="Previous" class="view-button" type ="button" thispage="'.$page.'" pagelength="'.$pageLength.'">';
    }
    if($page < $count/$pageLength-1){
      echo '<input id="next-page" value="Next" class="view-button" type="button" thispage="'.$page.'" pagelength="'.$pageLength.'">';
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
  $t1 = time();
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
        echo '<img class="view" src="img/view.png" value="'.$row['id'].'_'.$row['sent_id'].'">';
        echo '</td>';
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
  echo '<input class="view-button" type="submit" value="View">';
  }
  $time = time() - $t1;
  //echo '" Time to fetch sentence metadata: '.$time.' seconds"';
?>