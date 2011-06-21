/** Fetch search results based on parts of the graph that are clicked
**/

function setCurrentGov(label){
	if(label == "reset"){
		currentGov = $('#gov').val();
	}else{
		currentGov = label;
	}
}

function setCurrentDep(label){
	if(label == "reset"){
		currentDep = $('#dep').val();
	}else{
		currentDep = label;
	}
}

function setCurrentRelationship(label){
	if(label == "reset"){
		currentRel = $('select[name="relation"]').val();
	}else{
		currentGov = label;
	}
}

function getSearchResults(p){
	var w = $('input[name="within"]:checked').val();
	var r = $('input[name="results"]').val();
	page = p;
	$.get('src/php/getsearchresults.php', 
		{
			dep:currentDep,
			gov:currentGov,
			relation:currentRel,
			page:p,
			pagelength:100,
			within:w,
			results:r
		}, 
		showFilteredResults
	)
}

function showFilteredResults(results){
	$('#submit-results').html(results);
	//paging
	$('#prev-page').click(function(){
		getSearchResults(page-1);
	});
	$("#next-page").click(function(){
		getSearchResults(page+1);
	});
}