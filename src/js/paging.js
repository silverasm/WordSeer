/** Submit the search form to see the next or previous page of search results
**/


function getNextPage(){
	getPage("next");
}

function getPreviousPage(){
	getPage("previous")
}

function getPage(direction){
	//set the new page value
	var thisPage = parseInt($('#grammatical > input[name="page"]').val());
	var newPage = thisPage;
	if(direction == "previous"){
		newPage = thisPage -1;
	}else{
		newPage = thisPage + 1;
	}
	$('#grammatical > input[name="page"]').val(newPage.toString());
	$('#text > input[name="page"]').val(newPage.toString());
	// submit the appropriate form
	if($('input[name="grammatical"]').is(':checked')){
		$('form[name="grammatical"]').submit();
	}else{
		$('form[name="text"]').submit();
	}
}

function resetPaging(){
	$('#grammatical > input[name="page"]').val("0");
	$('#text > input[name="page"]').val("0");
}