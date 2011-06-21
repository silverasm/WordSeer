/****
* This file contains utilities for visualizing the distribution
* of a grammatical or exact-match-phrase pattern in the set
* of narratives opened up for reading.
****/

/** Fetch the data and call back the visualizeOccurrences
*** function.
**/
function getOccurrences(whatToDo){
	narratives = getNarratives();
	var func = (whatToDo=="visualize"? visualizeOccurrences : saveOccurrences);
	var grammaticalURL = "";
	var info = {};
	$('input:checkbox[name="depindex"]').each(function(){
			if($(this).is(":checked")){
				if(type=="grammatical"){
					grammaticalURL += "&id="+$(this).attr("dep");
					if(whatToDo == "save"){
						info = {
							type:"grammatical",
							id:$(this).attr("id"),
							name:$(this).closest("li").html()
						}
						saved.push(info);
					}
				}
				else if(type=="text"){
					if(whatToDo == "save"){
						info = {
							type:"text",
							query:$('input:checkbox[name="textpattern"]').attr("value"),
							name:$(this).closest("li").html()
						}
						saved.push(info);
					}
				}
			}
		})

	for(var i = 0; i < narratives.length; i++){
		if(type=="text"){
			$.getJSON("src/php/getdistribution.php",
			{
				narrative:narratives[i],
				type:"text",
				q:$('input:checkbox[name="textpattern"]').attr("value")
			},
			func
			);
		}else if(type=="grammatical"){
			$.getJSON("src/php/getdistribution.php?type=grammatical&narrative="+narratives[i]+"&"+grammaticalURL,
			visualizeOccurrences);
		}	
	}
	$("#savePattern").hide();
}

/** Visualize the occurrence data.
*		If it is a text pattern, then the instances field
*		is just a list of sentence ID's.
*		If it is a grammatical pattern, then there might be many, so
*		the instances object has one list of sentenceID's for every ID.
**/
function visualizeOccurrences(occurrences){
	var total = occurrences.total;
	var narrativeID = '#'+occurrences.narrative;
	var sentences = occurrences.instances;
	var step = Math.max(2, parseInt(occurrences.total)/100);
	var currentStep = 0;
	var found = false;
	var html = "";
	var contained = 0, j=0;
	if(type=="text"){
		for(var i = parseInt(occurrences.min); i <= parseInt(occurrences.max); i++){
			if(currentStep >= step){
				if(found){
					html+='<div class="found indexer" sentence="'+currentSentID+'"></div>';
					found = false;
				}else{
					html+='<div class="indexer" sentence="'+i+'"></div>';
				}
				currentStep = 0;
			}else{
				//$("#debug").append(" "+i);
				if(contains(sentences, i.toString())){
					found = true;
					currentSentID = i;
				}
				currentStep+=1;
			}
		}
		$(narrativeID).children(".sentence-visual").html(html);
	}else if (type=="grammatical"){
		for(var i = parseInt(occurrences.min); i <= parseInt(occurrences.max); i++){
			if(currentStep >= step){
				if(found){
					html+='<div class="found c'+contained+' indexer" sentence="'+currentSentID+'"></div>';
					found = false;
				}else{
					html+='<div class="indexer" sentence="'+i+'"></div>';
				}
				currentStep = 0;
			}else{
				contained = -1;
				j = 0;
				for(var depID in occurrences.instances){
					if (contains(occurrences.instances[depID], i.toString())){
						contained=j;
					}
					j+=1;
				}
				if(contained != -1){
					found = true;
					currentSentID = i;
					contained = contained % 5; // there are only 5 colors to paint the indexers
				}
				currentStep+=1;
			}
		}
		$(narrativeID).children(".sentence-visual").html(html);
	}
	$('div.indexer').click(function(){
		$("span.searched").removeClass("searched");
		var id = "#"+$(this).attr("sentence");
		var narr_id = $(this).closest("div.narrative").attr("id");
		var narrativeID = "#"+narr_id;
		if(outOfRange(narr_id, parseInt($(this).attr("sentence")))){
			narrativeFrames[narr_id] = parseInt($(this).attr("sentence"));
			$(narrativeID).children("div.words").html("");
			$(narrativeID).children("div.words").load("src/php/getwords.php?narrative="+narr_id+"&sentence="+$(this).attr("sentence"),
			function(){
				$(id).addClass("searched");
				scrollToPlace();
				$('span.word').mousedown(turnHighlighterOn);
				$('span.word').mouseup(turnHighlighterOff);
			}
			);	
		}else{
			$(id).addClass("searched");
			scrollToPlace();
		}
	})
}