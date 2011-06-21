/** check if an array <a> contains an object <obj>**/
function contains(arr, obj) {
	var i = arr.length;
	while (i--) {
		if (arr[i] === obj) {
			return true;
		}
	}
	return false;
}
/** Close a parent dialog box
*/
function closeParent(){
	$(this).closest("div.dialog").hide();
}

/** get all the tags **/
function getAllTags(){
	$.getJSON("src/php/listalltags.php",{}, function(data){
		allTags = data.tags;
		$(".tags.autocomplete").autocomplete({source:allTags});
		$("select.tag.select").html("");
		$("select.tag.select").append('<option name="filter" value="all">(all narratives)</option>');
		for(var i = 0; i < data.tags.length; i++){
			$("select.tag.select").append('<option name="tag" value="'+data.tags[i]+'">'+data.tags[i]+'</option>');
		}
		if($.url.param("filter") && $.url.param("filter") == "tag" && contains(data.tags, $.url.param("tag"))){
			var tag = 'option[value="'+$.url.param("tag")+'"]';
			$(tag).attr("selected", "selected");
		}
		else{
			$('option[name="filter"]').attr("selected", "selected");
		}
	})
}

/** remove whitespace from the start and end of a string**/
String.prototype.trim = function () {
	return this.replace(/^\s*/, "").replace(/\s*$/, "");
}

/** append a list of elements to the end of an array**/
Array.prototype.append = function(elements){
	for(var i = 0; i < elements.length; i++ ){
		this.push(elements[i]);
	}
	return this;
}

/** Show and hide the examples menu**/
function handleMenu(){
	$("#examples-menu").mouseover(function(){
		var position = $("#examples-menu").offset();
		var top = $("#examples-menu").outerHeight();
		$("ul.examples").offset({left:position.left, top:top+position.top});
		$("ul.examples").show();
	})
	$("h1,table,img,form").mouseover(function(){
		$("ul.examples").offset({left:0, top:0});
		$("ul.examples").hide();
	})	
}
/** make tabs work**/
function tabs(){
	$(".tabs").click(function(){
		var panel = "";
		$(".tabs").each(function(){
			panel = "#"+$(this).attr("panel");
			$(panel).hide();
			$(this).removeClass("selected");
			$(this).addClass("unselected");
		})
		panel = '#'+$(this).attr("panel");
		$(panel).show();
		$(this).removeClass("unselected");
		$(this).addClass("selected");
	})	
}

/** Output a human-friendly description of a given dependency 
*** relation
**/ 
function describe(relation){
	switch(relation){
		case "auxpass":
		return "-";
		case "prep":
		return "is related by a preposition to";
		case "nn":
		return "forms a compund";
		case "advmod":
		return "described as";
		case "amod":
		return "described as";
		case "nsubj":
		return "done by";
		case "nsubjpass":
		return "done by";
		case "dobj":
		return "done to";
		case "iobj":
		return "done to";
		case "poss":
		return "is owned by";
		case "conj":
		return "and/but/or";
		case "rcmod":
		return "done to";
		case "infmod":
		return "to";
		default:
		return relation.replace("prep_", "").replace("conj_", "").replace("_", " ");
	}
}

/** Gets the ids of all the narratives being read.
**/
function getNarratives(){
	var n = [];
	var query = $.url.attr("query");
	if(query){
		var components = query.split("&");
		var item = [];
		for(var i = 0; i < components.length; i++){
			item = components[i].split("=");
			if(item[0]=="id"){
				n.push(item[1].split("_")[0]);
				narrativeFrames[item[1].split("_")[0]] = parseInt(item[1].split("_")[1]);
			}
		}
		narratives = n;	
	}
	return n;
}

/** Get more sentences from the narrative
**/
function getFrame(narrativeID, sentence, increasing){
	var id = "#"+narrativeID;
	$(id).children("div.words").load("src/php/getwords.php?narrative="+narrativeID+"&sentence="+sentence, reinit);
}
function reinit(){
	$('span.word').mousedown(turnHighlighterOn);
	$('span.word').mouseup(turnHighlighterOff);
}

function getPrevious(){
	narrativeFrames[narrativeID] = $('span.sentence').last().attr("id");
	var narr = $(this).attr("narrative");
	var sentence = narrativeFrames[narr];
	narrativeFrames[narr] = sentence-499;
	getFrame(narr, narrativeFrames[narr], false);
	$(this).closest("div.narrative").children("div.words").scrollTo("100%", 1);
}
function getNext(){
	narrativeFrames[narrativeID] = $('span.sentence').last().attr("id");
	var narr = $(this).attr("narrative");
	var sentence = narrativeFrames[narr];
	narrativeFrames[narr] = sentence+499;
	getFrame(narr, narrativeFrames[narr], true);
	$(this).closest("div.narrative").children("div.words").scrollTo("0%", 1);
}
function getFirst(){
	narrativeFrames[narrativeID] = $('span.sentence').last().attr("id");
	var narr = $(this).attr("narrative");
	narrativeFrames[narr] = -1;
	getFrame(narr, narrativeFrames[narr], false);
	$(this).closest("div.narrative").children("div.words").scrollTo("0%", 1);
}
function getLast(){
	narrativeFrames[narrativeID] = $('span.sentence').last().attr("id");
	var narr = $(this).attr("narrative");
	narrativeFrames[narr] = -2;
	getFrame(narr, narrativeFrames[narr], true);
	$(this).closest("div.narrative").children("div.words").scrollTo("100%", 1);

}

/** Determine whether a sentence can be seen in the current frame
**/
function outOfRange(narrativeID, sentence){
	if(sentence < narrativeFrames[narrativeID]-250){
		return true;
	}else if (sentence > narrativeFrames[narrativeID]+250){
		return true;
	}else{
		return false;
	}
}