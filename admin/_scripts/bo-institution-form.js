/* $Id: bo-institution-form.js 814 2009-11-04 22:14:39Z richard $ */

//call all required dojo library
dojo.require("dijit.form.Form");
dojo.require("dijit.form.TextBox");
dojo.require("dijit.form.ValidationTextBox");
dojo.require("dijit.Toolbar");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.layout.TabContainer");
dojo.require("dijit.Tooltip");
dojo.require("dojo.number");

//variable to hold selected item object
var selectedItem;

//function to handle cancellation
function doCancel()
{
	location.href="?do=7";
}
//function to handle reset
function doReset()
{
	document.getElementById("form").reset();
}


function doSubmit()
{
	if(dijit.byId('form').validate()) {
		dojo.xhrPost({
			url:ajaxProcessDispatcher,
			content:{
				a: "institution",
				name: dijit.byId("name").value,
				url: dijit.byId("url").value,
				id: dojo.byId("id").value,
                share: dojo.byId("share").value,
                comment: dojo.byId("comment").value,
                commentapi: dojo.byId("commentapi").value,
				operation: (dojo.byId("id").value!="") ? 'update' : 'insert'	//check if it's insertion or alteration
			},
			timeout:2000,
			error: function(){
				showNotification("Error", "Could not save.", null);
			},
			load: function(data){
				newId = dojo.number.parse(data);
				if (isNaN(newId)) {
					showNotification("Error", "<p>Could not save.</p><p>" + data + "</p>", null);
				}
				else {
					var urlRedirection = "?do=7";

					showNotification(
						"Success",
						(dojo.byId("id").value!="") ? dojo.byId("name").value + " has been saved" : dojo.byId("name").value + " has been created",
						urlRedirection);
				}
			}
		});
	}
}

function deleteRow(rowId, next)
{
	if(next==""){
		dijit.byId('dialogDeleteConfirmation').show();
		id=rowId;
	}
	else if(next=="yes"){
		dojo.xhrPost({
			url:ajaxProcessDispatcher,
			content:{
				a: "institution",
				id: dojo.byId("id").value,
				operation: "delete"	//check if it's insertion or alteration
			},
			timeout:1000,
			error: function(){
			},
			load: function(returnedId){
				var urlRedirection="?do=7";
				showNotification(
					"Success",
					"Institution has been deleted",
					urlRedirection);
			}
		});
		dijit.byId('dialogDeleteConfirmation').hide();
	}
	else if(next=="no") dijit.byId('dialogDeleteConfirmation').hide();
}