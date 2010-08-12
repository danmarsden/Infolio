/* $Id: bo-user-grid.js 262 2008-11-24 17:03:50Z richard $ */

// Script does sorting and deleting users

dojo.require("dojo.number");
dojo.require("dijit.Dialog");
dojo.require("dijit.form.Button");
var id;

function doDelete(rowId){
	if(confirm("Are you sure you want to delete this user?")){
		dojo.xhrGet ({
			url: path.AJAX_DISPATCHER + "?a=User&operation=delete&id=" + rowId,
			load: function (data) {
				deletedId = dojo.number.parse(data);
				if (isNaN(deletedId)) {
					showNotification("Error", "Could not delete user.", null);
				}
				else {
					dojo._destroyElement('u' + deletedId);
					showNotification("Success", "User has been deleted.", null);
				}
			},
			error: function (error) {
				console.error ('Error: ', error);
			}
		});				
	}
}	

$(document).ready(
	function(){
		// Add sorting to user data grid
		$("#dataGridTable")
			.tablesorter(
				{ headers: {
					4:{ sorter:false}								
				}
			}
		)
		.tablesorterPager({container: $("#pager")});
	} 
); 