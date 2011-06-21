/** Draw the annotation popup */
function annotate(){
	if(!isSignedIn()){
		alert("You need to be signed in to do that!");
		signIn(); //user.js
	}
	$("textarea.note").val("");
	$("input.tags").val("");
	$("#annotate").css("top", highlightStartY-25);
	$("#annotate").css("z-index", 2000);
	$("#annotate").show();
	return false;
}

function submitAnnotation(event){
	event.preventDefault();
	if(!isSignedIn()){
		alert("You need to be signed in to do that!");
		signIn(); //user.js
	}else{
		$("#annotate").hide();
		var data = {
			narrative:narrativeID,
			start:startSentence,
			end:endSentence,
			startpos:startPosition,
			endpos:endPosition,
			username:localStorage["username"]
		}
		var note = $("textarea.note").val();
		var tags = $("input.tags").val();
		if(note.trim().length>0){
			data['note'] = note;
		}
		if(tags.trim().length>0){
			data['tags'] = tags;
		}
		$.getJSON("src/php/annotate.php", data, displayAnnotations);	
	}
}
/** Get the annotations belonging to a certain narrative */
function displayAnnotations(){
	getAllTags();//util.js (for autocomplete)
	if($.url.param("id")){
		$.getJSON("src/php/getnarrativeannotations.php", 
		{
			narrative: $.url.param("id").split("_")[0]
		}, 
		displayNarrativeAnnotations)	
	}
}


