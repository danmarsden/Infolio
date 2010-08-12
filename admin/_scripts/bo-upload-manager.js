$(document).ready(function(){

	// Add actions to elements such as click events	
	applyActionsToElements();
});


function applyActionsToElements(){

	// Add click event on all links that are required to move an item to batch edit list

	



	$("#selectall").click(function(event){
		
		listItems = new Array();
		
		$("#batchcontainer ul#batchitems").empty();
		
		numberOfItems = $("#numofitems").val();
		
		$("#container span.tags").hide();


		for (var i=1; i<=numberOfItems; i++){
			$("#item" + i).removeClass("batchfirst");
			$("#item" + i).addClass("batch");
			listItems.push(addItemForBatchProcessingRefined($("#image" + i)));
		}
		
		listItems.reverse();
		
		$("#batchcontainer ul#batchitems").prepend(listItems.join(""));
	
		$("#batchcontainer a.close").click(function(){
			var itemToBeRemoved = $(this).parent().attr("id").replace(/batchimage/, "item");

			$(this).parent().remove();			
			$("#" + itemToBeRemoved).removeClass("batch");

			if($("#batchitems li").length == 0){
				$("#batchinfo").text("No files selected");
				$("#batchitems").remove();
				$("#batchactions").hide();
				$("#clearBatch").hide();
			}
			return false;
		});
		
		$("#batchactions").show();
		$("#clearBatch").show();

		return false;
	});


	$("#container p.image").click(function(event){
		if($("#container").hasClass("thumbs")){
			if (event.shiftKey) {
				var startSelection = $("#container div[id^='item'][class='item batchfirst batch']").attr("id").replace(/item/, "");
				var endSelection = $(this).parent().attr("id").replace(/item/, "");
				var storeStartSelection = ""

				if(parseInt(startSelection) > parseInt(endSelection)){
					storeStartSelection = startSelection;
					startSelection = endSelection;
					endSelection = storeStartSelection;
				}
				
				$("#container span.tags").hide();
				
				listItems = new Array();
				
				for (var i=parseInt(startSelection); i<=parseInt(endSelection); i++){
					$("#item" + i).removeClass("batchfirst");
					$("#item" + i).addClass("batch");
					
					var fineToAdd = checkIfItemExistsInBatchList($("#image" + i).next().next().next().find("a"));				
					
					if(fineToAdd){
						listItems.push(addItemForBatchProcessingRefined($("#image" + i)));
						
					}else{
						// dont add because it's already going to be processed

					}
					
				}
				
				if(storeStartSelection == ""){
					listItems.reverse();
					$("#batchcontainer ul#batchitems").prepend(listItems.join(""));
				}else{
					listItems.reverse();
					$("#batchcontainer ul#batchitems").append(listItems.join(""));
				}
				
				
				
				$("#batchcontainer a.close").click(function(){
					var itemToBeRemoved = $(this).parent().attr("id").replace(/batchimage/, "item");
					
					$(this).parent().remove();			
					$("#" + itemToBeRemoved).removeClass("batch");

					if($("#batchitems li").length == 0){
						$("#batchinfo").text("No files selected");
						$("#batchitems").remove();
						$("#batchactions").hide();
						$("#clearBatch").hide();
					}
					return false;
				});

				$("#batchactions").show();
				$("#clearBatch").show();
				


			}else{
				$("#container div[id^='item']").removeClass("batchfirst");
				
				$(this).parent().addClass("batchfirst");
				$(this).parent().toggleClass("batch");
				
				if($(this).parent().hasClass("batch")){
					var fineToAdd = checkIfItemExistsInBatchList($(this).next().next().next().find("a"));
					if(fineToAdd){
						addItemForBatchProcessing($(this).next().next().next().find("a"));
					}else{
						// dont add because it's already going to be processed
					}
				}else{
					$("#container div[id^='item']").removeClass("batchfirst")
					var itemToBeRemoved = $(this).attr("id");
					$("#batch" + itemToBeRemoved).remove();			
					$("#" + itemToBeRemoved).parent().removeClass("batch");

					if($("#batchitems li").length == 0){
						$("#batchinfo").text("No files selected");
						$("#batchitems").remove();
						$("#batchactions").hide();
						$("#clearBatch").hide();
					}
					return false;
				}
			}
		}
	});


	$("#viewOptions #thumbs a").click(function(){
		$("#container").addClass("thumbs");
		$("#container").removeClass("list");
		$(this).parent().parent().find("li").removeClass("selected");
		$(this).parent().addClass("selected");
		return false;
	});
	
	$("#viewOptions #list a").click(function(){
		$("#container").addClass("list");
		$("#container").removeClass("thumbs");
		$(this).parent().parent().find("li").removeClass("selected");
		$(this).parent().addClass("selected");
		return false;
	});
	
	$("#container .closeTags").click(function(){
		$(this).parent().hide();
	});

	$("#container .selectbatch").click(function(){
		if($("#batchinfo").text() == "No files selected"){
			// If info "No files added" then we can just add the item
			addItemForBatchProcessing(this);
		}else{
			// else we need to check if item exists then add the item at the top
			var fineToAdd = checkIfItemExistsInBatchList(this);
			if(fineToAdd){
				addItemForBatchProcessing(this);
			}else{
				// dont add because it's already going to be processed
				
			}
		}
		return false;
	});
	
	$("#batchactions #edit").click(function(){
	// this controls the batch edit dialog box
		if($("#batchitems").length > 0){
			$("#overlay").show();
			$("#info").show();
			$("#info").append(htmlOverlayFragments("Batch edit"));
			
			$("#info #exit").click(function(){
				$("#overlay").hide();
				$("#info").hide();
				$("#info").empty();
				$("#viewOptions li").removeClass("selected");
				$("#viewOptions li#list").addClass("selected");
				$("#container").removeClass("thumbs");
				$("#container").addClass("list");
			});
			
			$("#info #rename").click(function(){
				// When renaming there are two loops one to get the selected items then 1 to set the items
				
				var numItemsModidied = $("#batchitems li").length;				
				
				if(trim($("#newtitle").val()) != ""){;
				
					var answer = confirm("Are you sure you want to rename " + numItemsModidied + " file(s)?");
				
					if(answer){
						itemsToBeRenamed = new Array();
						
						//$("#batchitems li").each( function(i){
						//	itemsToBeRenamed[i] = $(this).attr("id").replace(/batch/, "");
						//});

						newTitle = $("#newtitle").val();
		
						$("#batchitems li").each( function(i){
							itemToBeRenamed = $(this).attr("id").replace(/batchimage/, "title");
							$("#" + itemToBeRenamed).val(newTitle);
							//$(textArea).val($(textArea).val() + tagName + ",")								
						});
						
						$("#infoText").addClass("warning");
						
						$("#infoText").text(numItemsModidied + " file(s) renamed to '" + $("#newtitle").val() + "'");


						$("#newtitle").val("");

						//$("#overlay").hide();
						//$("#info").hide();
						//$("#info").empty();
						//$("#batchitems").remove();
						//$("#batchinfo").text("No files selected");
						//$("#container div").removeClass("batch");
						//$("#batchactions").hide();
						//$("#clearBatch").hide();
						//$("#container").removeClass("thumbs");
						//$("#container").addClass("list");
						//$("#viewOptions li").removeClass("selected");
						//$("#viewOptions li#list").addClass("selected");
					}
				}else{
					alert("Please enter a new name");
				}
			});
			
			$("#info #addtag").click(function(){
			
				var tagName = $("#newtag").val();
				var numItemsModidied = $("#batchitems li").length;
				if(trim(tagName) != "" || trim(tagName) == ","){
					var answer = confirm("Are you sure you want to add a new tag '" + tagName + "' to your selected items");
					// got to get the selected items
					if(answer){
						itemsToBeRenamed = new Array();
						$("#batchitems li").each( function(i){
							itemsToBeRenamed[i] = $(this).attr("id").replace(/batchimage/, "tags");
						});

						$(itemsToBeRenamed).each( function(i){
							addTag($("#" + itemsToBeRenamed[i]), tagName);
						});
						
						$("#infoText").addClass("warning");
						$("#infoText").text(numItemsModidied + " file(s) tagged as '" + tagName + "'");

						//$("#overlay").hide();
						//$("#info").hide();
						//$("#info").empty();
						//$("#batchitems").remove();
						//$("#batchinfo").text("No files selected");
						//$("#container div").removeClass("batch");
						//$("#batchactions").hide();
						//$("#clearBatch").hide();
						//$("#container").removeClass("thumbs");
						//$("#container").addClass("list");
						//$("#viewOptions li").removeClass("selected");
						//$("#viewOptions li#list").addClass("selected");
					}
				}else{
					alert("Please enter a tagname")
				}
			
			});
			

		}
	});
	
	$("#batchcontainer #clearBatch").click(function(){
	
		// remove all items from list
		$("#container div").removeClass("batch");
		$("#batchinfo").text("No files selected");
		$("#batchitems").remove();
		$("#batchactions").hide();
		$("#clearBatch").hide();
		return false;
	
	});
	
	
	$("#container a.picktags").click(function(){
	
		
		$("span.tags").clone(true).insertAfter(this);
			
		var textArea = $(this).parents("div[id^='item']").find("textarea");
		arrayTags = $(textArea).val().split(",");
		arrayListTags =$(this).next().find("li"); 
		$(this).next().find("li").removeClass("selected");
		
		for (var i=0; i<arrayTags.length; i++){
			if(trim(arrayTags[i]) != ""){
				for (var j=0; j<arrayListTags.length; j++){
					if(trim($(arrayListTags[j]).text()) == trim(arrayTags[i])){
						$(arrayListTags[j]).addClass("selected");
					}
				}
			}
		}

		$("#container a.picktags").next().hide();
		$(this).next().show();	
		
		return false;
	});
	
	$("#container span li a").click(function(){
	
		// add a tag to the textarea
		var tagName = $(this).text();
		var textArea = $(this).parents("div[id^='item']").find("textarea")
		addTag(textArea, tagName);
		return false;
		
	});
	
	
	$("#saveAll").click(function(){
		
		$("#overlay").show();
		$("#info").show();
		$("#info").append(htmlOverlayFragments("Save files"));		
			
		var items = new Array();
		var completedItems = new Array();
		var totalItemsToUpload;
		var concurrentItems = 5;

		$("#container div.item").each( function(i){
			var itemInfo = new Array();
			
			itemInfo[0] = $("input[name='filename']",this).val();
			itemInfo[1] = $("input[name='title']",this).val();
			itemInfo[2] = $("input[name='description']",this).val();
			itemInfo[3] = $("textarea[name='tags']",this).val();
			
			items.push(itemInfo);
			totalItemsToUpload = items.length;
		});
		
		$("#testingasync").ajaxComplete(function(evt, request, settings){
		
			if(settings.url == "/admin/ajax/asset_create_from_upload.action.php"){
				
				completedItems.push("Completed");
											
				if(items.length > 0){
					var itemInfo = items.pop();
					var filename = itemInfo[0];
					var title = itemInfo[1];
					var description = itemInfo[2];
					var tags = itemInfo[3];
					$.post("/admin/ajax/asset_create_from_upload.action.php", {filename: filename, title: title, description: description, tags: tags});
				}else{	
					if(totalItemsToUpload == completedItems.length){
						$("#savefiles").remove();
						$("#info").html("<h2>Completed bulk upload</h2>");
						$("#info h2").after("<p><input id='CloseAll' type='submit' value='Close' /></p>");
						$("#CloseAll").click(function(){
							$("#overlay").hide();
							$("#info").hide();
							$("#info").empty();
							$("#batchinfo").text("No files selected");
							$("#batchitems").remove();
							$("#batchactions").hide();
							$("#clearBatch").hide();
						});
					}					
				}
			}else{
			
			}
		});
		
		

		$("#testingasync").ajaxSuccess(function(evt, request, settings){
			
			var responseTextArray = request.responseText.split("####");
						
			if(responseTextArray[1] != ""){
				strImageForSaving = responseTextArray[1];
			}else{
				strImageForSaving = responseTextArray[0];
			}
			
			if($("#savefiles li").length < 6){
				$("#savefiles").append("<li>File " + strImageForSaving +" added</li>");
			}else{
				$("#savefiles li:last-child").remove();
				$("#savefiles").append("<li>File " + strImageForSaving +" added</li>");	
			}
			
			$("#container input[value='" + responseTextArray[0] +"']").parent().parent().remove();

		});

		$("#testingasync").ajaxError(function(evt, request, settings){
			// Add in error messaging if required
		
		});
		
		for(var i=0; i<concurrentItems; i++){
			//$("#testingasync").append("<p>Spare threads available...</p>");
			if(items.length > 0){
				var firstItemInfo = items.pop();
				var filename = firstItemInfo[0];
				var title = firstItemInfo[1];
				var description = firstItemInfo[2];
				var tags = firstItemInfo[3];
				
				//$("#testingasync").append("<p>Spawning thread "+i+"</p>");
				$.post("/admin/ajax/asset_create_from_upload.action.php", {filename: filename, title: title, description: description, tags: tags});
			}
		}				
	});
	
}

