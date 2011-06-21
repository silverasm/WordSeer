/*****************************************************************
tf-idf.js
Called by init() in view.js in service of view.php
Utilities for displaying a list of salient words next to a paragraph.
*****************************************************************/
function showSalientWords(){
	if($(this).closest("p.paragraph").attr("paragraph") != currentParagraph){
		var id = 'p[paragraph="'+currentParagraph+'"]';
		$(id).children('div.salient').remove();
		$(id).css("border", "1px solid white");
		currentParagraph = $(this).closest("p.paragraph").attr("paragraph");
		var id = 'p[paragraph="'+currentParagraph+'"]';
		$(id).css("border", "1px solid #DDD");
		$.getJSON("src/php/tf-idf.php", {
			paragraph:currentParagraph,
			number:numSalientWords
		}, displaySalientWords);	
	}
}
function displaySalientWords(data){
	var html = '<div class="salient"><h5>Salient words</h5><ul>';
	for(var i = 0; i < data['words'].length; i++){
		html += "<li>";
		html += data['words'][i];
		html += '</li>';
	}
	html += "<ul></div>";
	var id = 'p[paragraph="'+data['paragraph']+'"]';
	$(id).children('div.salient').remove();
	var height = $(id).outerHeight();
	$(id).append(html);
	$(id).children('div.salient').css("top", $(id).offset()['top']);	
}
