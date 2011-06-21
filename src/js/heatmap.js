/**
*	This file contains utilities for 
*		- Drawing a heat map
*		- Fetching heat map data based on data filled out in
*			heatmap.php
**/

/** When the page loads, do the following**/
function init(){
	overlay = false;
	//setup drawing surface for heat map
	paper = Raphael("heatmap", heatMapWidth, heatMapHeight);
	//setup interactivity controls
	$("#add-heat-map").click(addHeatMap);
	$("#reset-heat-map").click(function(){window.location.href = "heatmap.php";})
	$("#words").clickOnEnter(addHeatMap);
	$('img[type="close"]').click(function(){$(this).parent().hide();})
	$("fieldset").hover(function(){$("#info-popup").hide();});
	//draw the heat maps from the restful URL
	drawHeatMapsFromURL();
	//draw the menu
	handleMenu(); //util.js
	//sign user in
	signUserIn(); // user.js
	getAllTags(); //util.js (for autocomplete)
}

/** 
The main method called when the page loads.
Grab the URL of the page and draw the corresponding heat maps
Also draw concordances and other attending stuff.	
**/
function drawHeatMapsFromURL(){
	currentHeatMapColor = 0;
	$('input[name="unit"]').attr("checked", "false");
	$('#sentences-unit').attr("checked", true);
	unit = "sentences";
	if($.url.param("unit") == "paragraphs"){
		$("#paragraphs-unit").attr("checked", true);
		$("#sentences-unit").attr("checked", false);
		unit = "paragraphs";
	}
	if($.url.param("words")){
		var words = $.url.param("words").replace(/;;/g, ";");
		var w = words.split(";");
		for(var i = 0; i < w.length; i++){
			if(w[i].trim() != ""){
				addHeatMapFromWords(w[i]);
			}
			// get the context and concordances
			getContext(w[i].trim());
		}
	}
}

/** Given some words, gets the sentence or paragraph occurrence data
from the server. If filtering is required based on tag, makes the
appropriate request **/
function heatMap(words, field, direction, color, unit){
	if(!$.url.param("filter") || $.url.param("filter")=="all"){
		$.getJSON("src/php/matchingsentences.php", 
		{	q:words,
			sort:field,
			unit:unit,
			direction:direction,
			type:'all'
		}, 
		function(data){
			drawHeatMap(data, words, color);
		});	
	}
	else if($.url.param("filter")=="tag" && $.url.param("tag")){
		$.getJSON("src/php/matchingsentences.php", 
		{	q:words,
			sort:field,
			direction:direction,
			unit:unit,
			type:'tag',
			tag:$.url.param("tag")
		}, 
		function(data){
			drawHeatMap(data, words, color);
		});	
	}
}

