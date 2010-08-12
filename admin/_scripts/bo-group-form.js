/* $Id: bo-group-form.js 693 2009-07-13 11:54:27Z richard $ */

dojo.require("dojo.number");
dojo.require("dijit.form.ValidationTextBox");	
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.form.Button");
dojo.require("dijit.layout.TabContainer");
dojo.require("dijit.form.Textarea");
dojo.require("dojo.data.ItemFileWriteStore");
dojo.require("dijit.Tree");
dojo.require("dijit.Toolbar");
dojo.require("dijit.Dialog");

dojo.addOnLoad(function() {
	// Hide member tab if group not saved yet
	if (dojo.byId('id').value == '') {
		dijit.byId("tabMember").controlButton.domNode.style.visibility = "hidden";
	}
});


//function to handle cancellation
function doCancel()
{
	location.href = path.BACKOFFICE_INDEX_FILENAME + "?do=4";
}

//function to handle reset
function doReset()
{
	document.getElementById("form").reset();
}

function doSave()
{
	dojo.xhrPost({
		url: ajaxProcessDispatcher,
		form: "form",
		timeout: 1000,
		error: function(){
		},
		load: function(data) {
			newId = dojo.number.parse(data);
			if (isNaN(newId)) {
				showNotification("Error", "Group could not be saved");
			}
			else {
				showNotification("Success", "Group has been saved", null);
				// Change from create to update mode
				if (dojo.byId('operation').value != 'update') {
					dojo.byId('operation').value = 'update';
					dojo.byId('id').value = newId;
					dijit.byId('btnDelete1').domNode.style.display = "inline";
					dijit.byId('btnDelete2').domNode.style.display = "inline";
					dijit.byId("tabMember").controlButton.domNode.style.visibility = "visible";
				}
			}

		}
	});
}

function showDeleteMsg(){
	dijit.byId('dialogDeleteConfirmation').show();
}

function hideDeleteMsg(){
	dijit.byId('dialogDeleteConfirmation').hide();
}

function deleteGroup()
{
	dojo.xhrPost({
		url:ajaxProcessDispatcher,
		content:{
			a: "Group",
			id: dojo.byId("id").value,
			operation: "delete"	//check if it's insertion or alteration
		},
		timeout:1000,
		error: function(){
		},
		load: function(returnedId){
			var urlRedirection = "?do=4";
			showNotification("Success", "Group has been deleted", urlRedirection);
		}
	});
	dijit.byId('dialogDeleteConfirmation').hide();
}

function doAddUserToGroup(userOption)
{
	moveUsersAjax([userOption.value], "add_users")
	moveUser(userOption, "targetSelect", "doRemoveUserFromGroup(this);")
}
			
function doRemoveUserFromGroup(userOption)
{
	moveUsersAjax([userOption.value], "remove_users")
	moveUser(userOption, "sourceSelect", "addUserToGroup(this);")
}

function moveUser(userElement, toContainerId, functionName)
{
	userElement.setAttribute("ondblclick", functionName);
	document.getElementById(toContainerId).appendChild(userElement);
}

function moveUsersAjax(users, actionName)
{
	dojo.xhrPost({
		url:ajaxProcessDispatcher,
		content:{
			a: "Group",
			id: dojo.byId("id").value,
			ids: users.join(','),
			operation: actionName	// add_users/remove_users
		},
		timeout:1000,
		error: function(er){
			console.error(er);
			// ToDo: better error handling.
			// Inform user and refresh page
		},
		load: function(data) {
			// console.info('Went ok');
		}
	});							
}

function doAddUsersToGroup()
{
	var elementIds = new Array();
	dojo.query("#sourceSelect option").forEach(
		function(inputElement) {
			if(inputElement.selected) {
				moveUser(inputElement, "targetSelect", "doRemoveUserFromGroup(this);");
				elementIds.push(inputElement.value);
			}
		}
	);
	moveUsersAjax(elementIds, "add_users");
}

function doRemoveUsersFromGroup()
{
	var elementIds = new Array();
	dojo.query("#targetSelect option").forEach(
		function(inputElement) {
			if(inputElement.selected) {
				moveUser(inputElement, "sourceSelect", "addUserToGroup(this);");
				elementIds.push(inputElement.value);
			}
		}
	);
	moveUsersAjax(elementIds, "remove_users");
}

function updateSourceByKeyword(keyword)
{
	dojo.query("#sourceSelect option").forEach(
		function(inputElement) {
			console.info(inputElement.text + ": " + inputElement.text.indexOf(keyword));
			if( inputElement.text.indexOf(keyword) < 0 ) {
				inputElement.style.display = "none";
			}
			else {
				inputElement.style.display = "block";
			}
		}
	);
}