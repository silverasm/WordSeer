/**** Initialization
***** If something needs to be done after the page loads, this
***** is where the function call is placed.
****/

function init(){
	//viewing results
  $('img.view').click(viewResult);
	$('img.view').attr("alt", "View this sentence in the text.")
	$('img.view').attr("title", "View this sentence in the text.")

	//help text
	if($.url.param("grammatical")!="on"){
		$("#gov, #dep").hover(function(){
			$("#help-text").show();
		})
		$('#gov, #dep').mouseout(function(){
			$("#help-text").hide();
		})
	}
	
	//random sentence
	$('form[name="grammatical"]').append('<input id="show-random" class="button" value="Show a Random Sentence" style="display:none;">');
	$("#show-random").click(function(){
		$(this).hide();
		$('#random').show();
		getRandomSentence();
		drawRandomSentence();
	})
	if($.url.param("grammatical")=="on"){
		$('#random').hide();
		$('#show-random').show();
	}else{
		$("#random").show();
		getRandomSentence();
		drawRandomSentence();
	}
	
	//paging
	$('#next-page').click(getNextPage);
	$('#prev-page').click(getPreviousPage);
	$("#text > input").change(resetPaging);
	$("#grammatical > input").change(resetPaging);
	
	//the relationship select box used for simple text search;
	$('select[name="relation"]').change(function(){
		if($('select[name="relation"]').val()=="none"){
			$("#dep").hide();
		}else{
			$("#dep").show();
		}
	})
	
	//draw frequency graphs
	if($.url.param("grammatical")=="on"){
		//drawFrequencyGraph(data_relationship, "relationship-frequency");
		drawFrequencyGraph(data_gov, "gov-frequency", "gov");
		drawFrequencyGraph(data_dep, "dep-frequency", "dep");
	}
	
	//menu //util.js
	handleMenu(); 
	
	// sign user in //user.js
	signUserIn();
}


/*** The functions that submit the narrative id's to the
***		viewer
***/

function toggleSelectAll(){
	var source = $('#select-all-checkbox');
	if(!source.is(':checked')){
		$('input.hidden-id:checkbox').attr('checked', false);
		source.attr('checked', false);
	}else{
		$('input.hidden-id:checkbox').attr('checked', 'checked');
		source.attr({'checked':'checked'});
	}
	addResults();
}

function addResults(){	
	var values = "";
	var type = $('input[name="within"]:checked').attr('value');
	$('input.hidden-id:checkbox').each(function(){
		if($(this).is(":checked")){
		if(type=="narrative"){
			values += $(this).attr('value').split("_")[0]+" ";
		}else{
			values += $(this).attr('value').split("_")[1]+" ";
		}
		}
	})
	//$("#debug").html(values.toString());
	$('input[name="results"]').attr('value', values);
	if(values.length == 0){
		$("input.view-button").hide();
	}else{
		$("input.view-button").show();
	}
}

/*
* Draw a graph showing the frequencies of different 
* words participating at a particular position in a
* grammatical relationship.
* 
* This method uses protovis: lib/protovis-3.2
* The content of the data is as follows:
* 	min and max values are specified so that a scale can be derived
* 	there is a list of data points that have a the follwing:
*				label
*				value
*
* At this point, I don't restrict what I'm graphing. Could be words,
* could be relations.
*/	
function drawFrequencyGraph(data, canvas, name){
	if(name=="gov"){
		num = 1;
	}else if(name=="dep"){
		num = 2;
	}
	setCurrentDep("reset");
	setCurrentGov("reset");
	setCurrentRelationship("reset");
	if(data.data.length > minGraphSize){//need a certain number of data points
	var x = pv.Scale.ordinal()
	var v = data.data;
	var keys = new Array();
	var values = new Array();
	for(var i = 0; i < Math.min(v.length, maxNum); i++ ){
		keys.push(v[i]['label']);
		values.push(v[i]);
	}
	
	var x = pv.Scale.ordinal(keys).splitBanded(0, w, 4/5),
	    y = pv.Scale.quantitative(0, data.max*1.05).nice().range(0, h);

	var vis = new pv.Panel()
			.canvas(canvas)
	    .width(w)
	    .height(h)
			.right(30)
			.bottom(70)
			.top(5)
			.left(30)
	    .strokeStyle("#ccc")
			.def("i", -1)
			.def("j", -1);

	vis.add(pv.Bar)
	    .data(values)
			.def("name", name)
	    .left(function(d){ return x(d['label']);})
	    .width(x.range().band)
	    .bottom(0)
	    .height(function(d){return y(d['value']);})
	    .fillStyle(function() { 
				if(vis.i() == this.index){
					return "brown";
				}else if(vis.j() == this.index){
					return "orange";
				}else{ 
					return "steelblue";
				}})
			.event("mouseover", function(){
				vis.i(this.index);
				vis.render();
			})
			.event("mouseout", function(){
				vis.i(-1);
				vis.render();
			})
			.event("click", function(d){
				if(vis.j()!=this.index){
						vis.j(this.index);
						//selectDependencies("reset", this.name());
						selectDependencies(d['label'], this.name());
						vis.render();
				}else{
					vis.j(-1);
					selectDependencies("reset", this.name());
				}
			})
	  .anchor("bottom").add(pv.Label)
			.font("13px sans-serif") 	     
	    .textBaseline("top")
			.textAlign("right")
			.textAngle(-Math.PI/4)
	    .text(function(d){return d['label'];});

	vis.add(pv.Rule)
	    .data(y.ticks())
	    .strokeStyle("rgba(0, 0, 0, .2)")
	    .bottom(function(d){if(d==parseInt(d)){return y(d);} })
			.visible(function(d){return d==parseInt(d);})
	  .anchor("right").add(pv.Label)
	    .text(function(n){ return y.tickFormat(n).replace(".0", "");});

	vis.render();
	graphs.push(vis);
	graphData.push(values);
	//$("#"+canvas).html("<h4>Term "+num+" Counts</h4>"+$("#"+canvas).html())
	}
}