/** Display the annotations made to a narrative alongside it**/
function displayNarrativeAnnotations(data){
	$('#display-annotations').html("");
	$("span.annotated").removeClass("annotated");
	var info = [],
	html = "", 
	startSentID = -1,
	endSentID = -1,
	id = -1,
	tableID = "",
	startSentY = -1;
	// for each highlight
	for(var i = 0; i < data.length; i++){
		try {
			info = data[i];
			// higlight the appropriate section
			if(parseInt(info.start) == parseInt(info.end)){
				id = "#"+info.start;
				$(id).children("span.word").each(function(){
					if(parseInt($(this).attr("position"))>= parseInt(info.startpos) && parseInt($(this).attr("position"))<=parseInt(info.endpos)){
						$(this).addClass("annotated");
					}
				})
			}
			else{
				for(var k = parseInt(info.start); k <= parseInt(info.end); k++){
					id = "#"+k;
					if(k > parseInt(info.start) && k < parseInt(info.end)){
						$(id).addClass("annotated");
					}
					else if(k == parseInt(info.start)){
						$(id).children("span.word").each(function(){
							if(parseInt($(this).attr("position")) >= parseInt(info.startpos)){
								$(this).addClass("annotated");
							}
						})
					}
					else if(k == parseInt(info.end)){
						$(id).children("span.word").each(function(){
							if(parseInt($(this).attr("position")) <= parseInt(info.startpos)){
								$(this).addClass("annotated");
							}
						})
					}
				}
			}
			// display the tags and notes	
			html = '<div class="annotation">';
			if(isSignedIn() && info.username == localStorage["username"]){
				html += '<img class="icon annotation delete" title="delete this annotation" src="img/trash.png">';
			}
			html += '<img class="icon annotation" src="img/highlight.png">';
			html += '<div name="highlight'+info.id+'" class="highlight annotation dialog triangle-border left">';
			//buttons
			html +='<img class = "button close" src="img/close.png"><img class="button icon add note" title="add a note" src="img/addnote.png"><img class="button icon add tag" title="add a tag" src="img/addtag.png">';
			if(info.notes.length>0 || info.tags.length > 0){	
				html +='<table class="annotation">';
				// display the notes
				for(var j = 0; j < info.notes.length; j++){
					html += '<tr class="note" note="'+info.notes[j].id+'"><td>';
					html += '<span class="user">'+info.notes[j].username+'</span>';
					html += '</td><td>';
					if(isSignedIn() && info.notes[j].username == localStorage["username"]){
						html += '<span class="note editable">';
					}else{
						html += '<span class="note">';	
					}
					html += info.notes[j].note+"</span>";
					if(isSignedIn() && info.notes[j].username == localStorage["username"]){
						html += '</td><td><img class="icon trash note" title="delete this note" note="'+info.notes[j].id+'" src="img/trash.png">'
					}
					html += "</td></tr>";
				}
				// display the tags
				if(info.tags.length > 0){
					html += '<tr class="tag"><td> <img class="icon edit tag" src="img/tag.png"></td><td>';
					for(var j = 0; j < info.tags.length; j++){
						if(isSignedIn() && info.tags[j].username == localStorage["username"]){
							html += '<span tag="'+info.tags[j].id+'" class="tag editable">'+info.tags[j].tag+'<img class="tinyicon delete" title="delete this tag" src="img/delete.png"></span>';
						}else{
							html += '<span tag="'+info.tags[j].id+'" class="tag">'+info.tags[j].tag+"</span>";
						}
					}
					html += "</td></tr>";
				}
				html += '</table>';
			}
			html += '</div></div>';
			$('#display-annotations').append(html);
			divID = 'div[name="highlight'+info.id+'"]';
			startSentID = "#"+info.start;
			startSentY = $(startSentID).children('span.word[position="'+info.startpos+'"]').offset()["top"];
			$(divID).parent().css("top", startSentY-45);
			$('.close').click(closeParent);
			$(divID).show();
		} catch(error){
			//do nothing.
		};
	}
	//interactivity
	$('img.annotation').toggle(
		function(){$(this).parent().children("div.dialog").show();},
		function(){$(this).parent().children("div.dialog").hide();}
	)
	$(".annotation").click(function(e){
		var largestZ = 1; 
		$("div.annotation").each(function(i) {
			var currentZ = parseFloat($(this).css("z-index"));
			largestZ = currentZ > largestZ ? currentZ : largestZ;
		});
		$(this).css("z-index", largestZ + 1);
	});
	$('span.tag > img.delete').click(deleteTag);
	$('img.trash.note').click(deleteNote);
	$('span.note.editable').click(editNote);
	$('img.annotation.delete').click(deleteAnnotation);
	$('img.add.tag').click(addTags);
	$('img.add.note').click(addNote);
}
/** delete a tag **/
function deleteTag(){
	var tagID = $(this).closest("span.tag").attr("tag");
	var highlightID = $(this).closest('div.highlight').attr("name").replace("highlight", "");
	$.getJSON('src/php/editannotations.php',{
		event:'delete-tag',
		tag:tagID,
		highlight:highlightID
	},
	function(data){
		if(data.error){
			if(data.error =="no-error"){
				displayAnnotations();
			}else{
				alert(data.error)
			}
		}else{
			alert("Internal server error");
		}
	});
}

/** add tags **/
function addTags(){
	$("#add-tags").remove();
	$("#submit-tags").remove();
	$("#cancel-edit").remove();
	$("#tag-label").remove();
	if(!isSignedIn()){
		alert("You need to be signed in to do that!");
		signIn(); //user.js
	}else{
		$(this).after('<label id="tag-label">Enter tags (comma separated)</label><input id="add-tags" class="tags autocomplete"></textarea><input type="button" id="cancel-edit" value="Cancel"><input type="button" id="submit-tags" value="OK">');
		$("#cancel-edit").click(function(){
			$("#add-tags").remove();
			$("#submit-tags").remove();
			$("#tag-label").remove();
			$("#cancel-edit").remove();
		})
		$("#submit-tags").click(function(){
			var tags = $("#add-tags").val().replace(/'/g, '\'').replace(/"/g, "\"");
			var highlightID = $(this).closest('div.highlight').attr("name").replace("highlight", "");
			$(this).remove();
			$.getJSON('src/php/editannotations.php',{
				event:'add-tags',
				highlight:highlightID,
				tags:tags,
				user:localStorage['username']
			},
			function(data){
				if(data.error){
					if(data.error =="no-error"){
						displayAnnotations();
					}else{
						alert(data.error)
					}
				}else{
					alert("Internal server error");
				}
			});
		})	
	}
}