/** Given sentence occurrence data, and the words, and the current color, 
draw the heat map on screen in the appropriate color **/
function drawHeatMap(data, words, currentColor){
	var html = '<li><span class="highlight" style="background:'+currentColor+';">';
	html += words;
	html +='<img class="clear button" src="img/close.png"></span></li>';
	$("#words-list").prepend(html);
	$("img.clear").click(function(){
		var newWords = $.url.param("words").replace($(this).parent().text()+";", "");
		var newURL = $.url.attr("protocol")+"://"+$.url.attr("host")+$.url.attr("path")+"?words="+newWords;
		window.location.href = newURL;
	})
	var narrativeInfo = [];
	var sentences = [];
	var rect = {};
	var x = 0;
	var y = 0;
	var intensity = 0;
	var sents = "";
	var columns = paper.set();
	var glow = 2;
	for(var i = 0; i < data.length; i++){
		y = 0;
		narrative = paper.set();
		narrativeInfo = data[i];
		sentences = narrativeInfo['sentences']
		//adaptable-height blocks
		var perBlock = sentencesPerBlock;
		if(unit == "paragraphs"){
			perBlock = paragraphsPerBlock;
		}
		granularity = parseInt(narrativeInfo['length']/perBlock);
		blockHeight = heatMapHeight/granularity;
		blockWidth = heatMapWidth/data.length;
		if(!overlay){
			rect = paper.rect(x, 0, blockWidth, heatMapHeight);
			$(rect.node).attr("column", narrativeInfo['narrative']);
			rect.attr("fill", "white");
			rect.attr("stroke", "none");
			rect.attr("fill-opacity", 0);
			$(rect.node).attr("c", "white");
			$(rect.node).attr("fill", "white");
			//$(rect.node).attr("stroke", "#333");
			$(rect.node).attr("title", narrativeInfo['title']);
			$(rect.node).attr("date", narrativeInfo['date']);
			rect.hover(function(event){
				var narr = $(this.node).attr("column");
				var position = $("rect[column="+narr+"]").offset();
				$("#info-popup>div.content").html('<p><a href="view.php?id='+narr+'" target="new"><img class="viewbutton" src="img/view.png"></a><strong>'+$(this.node).attr("title")+"</strong></p><p>"+$(this.node).attr("date")+"</p>");
				$("#info-popup").css({position:"absolute",top:Math.max(0,position['top']-$("#info-popup").outerHeight()-30), left:position['left']-65+blockWidth/2});
				$("#info-popup").show();
				$("#sentence-popup").hide();
				$('rect[column="'+narr+'"]').css("fill-opacity", 0.3)
			})
			rect.mouseout(function(event){
				if(this.attr("fill") == $(this.node).attr("c")){
					var narr = $(this.node).attr("column");
					$('rect[column="'+narr+'"]').css("fill-opacity", 0);
				}
			})
			rect.click(function(event){
				if(currentMarkColor == markColors.length ){
					this.attr("fill", $(this.node).attr("c"));
					currentMarkColor = 0;
				}else{
					this.attr("fill", markColors[currentMarkColor]);
					currentMarkColor += 1;
				}
			})	
		}
		for(var j = 1; j <= granularity; j++){
			if(sentences.length > 0 && (sentences[0].number/narrativeInfo['length'])*granularity < j){
				rect = paper.rect(x, y, blockWidth, blockHeight);
				//metadata
				$(rect.node).attr("narrative", narrativeInfo['narrative']);
				$(rect.node).attr("title", narrativeInfo['title']);
				$(rect.node).attr("date", narrativeInfo['date']);
				$(rect.node).attr("words", words);
				$(rect.node).attr("unit", sentences[0].id);
				register(sentences[0].id, rect, "heatmap");
				sents = "";
				$(rect.node).attr("start", sentences[0].number);
				$(rect.node).attr("end", sentences[sentences.length-1]);
				intensity = 0;
				while(sentences.length > 0 && (parseInt(sentences[0].number)/narrativeInfo['length'])*granularity < j){
					intensity += 1;
					sents += sentences[0].number+" ";
					sentences = sentences.slice(1, sentences.length);
				}
				$(rect.node).attr("sentences", sents);
				//appearance
				intensity = Math.min(5, intensity*2)/5;
				$(rect.node).attr("c", currentColor);
				rect.attr("fill-opacity", intensity);
				$(rect.node).attr("stroke", "#333");
				rect.attr("cursor", "pointer")
				rect.attr("fill", currentColor);
				//events
				rect.hover(function(event){
					var narr = $(this.node).attr("narrative");
					var position = $("rect[column="+narr+"]").offset();
					$("#info-popup>div.content").html('<p><a href="view.php?id='+narr+'" target="new"><img class="viewbutton" src="img/view.png"></a><strong>'+$(this.node).attr("title")+"</strong></p><p>"+$(this.node).attr("date")+"</p>");
					$("#info-popup").css({position:"absolute",top:Math.max(0,position['top']-$("#info-popup").outerHeight()-30), left:position['left']-65+blockWidth/2});
					$("#info-popup").show()
					$('rect[column="'+narr+'"]').css("fill-opacity", 0.3)
					$(this.node).css("fill-opacity", $(this.node).css("fill-opacity")*glow);
					currentMarkColor = 0;
				},
				function(event){
					var narr = $(this.node).attr("narrative");
					if($('rect[column="'+narr+'"]').attr("fill") == $('rect[column="'+narr+'"]').attr("c")){
						$('rect[column="'+narr+'"]').css("fill-opacity", 0);
					}
					$("#info-popup").hide()
					$(this.node).css("fill-opacity", $(this.node).css("fill-opacity")/glow);
				});
				$(rect.node).hoverIntent(function(event){
					var narr = $(this).attr("narrative");
					sentencePopup(narr, $(this).attr("sentences"), {left:$(this).offset().left, top:event.pageY-5}, words, $(this).attr("c"));
				}, 
				function(){
					return false;
				})
				rect.click(function(event){
					var narr = $(this.node).attr("narrative");
					if(currentMarkColor == markColors.length ){
						this.attr("fill", $(this.node).attr("c"));
						currentMarkColor = 0;
					}else{
						this.attr("fill", markColors[currentMarkColor]);
						currentMarkColor += 1;
					}
				})
			}
			y += blockHeight;
		}
		x += blockWidth;
	}
	overlay = true; // subsequent heat maps are overlays
}

/** When the user hovers over a colored block gets the backing occurrences **/
function sentencePopup(narrative, numbers, position, words, color){
	$.getJSON("src/php/getsentence.php", 
	{
		narrative:narrative,
		unit:unit,
		numbers:numbers
	},
	function(sentences){
		drawSentencePopup(sentences, position, words, color);
	}
)
}