/** Filter search results to show the dependencies corresponding to
** those selected.
**/
function selectDependencies(label, name){
	if(name=="dep"){
		setCurrentDep(label);
	}else if(name=="gov"){
		setCurrentGov(label);
	}else if(name=="rel"){
		setCurrentRelationship(label);
	}
	getSearchResults(/*page*/ 0);
	$("#submit-results").html('<img src="img/loading.gif">');
}

/** Gets a random sentence from the server, sets the appropriate variables
and calls drawRandomSentence
**/
function getRandomSentence(){
	$.getJSON('src/php/randomsentence.php', 
			{
				random:'on'
			},
			handleRandomSentence
	);
}

function handleRandomSentence(data){
	randomDependencies = data['randomDependencies'];
	randomSentence = data['randomSentence'];
	drawRandomSentence();
}

/** Draw the graphics showing the dependencies in a randomly selected sentence;
**/
function drawRandomSentence(){
	$("#random").html('<img src="img/close.png" class="close"><h4>A Randomly Chosen Sentence from the Narratives</h4><ul id="relationship-list"></ul>');
	$("img.close").click(function(){
		$("#random").hide();
		$("#show-random").show();
	});
	//reorganize dependencies according to human-understandable names
	var rd = {};
	for(var relation in randomDependencies){
		if(!rd[describe(relation)]){
			rd[describe(relation)] = randomDependencies[relation];
		}else{
			for(var i = 0; i < randomDependencies[relation].length; i++){
				rd[describe(relation)].push(randomDependencies[relation][i]);
			}
		}
	}
	randomDependencies = rd;
	//display the random sentence
	$("#random").append('<div id="random-sentence"></div>');
	var words = tokenize(randomSentence);
	var html = "<p>";
	for(var i = 0; i< words.length; i++){
		html += '<span class="random-word" index="'+(i+1)+'" >'+words[i]+"</span>";
	}
	$("#random-sentence").append(html+"</p>");
	
	
	// display the relationship list
	for(var relationship in randomDependencies){
		$("#relationship-list").append('<li class="menu" name="'+relationship+'">'+relationship+'</li>');
	}
	$("#random-sentence").append('<p id="random-instances">');
	// handle interactively showing relationships
	$("#relationship-list > li").hover(function(){
		var rel = $(this).attr("name");
		var relationships = randomDependencies[rel];
		var position = -1;
		for(var i = 0; i < 7; i++){
			$('span.random-word').removeClass('highlight'+i);
		}
		$('#random-instances').html("");
		var html = "";
		for(var i = 0; i < relationships.length; i++){
			position = parseInt(relationships[i]['gov_index'])+1;
			$("span.random-word[index="+position+"]").addClass('highlight'+(i%7));
			position = parseInt(relationships[i]['dep_index'])+1;
			$("span.random-word[index="+position+"]").addClass('highlight'+(i%7));
			html = '<p><span class="gov highlight'+i%7+'">'+relationships[i]['gov']+"</span>";
			html += '<span class="relationship"> '+rel+' </span>';
			html += '<span class="dep highlight'+i%7+'">'+relationships[i]['dep']+'</span></p>';
			$('#random-instances').append(html);
		}
		$("#random").height($("#get-random").height()+$('h2').height()+10+Math.max($("#relationship-list").height(), $("#random-sentence").height()));		
		
	});
	// display the button to get the next sentence
	$("#relationship-list").append('<li><input class="button" type="button" id="get-random" value="Another Random Sentence"></li>	');
	$("#get-random").click(getRandomSentence);
	$("#random").height($("#get-random").height()+$('h2').height()+10+Math.max($("#relationship-list").height(), $("#random-sentence").height()));		
}

/** Submit the view form
**/

function viewResult(){
	var id = $(this).attr("value");
	window.location.href = "view.php?id="+id;
}