function addTag(textArea, tagName){

	// Adding tags is case insensitive and handles comma seperation - also trims whitespace
	
	var tagExists = false;

	if($(textArea).val() == ""){
		// Empty text area just add tag
		$(textArea).val(tagName)	
	}else{
		
		$(textArea).val(trim($(textArea).val()));
		
		arrayTags = $(textArea).val().split(",");
		
		var lengthOfString = $(textArea).val().length;
	
		// loop through and see if tag already exists
	
		for (var i=0; i<arrayTags.length; i++){
			if(arrayTags[i] != ""){
				if(arrayTags[i].toLowerCase() == tagName.toLowerCase()){
					tagExists = true;	
				}
			}
		}
		
		// If tag exists then the character at the end is checked for comma and commas are added
		// where required
		
		if(!tagExists){
			if($(textArea).val().charAt(lengthOfString -1) == ","){
				$(textArea).val($(textArea).val() + tagName)	
			}else{
				$(textArea).val($(textArea).val() + "," + tagName)	
			}
		}
	}
}

function checkIfItemExistsInBatchList(batchselector){

	// Need to check ID so duplicates arent added
	
	var selectedItem = "#batch" + $(batchselector).parent().parent().find(".image").attr("id")
	if($("#batchitems li" + selectedItem).length == 0){
		return true;
	}else{
		return false;
	}
	
	
}

