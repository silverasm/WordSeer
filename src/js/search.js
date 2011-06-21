/**
 This file contains utilities for visualizing the distribution
 of a grammatical or exact-match-phrase pattern in the set
 of narratives opened up for reading.
****/
function search(){
	var newURL = $.url.attr("protocol")+"://"+$.url.attr("host")+$.url.attr("path").replace("view.php", "index.php?");
	if(type=="text"){
		var query = '"'+$('input:checkbox[name="textpattern"]').attr("value")+'"';
		newURL += 'fulltext=on&q=';
		newURL += query;
	}else if(type=="grammatical"){
		newURL += "grammatical=on";
		var govs = "";
		var deps = "";
		var relation = "";
		var d;
		$('input:checkbox[name="depindex"]').each(function(){
			if($(this).is(":checked")){
				d = detected[parseInt($(this).attr("value"))];
				govs +="+"+getWord(d.gov);
				deps +="+"+getWord(d.dep);
				relation += "+"+d.relation;
			}
		})
		newURL += "&gov="+govs;
		newURL += "&dep="+deps;
		newURL += "&relation="+relation;
	}
	window.location.href = newURL;
}

function searchWithin(){
	var newURL = $.url.attr("protocol")+"://"+$.url.attr("host")+$.url.attr("path").replace("view.php", "index.php?");
	if(type=="text"){
		var query = '"'+$('input:checkbox[name="textpattern"]').attr("value")+'"';
		newURL += 'fulltext=on&q=';
		newURL += query;
	}else if(type=="grammatical"){
		newURL += "grammatical=on";
		var govs = "";
		var deps = "";
		var relation = "";
		var d;
		$('input:checkbox[name="depindex"]').each(function(){
			if($(this).is(":checked")){
				d = detected[parseInt($(this).attr("value"))];
				govs +="+"+getWord(d.gov);
				deps +="+"+getWord(d.dep);
				relation += "+"+d.relation;
			}
		})
		newURL += "&gov="+govs;
		newURL += "&dep="+deps;
		newURL += "&relation="+relation;
	}
	newURL += "&within=narrative&results=";
	for(var i = 0; i < narratives.length; i++){
		newURL += narratives[i]+"+";
	}
	window.location.href = newURL;
	
}

function save(){
	
}