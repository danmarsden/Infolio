/* $Id: bo-institution-grid.js 551 2009-05-16 20:12:42Z richard $ */

dojo.require("dijit.Dialog");
dojo.require("dijit.form.Button");
dojo.require("dojo.number");
var id;

function onDelete(rowId)
{
	dijit.byId('dialogDeleteConfirmation').show();
	id=rowId;
}

function onDeleteCancel()
{
	dijit.byId('dialogDeleteConfirmation').hide();
}

function onDeleteConfirm()
{
	dojo.xhrPost({
		url:ajaxProcessDispatcher,
		content:{
			a: "institution",
			id: id,
			operation: "delete"	//check if it's insertion or alteration
		},
		timeout:1000,
		error: function(){
		},
		load: function(data){
			deletedId = dojo.number.parse(data);
			if (isNaN(deletedId)) {
				showNotification("Error", "Could not delete institution.", null);
			}
			else {
				dojo._destroyElement('i' + deletedId);
				showNotification("Success", "Institution has been deleted.", null);
			}
		}
	});
	dijit.byId('dialogDeleteConfirmation').hide();
}