/** match two words **/
function match(word, words){
	for(var i = 0; i < words.length; i++){
		if(words[i][words[i].length-1]=="*"){
			if(word.indexOf(words[i]) != -1 && words[i].trim()!=""){
				return true
			}	
		}else if(word == words[i] && words[i].trim()!=""){
			return true
		}
	}
	return false;
}

/** Given sentence occurrence data, draw the popup bubble **/
function drawSentencePopup(sentences, position, words, color){
	var html = "";
	if(words.indexOf('"') != -1){
		var segments = ("X"+words).split('"');
		var w = [];
		for(var i = 0; i < segments.length; i++){
			if(i%2==0){
				var s = segments[i].replace(/\W+/g, " ").toLowerCase();
				if(i == 0){
					s = segments[i].substring(1).replace(/\W+/g, " ").toLowerCase();
				}
				if(s.trim().length > 0){
					w.append(s.split(" "));
				}
			}else{
				if(words.indexOf('"'+segments[i]+'"')!=-1){
					w.push(segments[i]);
				}
			}
		}
		words = w;
	}else{
		words = words.replace(/[^\w\*]+/g, " ").toLowerCase().split(" ");	
	}
	for(var i = 0; i < words.length; i++){
		if(words[i].substring(words[i].length-1) !='*'){
			words[i] = words[i]+" "
		}else{
			words[i] = words[i].substring(0, words[i].length-1);
		}
	}
	for(var i = 0; i < sentences.length; i++){
		if(sentences[i]['sentence'].length > 0){
			html += "<tr><td>";
			html +='<a target="_blank" href="view.php?id='+sentences[i]['narrative']+"_"+sentences[i]['sentence_id']+'"><img src="img/view.png" class="viewbutton"></a>';
			html+="</td><td>"
			for(var j = 0; j < words.length; j++){
				if(words[j].trim().length > 0){
						var newWord = '<span class="highlight" style="background:'+color+'">'+words[j]+'</span>';
						sentences[i]['sentence'] = sentences[i]['sentence'].replace(words[j], newWord);	
				}
				}
			html += sentences[i]['sentence'];
			html +="</td></tr>";	
		}
	}
	$("#sentences").html(html);
	$("#sentences>tr:empty").remove();
	$("#sentence-popup").css({position:"absolute", left:position['left']+blockWidth/2, top:position['top']-27})
	$("#sentence-popup").show();
}

/** 
Called when the user types in a new heat map.
Add the words to the list of heat maps in the URL and reload the page 
**/
function addHeatMap(){
	var words = $("#words").val().replace(/,/, " ");
	var oldWords = $.url.param("words");
	if(!$.url.param("words")){
		oldWords = "";
	}
	var newWords = oldWords;
	if(words.length > 0){
		newWords = oldWords+words+";";
	}
	
	if($("#sentences-unit").attr("checked")){
		unit = "sentences";
	}
	else if($("#paragraphs-unit").attr("checked")){
		unit = "paragraphs";
	}
	var filter = $('#tag').val();
	var newURL = "";
	if(filter == "all"){
		 newURL = $.url.attr("protocol")+"://"+$.url.attr("host")+$.url.attr("path")+"?words="+newWords+"&filter=all&unit="+unit;
	}else{
		 newURL = $.url.attr("protocol")+"://"+$.url.attr("host")+$.url.attr("path")+"?words="+newWords+"&filter=tag&tag="+escape(filter)+"&unit="+unit;
	}
	window.location.href = newURL;
}

/**draw the heat map based on what's been typed into the "words" input**/
function addHeatMapFromWords(w){
	var words = $("#words").val().replace(/,/, " ");
	if(w){
		words = w;
	}
	//sort by date
	//heatMap(words, "date", "asc",  heatMapColors[currentHeatMapColor], 'sentence);'
	//sort by length
	heatMap(words, "sentence_count", "asc",  heatMapColors[currentHeatMapColor], unit);
	
	currentHeatMapColor = (currentHeatMapColor + 1)%heatMapColors.length;
	$("#words").val("");
	$("span.highlight").click(function(){
		$('rect[words="'+$(this).text()+'"]').remove();
		$(this).remove();
	})
	$("#sentence-popup").click(function(){$(this).hide();});
}



/*************************************************************
Contexts and corcordances accompanying the heat map 
*************************************************************/


/** Get the context information and the concordance from the server **/
function getContext(words){
	if(words.length > 0){
		$.getJSON("src/php/getcontext.php", {
			words: words.replace("+", ""),
			type:"tree"
		}, function(data){displayContext( words.replace("+", ""), data);})	
	}
}

/** display the context data **/
function displayContext(words, data){
	displayConcordance(words, data['concordance']);
	//displayGrammaticalContext(words, data['grammatical'])
}