/**delete a note**/
function deleteNote(){
	var noteID = $(this).closest("tr.note").attr("note");
	var highlightID = $(this).closest('div.highlight').attr("name").replace("highlight", "");
	$.getJSON('src/php/editannotations.php',{
		event:'delete-note',
		note:noteID,
		highlight:highlightID
	},
	function(data){
		if(data.error){
			if(data.error =="no-error"){
				displayAnnotations();
			}else{
				alert(data.error)
			}
		}else{
			alert("Internal server error");
		}
	});
}
/** edit a note **/
function editNote(){
	$("#edit-note").remove();
	$("#submit-note").remove();
	$("#cancel-edit").remove();
	$(this).after('<textarea id="edit-note" class="note">'+$(this).text()+'</textarea><input type="button" id="cancel-edit" value="Cancel"><input type="button" id="submit-note" value="OK">');
	$(this).hide();
	$("#cancel-edit").click(function(){
		$("#edit-note").remove();
		$("#submit-note").remove();
		$(this).siblings("span.note").show();
		$(this).remove();
	})
	$("#submit-note").click(function(){
		var noteText = $("#edit-note").val().replace(/'/g, '\'').replace(/"/g, "\"");
		var noteID = $(this).closest("tr.note").attr("note");
		$("#edit-note").remove();
		$("#cancel-edit").remove();
		$(this).remove();
		$.getJSON('src/php/editannotations.php',{
			event:'edit-note',
			note:noteID,
			text:noteText
		},
		function(data){
			if(data.error){
				if(data.error =="no-error"){
					displayAnnotations();
				}else{
					alert(data.error)
				}
			}else{
				alert("Internal server error");
			}
		});
	})
}

/** add a note **/
function addNote(){
	$("#edit-note").remove();
	$("#submit-note").remove();
	$("#cancel-edit").remove();
	if(!isSignedIn()){
		alert("You need to be signed in to do that!");
		signIn(); //user.js
	}else{
		$(this).after('<textarea class="note" id="add-note"></textarea><input type="button" id="cancel-edit" value="Cancel"><input type="button" id="submit-note" value="OK">');
		$("#cancel-edit").click(function(){
			$("#add-note").remove();
			$("#submit-note").remove();
			$(this).remove();
		})
		$("#submit-note").click(function(){
			var noteText = $("#add-note").val();
			var noteID = $(this).closest("tr.note").attr("note");
			var highlightID = $(this).closest('div.highlight').attr("name").replace("highlight", "");
			$("#add-note").remove();
			$("#cancel-edit").remove();
			$(this).remove();
			$.getJSON('src/php/editannotations.php',{
				event:'add-note',
				highlight:highlightID,
				text:noteText,
				user:localStorage['username']
			},
			function(data){
				if(data.error){
					if(data.error =="no-error"){
						displayAnnotations();
					}else{
						alert(data.error)
					}
				}else{
					alert("Internal server error");
				}
			});
		})	
	}
}

/** delete an annotation **/
function deleteAnnotation(){
	var highlightID = $(this).parent().children('div.highlight').attr("name").replace("highlight", "");
	$.getJSON('src/php/editannotations.php',{
		event:'delete-annotation',
		highlight:highlightID
	},
	function(data){
		if(data.error){
			if(data.error =="no-error"){
				displayAnnotations();
			}else{
				alert(data.error);
			}
		}else{
			alert("Internal server error");
		}
	});
}


/** Show all the annotations of all the narratives */
function listAnnotations(){
	$.get("src/php/listannotations.php", {}, function(html){
		$("#annotations").html(html);
		sorttable.makeSortable($('#notes-listing').get(0));
		sorttable.makeSortable($('#tags-listing').get(0));
		
	} )
} 