function addItemForBatchProcessingRefined(batchselector){	
	// adds the image into the batch process queue
	
	$(batchselector).parents("div[id^='item']").addClass("batch");

	// item is there and can be added
	// Remove info item

	$("#batchinfo").text("Files to be batch processed");

	if($("#batchcontainer ul").length == 1){
		$("#clearBatch").after("<ul id='batchitems'></ul>");
	}
	var item = $(batchselector);
	
	
	newListItem = "<li class='image' id='batch" + $(item).attr("id") + "'>" + item[0].innerHTML +" <a href='#' class='close'>Remove</a></li>";

	return newListItem
	
	
}
function addItemForBatchProcessing(batchselector){

	// adds the image into the batch process queue
	
	$(batchselector).parents("div[id^='item']").addClass("batch");

	// item is there and can be added
	// Remove info item

	$("#batchinfo").text("Files to be batch processed");

	if($("#batchcontainer ul").length == 1){
		$("#clearBatch").after("<ul id='batchitems'></ul>");
	}

	var item = $(batchselector).parent().parent().find(".image");
	
	
	$("#batchcontainer ul#batchitems").prepend("<li class='" + $(item).attr("class") +"' id='batch" + $(item).attr("id") + "'>" + $(item).html() +" <a href='#' class='close'>Remove</a></li>")
	//$("#batchcontainer ul#batchitems").prepend("<li class=''>" + $(item).html() +" <a href='#' class='close'>Remove</a></li>")


	$("#batchcontainer #batch" + $(item).attr("id")).click(function(){
		var itemToBeRemoved = $(item).attr("id");
		$(this).remove();			
		$("#" + itemToBeRemoved).parent().removeClass("batch");

		if($("#batchitems li").length == 0){
			$("#batchinfo").text("No files selected");
			$("#batchitems").remove();
			$("#batchactions").hide();
			$("#clearBatch").hide();
		}
		return false;
	});

	$("#batchactions").show();
	$("#clearBatch").show();


	return true
}