/** Draw the table of concordances **/
function table_displayConcordance(words, data){
	html = '<div class="concordance"><h2> Contexts for '+words+': '+data.num;
	if(data.num > 1){
		html += ' matches in ';
	}else{
		html += 'match in ';
	}
	if(data.docs > 1){
		html += data.docs+' documents </h2>';
	}else{
		html += data.docs+' document </h2>'
	}
	html += '<table class = "concordance">';
	var matches = data.matches;
	for(var i = 0; i < matches.length; i++){
		if(matches[i]["match"]){
				html += '<tr>';
				html += '<td><a href="view.php?id='+matches[i]['narrative']+'_'+matches[i]['id']+'"><img src="img/view.png"></a></td>';
				html += '<td class="left">'+concordanceLeft(matches[i]['left'])+'</td>';
				html += '<td class="match">'+matches[i]['match']+'</td>';
				html += '<td class="right">'+concordanceRight(matches[i]['right'])+'</td>';
				html += '</tr>';
		}
	}
	html += '</table></div>';
	$("#concordance").append(html);
}

function displayConcordance(words, data){
	$("#concordance").tabs("destroy");
	var newID = words.replace(/\W/g, "");
	var jqID = "#"+newID;
	newID = newID.substring(0, Math.min(10, newID.length));
	$("#tabs-list").append('<li class="tab-navigation"><a class="tab-navigation" href="#'+newID+'">'+words+'</a></li>');
	var html = '<div class="wordtree" id="'+newID+'">';
	html += '<form class="wordtree-control">';
	html += '<label>Detail </label><div class="wordtree-slider"></div>'; 
	html += '</form>'
	html += '<div class="wordtree-container" id="r-'+newID+'"></div>';
	html += '</div>'
	$("#concordance").append(html);
	$(jqID).data("data-right", data['rights']);
	$(jqID).data("data-left", data['lefts']);
	$(jqID).data("words", words);
	$(jqID).data("container", 'r-'+newID);
	$("div.wordtree-slider").slider({
		value:50, 
		min:0, 
		max:100, 
		step:10,
		slide:function(event, ui){
			if(windowLoaded){
			$(this).children("a").html(ui.value+"%");
			var c = "#"+$(this).closest("div.wordtree").data("container");
			var data = $(this).closest("div.wordtree").data();
			$(c).html("");
			var paper = Raphael($(this).closest("div.wordtree").data("container"),3000, 10000);
			makeWordTree($(this).closest("div.wordtree").data("data-right"), $(this).closest("div.wordtree").data("words"), ui.value, $(this).closest("div.wordtree").data("container"), 3000, 10000, WordTree.RO_LEFT, paper);
			makeWordTree($(this).closest("div.wordtree").data("data-left"), $(this).closest("div.wordtree").data("words"), ui.value, $(this).closest("div.wordtree").data("container"), 3000, 10000, WordTree.RO_RIGHT, paper);
			$(c).scrollTo("50%", 1);
		}
		}});
	
	$("div.wordtree-slider > a").html($("div.wordtree-slider").slider("value")+"%")
	var sliderID = "#"+newID+" > form > div.wordtree-slider";
	var paper = Raphael($(jqID).data("container"),  3000, 10000);
	makeWordTree(data['rights'], words, $(sliderID).slider("value"), "r-"+newID, 3000, 10000, WordTree.RO_LEFT, paper);
	makeWordTree(data['lefts'], words, $(sliderID).slider("value"), "r-"+newID, 3000, 10000, WordTree.RO_RIGHT, paper);
	$("#"+$(jqID).data("container")).scrollTo('50%', 1);
	$("#concordance").tabs({show:function(event){
		$("div.wordtree-container").scrollTo("50%", 1);
	}})
}

/** only show a few (i.e. concordanceLength) words to the left
(concordanceLength is defined in params.js) words **/
function concordanceLeft(words){
	try{
	if(words.length > 0){
		var w = words.split(" ");		
		w = w.slice(Math.max(0, w.length-concordanceLength), w.length);
		return w.join(" ");
	}
	else{
		return words;
	}
 }catch(exception){
	return words;
}

}

/** only show a few (i.e. concordanceLength) words to the right
(concordanceLength defined in params.js) **/
function concordanceRight(words){
	try{
	var w = words.split(" ");
	w = w.slice(0, Math.min(w.length, concordanceLength));
	return w.join(" ");
	}catch(exception){
		return words;
	}
}

/*************************************************************
Interactivity between the heat map and the concordance
*************************************************************/

/** register objects as belonging to a particular **/
function register(id, object, type){
	if(_register[id] == null){
		_register[id] = {heatmap:new Array(), wordtree:new Array()};
	}
	_register[id][type].push(object);
}

function getRegistered(id, type){
	return _register[id][type];
}