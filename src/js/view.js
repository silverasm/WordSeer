function init(){
	if (typeof(localStorage) == 'undefined' ) {
		alert("Your browser does not support this site's editing features. You will not be able to make annotations.");
	} 
	scrollToPlace();
	getNarratives();
	$('span.word').mousedown(turnHighlighterOn);
	$('span.word').mouseup(turnHighlighterOff);
	$('.save').click(getPatterns);
	$('.close').click(closeParent); //util.js
	$('input[name="p"]').click(getPrevious);
	$('input[name="n"]').click(getNext);
	$('input[name="f"]').click(getFirst);
	$('input[name="l"]').click(getLast);
	$('.highlight').click(annotate); //annotate.js
	addResults();
	// make section navigation
	createSectionNavigation();
	//help text
	if($.url.param("grammatical")!="on"){
		$("#gov, #dep").hover(function(){
			$("#help-text").show();
		})
		$('#gov, #dep').mouseout(function(){
			$("#help-text").hide();
		})
	}	
	//Make tabs work util.js
	tabs();
	// Handle annotations, src/js/annotation.js
	$('.tabs[panel="annotations"]').click(listAnnotations);
	$("#submit-annotation").submit(submitAnnotation);
	//handle listing
	$('.tabs[panel="listing"]').click(listNarratives);
	listNarratives();
	// display the annotations in the correct place
	displayAnnotations();
	//sign the user in if user was logged in before // user.js
	signUserIn();	
	//handle addding words to heat maps.
	$("#heat-map").val("");
	$('span.word').click(addToHeatMapWords)//src/php/viewinteractions.js
	//handle showing relate words.
	$('span.word').rightClick(viewRelatedWords)//src/php/viewinteractions.js
	$('span.word').noContext();
}

/** Scroll to the sentence that was searched for
**/
function scrollToPlace(){
	$('span.searched').each(function(){
		$(window).scrollTo(this, 1);
		$(window).scrollTo('-=100px', 1);
	});
}

/** Select a portion of text
**/
function turnHighlighterOn(){
	if(!$(this).is('.highlighted')){
			$('.highlighted').removeClass("highlighted");
			startSentence = parseInt($(this).attr("sentence"));
			startPosition = parseInt($(this).attr("position"));
			highlighted = $(this).closest(".sentence");
			narrativeID = $(this).attr("narrative");
			highlightStartY = $(this).offset()['top'];
		}		
}


function turnHighlighterOff(){
	highlighter = false;
	endPosition = parseInt($(this).attr("position"));
	endSentence = parseInt($(this).attr("sentence"));
	var id = -1;
	var pos = -1;
	if(endSentence == startSentence){
		id = "#"+startSentence;
		$(id).children("span.word").each(function(){
			pos = parseInt($(this).attr("position"));
			if(pos >= startPosition && pos <= endPosition){
				$(this).addClass("highlighted");
			}
		})
	}else{
		for(var i = startSentence; i <= endSentence; i++){
			id = "#"+i;
			if(i == startSentence){
				$(id).children("span.word").each(function(){
					pos = parseInt($(this).attr("position"));
					if(pos >= startPosition){ $(this).addClass("highlighted");}
				})
			}else if(i == endSentence){
				$(id).children("span.word").each(function(){
					pos = parseInt($(this).attr("position"));
					if(pos <= endPosition){ $(this).addClass("highlighted")};
				})
			}else{
				$(id).addClass("highlighted");
			}
		}
	}
}

/** Fetch pattern data from the server based on the sentence
**  positions that are highlighted.
*/
function getPatterns(){
$.getJSON("src/php/getpatterns.php",
		{
			sentence:highlightedSentence, 
			start:highlightStart,
			end:highlightEnd
		},
		showPatterns
	);
}

/** Display a detected-patterns popup, with save, search, view options.
**/
function showPatterns(dependencies){
	detected = dependencies;
	if(dependencies.length > 0){
		var html = "";
		html += "<h4>Grammatical Patterns</h4>"
		html += "<ul>";
		var d = {};
		for(var i = 0; i < dependencies.length; i++){
			d = dependencies[i];
			html += "<li>";
				html += '<input type="checkbox" dep="'+d.id+'" name="depindex" value="'+i+'">';
				html += '<span class="gov">'+getWord(d.gov)+' </span>';
				html += '<span class="relation" relation="'+d.relation+'">'+describe(d.relation)+" </span>";
				html += '<span class="dep">'+getWord(d.dep)+" </span>";
			html += "</li>";
		}
		html += "</ul>";
		$("div#savePattern").children(".listing").html(html);
	}
	if(highlightEnd-highlightStart >= 0){
		var words = getWords(highlightStart, highlightEnd);
		var html = "";
		html += "<h4>Text Pattern</h4><br>";
		html += '<input type="checkbox" name="textpattern" value="'+words+'">';
		html += '<span class="textpattern">'+words+"</span>";
		$("div#savePattern").children(".textpattern").html(html);
	}
	if(dependencies.length == 0 && highlightEnd-highlightStart < 0){
		alert("No patterns detected!");
	}else{
		$("#savePattern").show();
		bindPatternActions();
	}
}

/** Associate actions with the buttons in the 
*** save/view/search patterns dialog box
**/
function bindPatternActions(){
	type = "text";
	$('input[name="visualize"]').click(function(){getOccurrences("visualize");});
	$('input:checkbox[name="textpattern"]').click(function(){type="text";});
	$('input:checkbox[name="depindex"]').click(function(){type="grammatical";});
	$('input[name="search"]').click(search);
	$('input[name="within"]').click(searchWithin);
	$('input[name="save"]').click(function(){getOccurrences("save");displaySaved();});
}



/** Get the word with the given index from the highligted
*** sentence element
**/
function getWord(position){
	return $(highlighted).children('span.word[position="'+position+'"]').text()
}

function getWords(start, end){
	var words = "";
	for(var i = start; i <= end; i++){
		words += getWord(i)+" ";
	}	
	return words;
}

/**
Associate the hidden input "results" with the narrative id's being viewed
*/
function addResults(){
	var r = "";
	for(var i = 0; i < narratives.length; i ++){
		r += narratives[i]+" ";
	}
	$('input.hidden-input[name="results"]').attr("value", r);
}

/** Gets every <h3 class="section-type"> and turns it into an entry in a 
navigation panel **/
function createSectionNavigation(){
	var html = "";
	$("a.section-type").each(function(index){
		$(this).attr("name", $(this).text()+index);
		html = '<a href="#'+$(this).text()+index+'"><li class="menu">'+(index+1)+". "+$(this).text()+"</li></a>";
	 	$("ul.section-nav").append(html)
	});
}

/**List all the narratives : get data from src/php/listnarratives.php**/
function listNarratives(){
	$.get("src/php/listnarratives.php?",{}, function(html){
		$("#listing").html(html);
		sorttable.makeSortable($('#listing > table').get(0));
	})
}