function htmlOverlayFragments(fragmentType){
	var fragmentString = "";
	var numItemsModidied = $("#batchitems li").length;
	
	switch(fragmentType)
	{
	case "Batch edit":
		
	
		fragmentString += "<h2>Batch edit (editing " + numItemsModidied +" photo(s))</h2>\n";
		fragmentString += "<p id='exit'><a href='#'>Exit</a></p>\n";
		fragmentString += "<h3 id='infoText'>&nbsp;</h3>\n";
		fragmentString += "<div>";
		fragmentString += "<h3>Rename</h3>\n";
	  	fragmentString += "<fieldset>\n";
	  	fragmentString += "<p><label for='rename'>New title</label><input id='newtitle' type='text' value='' /></p>\n";
	  	fragmentString += "<p><input id='rename' type='submit' value='Rename' /></p>\n";
	  	fragmentString += "</fieldset>\n";
	  	fragmentString += "</div>";
	  	fragmentString += "<div>";
	  	fragmentString += "<h3>Add tag</h3>\n";
	  	fragmentString += "<fieldset>\n";
	  	fragmentString += "<p><label for='newtag'>New tag</label><input id='newtag' type='text' value='' /></p>\n";
	  	fragmentString += "<p><input id='addtag' type='submit' value='Add tag' /></p>\n";
	  	fragmentString += "</fieldset>\n";
	  	fragmentString += "</div>";
	  break;
	case "Save files": 
	
		fragmentString += "<h2>Saving files (" + numItemsModidied +")</h2>\n";
	  	fragmentString += "<ul id='savefiles'></ul>";
	break;  
	default:
	  
	}
	return fragmentString;
}

function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}
