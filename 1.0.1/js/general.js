function edit(placeholder_id,what,state){
	switch(what){
		case "name":
			if(state=="focus"){
				var currentName = document.getElementById("placeholder-currentName-"+placeholder_id).value;
				document.getElementById("placeholder-name-"+placeholder_id).innerHTML = "<input type=\"text\" name=\"new_name\" id=\"new_name\" size=\"30\" value=\""+currentName+"\" /><a onclick=\"edit("+placeholder_id+",'name','blur')\" style=\"cursor:pointer;\">[X]</a>";
			} else if(state=="blur"){
				var currentName = document.getElementById("placeholder-currentName-"+placeholder_id).value;
				var newName = document.getElementById("new_name").value;
				if(newName!=currentName){/* NAME CHANGED */save(what,placeholder_id,newName);}
				if(currentName.length>29){currentName=currentName.substr(0,25)+"...";}
				document.getElementById("placeholder-name-"+placeholder_id).innerHTML = "<a onclick=\"edit("+placeholder_id+",'name','focus');\" title="+currentName+"\">"+currentName+"</a>";
			}
		break;

		case "source":
			if(state=="focus"){
				var currentSource = document.getElementById("placeholder-currentSource-"+placeholder_id).value;
				document.getElementById("placeholder-source-"+placeholder_id).innerHTML = "<input type=\"text\" name=\"new_source\" id=\"new_source\" size=\"10\" value=\""+currentSource+"\" /><a onclick=\"edit("+placeholder_id+",'source','blur')\" style=\"cursor:pointer;\">[X]</a>";
			} else if(state=="blur"){
				var currentSource = document.getElementById("placeholder-currentSource-"+placeholder_id).value;
				var newSource = document.getElementById("new_source").value;
				if(newSource!=currentSource){/* NAME CHANGED */save(what,placeholder_id,newSource);}
				if(currentSource.length>29){currentSource=currentSource.substr(0,25)+"...";}
				document.getElementById("placeholder-source-"+placeholder_id).innerHTML = "<a onclick=\"edit("+placeholder_id+",'source','focus');\" title="+currentSource+"\">"+currentSource+"</a>";
			}
		break;

		case "type":
			if(state=="focus"){
				var currentType = document.getElementById("placeholder-currentType-"+placeholder_id).value;
				if(currentType=="image"){
					document.getElementById("placeholder-type-"+placeholder_id).innerHTML = "<select name=\"new_type\" id=\"new_type\" size=\"1\"><option value=\"image\" selected=\"selected\">Image</option><option value=\"vimeo\">Vimeo</option><option value=\"youtube\" selected=\"selected\">Youtube</option></select><a onclick=\"edit("+placeholder_id+",'type','blur')\" style=\"cursor:pointer;\">[X]</a>";
				} else if(currentType=="vimeo"){
					document.getElementById("placeholder-type-"+placeholder_id).innerHTML = "<select name=\"new_type\" id=\"new_type\" size=\"1\"><option value=\"image\">Image</option><option value=\"vimeo\" selected=\"selected\">Vimeo</option><option value=\"youtube\" selected=\"selected\">Youtube</option></select><a onclick=\"edit("+placeholder_id+",'type','blur')\" style=\"cursor:pointer;\">[X]</a>";
				} else if(currentType=="youtube"){
					document.getElementById("placeholder-type-"+placeholder_id).innerHTML = "<select name=\"new_type\" id=\"new_type\" size=\"1\"><option value=\"image\">Image</option><option value=\"vimeo\">Vimeo</option><option value=\"youtube\" selected=\"selected\">Youtube</option></select><a onclick=\"edit("+placeholder_id+",'type','blur')\" style=\"cursor:pointer;\">[X]</a>";
				}
			} else if(state=="blur"){
				var currentType = document.getElementById("placeholder-currentType-"+placeholder_id).value;
				var newType = document.getElementById("new_type").value;
				if(newType!=currentType){/* NAME CHANGED */save(what,placeholder_id,newType);}
				if(currentType.length>29){currentType=currentType.substr(0,25)+"...";}
				document.getElementById("placeholder-type-"+placeholder_id).innerHTML = "<a onclick=\"edit("+placeholder_id+",'type','focus');\" title="+currentType+"\">"+currentType+"</a>";
			}
		break;
		
		case "story":
			if(state=="focus"){
				var currentStory = document.getElementById("placeholder-currentStory-"+placeholder_id).value;
				document.getElementById("placeholder-story-"+placeholder_id).innerHTML = "<textarea name=\"new_story\" id=\"new_story\" cols=\"20\" rows=\"5\">"+currentStory+"</textarea><a onclick=\"edit("+placeholder_id+",'story','blur')\" style=\"cursor:pointer;\">[X]</a>";
			} else if(state=="blur"){
				var currentStory = document.getElementById("placeholder-currentStory-"+placeholder_id).value;
				var newStory = document.getElementById("new_story").value;
				if(newStory!=currentStory){/* NAME CHANGED */save(what,placeholder_id,newStory);}
				if(currentStory.length>29){currentStory=currentStory.substr(0,25)+"...";}
				document.getElementById("placeholder-story-"+placeholder_id).innerHTML = "<a onclick=\"edit("+placeholder_id+",'story','focus');\" title="+currentStory+"\">"+currentStory+"</a>";
			}
		break;
	}
}

function save(what,entry_id,value){
	var ajaxRequest;  // The variable that makes Ajax possible!
	
	try{
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		// Internet Explorer Browsers
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// Something went wrong
				alert("Your browser broke!");
				return false;
			}
		}
	}
	// Create a function that will receive data sent from the server
	ajaxRequest.onreadystatechange = function(){
		if(ajaxRequest.readyState == 4){
			//var ajaxDisplay = document.getElementById('ajaxDiv');
			//alert("Entry #"+entry_id+" has been saved with value: "+value);	
			//alert(ajaxRequest.responseText);	
			window.location.reload();
			//ajaxDisplay.innerHTML = ajaxRequest.responseText;
		}
	}
	var queryString = "?what="+what+"&entry_id=" + entry_id + "&value=" + value;
	ajaxRequest.open("GET", "update.php" + queryString, true);
	ajaxRequest.send(null); 
}