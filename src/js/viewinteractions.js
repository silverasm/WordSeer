/*****************************************************************
viewinteractions.js

Interactions with words while reading.

Called by init() in view.js in service of view.php
	1. Utilities for creating a list of words to search for in a heat map
	2. Utilities for displaying related words
*****************************************************************/

/** get the words that are highlighted, starting with the clicked-on words **/
function getHighlightedWords(word){
	var words = word.text().trim();
	if(word.is(".highlighted")){
		words = "";
		$("span.highlighted").each(function(){
			words += $(this).text();
		});
	}
	words = words.trim();
	return words;
}
//
//1.  Heat maps
//

/** display the heat map words dialog and add a word to the list**/
function addToHeatMapWords(){
	var words = getHighlightedWords($(this));
	if(words.trim().split(" ").length > 1){
		words = '"'+words.trim()+'"';
	}
	if(heatMapQuery.indexOf(words) == -1){
	html = '<li><span class="heat-map-word">';
	html += words;
	html += '<img class="icon heat-map-word delete" src="img/delete.png">'
	html += '</span></li>';
	$("ul.heat-map-words").append(html);
	$("img.heat-map-word.delete").click(removeHeatMapWord);
	$("ul.heat-map-words > li").last().hide();
	$("ul.heat-map-words > li").last().fadeIn();
	heatMapQuery = heatMapQuery+" +"+words+";";
	$("#heat-map-query > a").attr("href", heatMapURL+heatMapQuery)
	}
	$("#heat-map-query > a").show();
	$("#heat-map-query").show();
	$("#heat-map-query").css("top", $(this).offset().top-25);
}

/** remove a word from the list of words in the heat map query **/
function removeHeatMapWord(){
	var words = $(this).parent().text();
	$(this).closest("li").remove();
	heatMapQuery = heatMapQuery.replace(" +"+words+";", "");
	$("#heat-map-query > a").attr("href", heatMapURL+heatMapQuery)
	if(heatMapQuery.indexOf(" +") == -1){
		$("#heat-map-query > a").hide();
	}
}

//
// 2. Related words
//

/** fetch related words from the server **/
function fetchRelatedWords(phraseData, callback){
	$.getJSON("src/php/synonym_groups.php", phraseData, callback);
}

/** get words and ids **/
function getHighlighted(word){
	var words = word.text().trim()+"_"+word.attr("word")+"_"+word.attr("sentence")+" ";
	if(word.is(".highlighted")){
		words = "";
		$("span.highlighted").each(function(){
			words += $(this).text().trim()+"_"+$(this).attr("word")+"_"+$(this).attr("sentence")+" ";
		});
	}
	words = words.trim();
	return words;
}

/** view related words to a clicked on word**/
function viewRelatedWords(){
	var words = getHighlighted($(this));
	var phraseData = {
		type:"context", //wa
		words:words,
		json:true	
	};
	var top = $(this).offset().top;
	var left = $(this).offset().left;
	$.getJSON("src/php/synonym_groups.php", 
	phraseData, 
	function(data){displayRelatedWordPopup(data,top, left)});
}

/** display a popup in view.js showing the related word information **/
function displayRelatedWordPopup(data, top, left){
	var content = false;
	html = "";
	var word = {}
	html += "<ul>"
	for(var i = 0; i < data.length; i ++){
		word = data[i];
		html += '<li class="synset"><h6>'+word.word+'</h6>';
		html += '<ul class="synset">';
		for(var j = 0; j < word.synonyms.length; j++){
			content = true;
			html += '<li><span class="synonym">'+word.synonyms[j].word+"</span></li>";
		}
		html += '</ul></li>'
	}
	html+='</ul>'
	if(content){
		$("#related-words").html(html);
		$("#related-words-container").show();
		$("span.synonym").click(addToHeatMapWords);
		$("#related-words-container").css("top", top+25);
		$("#related-words-container").css("left", left-60);
	}
}
