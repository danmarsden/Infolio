/* $Id: bo-template-grid.js 690 2009-07-13 07:28:14Z richard $ */

dojo.require("dijit.Dialog");
dojo.require("dijit.form.Button");
		
var id;

// Start delete - show confirm question box
function doDelete(rowId)
{
	id = rowId;
	showYesNoBox('Delete template',
			'Are you sure you want to delete this template?',
			doDeleteConfirm);
}

function doDeleteConfirm()
{
	dojo.xhrPost({
		url:ajaxProcessDispatcher,
		content:{
			a: "Template",
				id: id,
				operation: "delete"
			},
		timeout:1000,
		error: function() {
		},
		load: function(data) {
			if(data=='1') {
				showNotification("Success", "Template has been deleted");
				dojo._destroyElement('t' + id);
			}
			else {
				showNotification("Error", "Template was not deleted");
			}
		}
	});
	dijit.byId('dialogDeleteConfirmation').hide();
}