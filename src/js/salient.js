/*****************************************************************
salient.js
Called by init() in view.js in service of view.php
Utilities for maintaining a list of words to search for in a heat map
*****************************************************************/

/** display the heat map words dialog and add a word to the list**/
function addToHeatMapWords(){
	var words = $(this).text().trim();
	if($(this).is(".highlighted")){
		words = "";
		$("span.highlighted").each(function(){
			words += $(this).text();
		});
	}
	if(words.trim().split(" ").length > 1){
		words = '"'+words.trim()+'"';
	}
	words = words.trim()
	html = '<li><span class="heat-map-word">';
	html += words;
	html += '<img class="icon heat-map-word delete" src="img/delete.png">'
	html += '</span></li>';
	$("#heat-map-query").show();
	$("#heat-map-query").css("top", $(this).offset().top-25);
	$("ul.heat-map-words").append(html);
	$("img.heat-map-word.delete").click(removeHeatMapWord);
	$("ul.heat-map-words > li").last().hide();
	$("ul.heat-map-words > li").last().fadeIn();
	var query = $("#heat-map").val();
	query = query +"+"+words+" ";
	$("#heat-map").val(query);
	$("#heat-map-query > a").attr("href", heatMapURL+escape(query)+";")
	$("#heat-map").change(function(){
		$("#heat-map-query > a").attr("href", heatMapURL+escape($(this).val())+";")
	})
	
}

/** remove a word from the list of words in the heat map query **/
function removeHeatMapWord(){
	var words = $(this).parent().text();
	$(this).closest("li").remove();
	var query = $("#heat-map").val();
	query = query.replace("+"+words+" ", "");
	$('#heat-map').val(query);
	$("#heat-map-query > a").attr("href", heatMapURL+escape(query)+";")
}

/** go to a heatmap page **/
