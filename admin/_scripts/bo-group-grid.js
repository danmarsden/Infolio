/* $Id: bo-group-grid.js 631 2009-06-19 06:41:07Z richard $ */

dojo.require("dojo.number");
dojo.require("dijit.Dialog");
dojo.require("dijit.form.Button");		

var id;

function showDeleteMsg(groupId) {
	dijit.byId('dialogDeleteConfirmation').show();
	id = groupId;
}

function hideDeleteMsg() {
	dijit.byId('dialogDeleteConfirmation').hide();
}

function deleteGroup() {
	dojo.xhrPost({
		url:ajaxProcessDispatcher,
		content:{
			a: "Group",
			id: id,
			operation: "delete"	//check if it's insertion or alteration
		},
		timeout:1000,
		error: function(error){
			console.error ('Error: ', error);
		},
		load: function(data) {
			deletedId = dojo.number.parse(data);
			if (isNaN(deletedId)) {
				showNotification("Error", "Could not delete group.", null);
			}
			else {
				dojo._destroyElement('g' + deletedId);
				showNotification("Success", "Group has been deleted.", null);
			}
		}
	});
	dijit.byId('dialogDeleteConfirmation').hide();
}

$(document).ready(function(){         
	$("#groupTable").tablesorter({
		headers: {
			0:{ sorter:false},
			4:{ sorter:false}
		}
	})
	.tablesorterPager({container: $("#pager")});
}); 