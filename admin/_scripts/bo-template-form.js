/* $Id: bo-template-form.js 747 2009-08-05 20:32:38Z richard $ */

dojo.require("dojo.data.ItemFileWriteStore");
dojo.require("dojo.number");
dojo.require("dijit.Dialog");
dojo.require("dijit.form.Button");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.form.Textarea");
dojo.require("dijit.form.ValidationTextBox");
dojo.require("dijit.layout.TabContainer");
dojo.require("dijit.Toolbar");
dojo.require("dijit.Tree");
dojo.require("widgets.ImageList");

//variable to hold selected item object
var selectedItem;
var templateId;
var tabIconDialog;

dojo.addOnLoad( function() {
	// Add symbol password buttons
	if(dojo.byId("tabIcon") != null) {
		dojo.connect(dojo.byId("tabIcon"), "onclick", "doTabIconChoose");
	}
});

function doAddPage()
{
	dijit.byId("itemTitle").setValue("");
	dijit.byId("addDialog").show();
}

function doAddPageCancel()
{
	dijit.byId("addDialog").hide();
}

function doAddPageConfirm()
{
	dojo.xhrGet({
		url:ajaxProcessDispatcher,
		content:{
			a: "page",
			name: dijit.byId("itemTitle").value,
			templateId: dojo.byId("id").value,
			operation: "insert"
		},
		timeout:1000,
		error: function(){
		},
		load: function(response){
			var id = response;

			templateDataStore.newItem({
				id: id,
				title: dijit.byId("itemTitle").value
			});
		}
	});
	dijit.byId("addDialog").hide();
}

//function to handle cancellation
function doCancel()
{
	location.href="?do=1";
}

function doCancelUpdatePage()
{
	dijit.byId("updateDialog").hide();
}

function doClickTemplateTree(item)
{
	selectedItem = item;
	console.info(selectedItem);
}

function doDeletePage()
{
	if(!selectedItem || !templateDataStore.isItem(selectedItem)) {
		// create the dialog
		var warningDialog = new dijit.Dialog({
			title: "Warning"
		});
		// set some content
		warningDialog.setContent("<p>You must select a page.</p>");
		// make a button
		var buttonNode = warningDialog.containerNode.appendChild(dojo.doc.createElement('div'));
		new dijit.form.Button({
			label: "OK",
			type: "button",
			onClick: function(){
				warningDialog.hide();
			}
		}, buttonNode);
		// show a dialog
		warningDialog.show();
	}
	else {
		dijit.byId("deleteDialog").show();
	}
}

function doDeletePageCancel()
{
	dijit.byId("deleteDialog").hide();
}

function doDeletePageConfirm()
{
	alert("You are about to delete '" + selectedItem.title + "'");

	dojo.xhrGet({
		url: ajaxProcessDispatcher,
		content:{
			a: "page",
			id: selectedItem.id,
			name: selectedItem.name,
			operation: "delete"
		},
		timeout: 1000,
		error: function(){
		},
		load: function(response){
			templateDataStore.deleteItem(selectedItem);
		}
	});

	dijit.byId("deleteDialog").hide();
}

function doEditPageOrTab()
{
	// Selected item is a page
	if(selectedItem && templateDataStore.isItem(selectedItem)) {
		dijit.byId("itemTitle2").setValue(selectedItem.title);
		dijit.byId("updateDialog").show();
	}
	// Page not selected
	else {
		// Set tab as selected if not already
		if(!selectedItem) {
			selectedItem = templateModel.root;
		}
		dijit.byId("itemTitle2").setValue(selectedItem.label.substring(5));
		dijit.byId("updateDialog").show();
	}
}

//function to handle reset
function doReset()
{
	document.getElementById("form").reset();
}

function doSubmit()
{
	var templateId = dojo.byId("id").value;

	dojo.xhrPost({
		url: ajaxProcessDispatcher,
		form: "form",
		timeout:1000,
		error: function() {
			},
		load: function(data) {
			newId = dojo.number.parse(data);
			
			if (isNaN(newId)) {
				showNotification("Error", "Template could not be saved");
			}
			else {
				if(templateId != "") {
					showNotification ("Success", "Template has been updated");
				}
				else {
					showNotification (
						"Success",
						"New template has been created",
						"?do=1&a=edit&id=" + newId
					);
				}
			}
		}
	});
}

function doUpdatePageOrTab()
{
	var theAction, pageId, templateid;

	if(templateDataStore.isItem(selectedItem)) {
		// Updating page name
		theAction = 'page';
		pageId = selectedItem.id
	}
	else {
		// Updating tab name
		templateId = dojo.byId("id").value;
		theAction = 'tab';
	}

	dojo.xhrGet({
		url:ajaxProcessDispatcher,
		content:{
			a: theAction,
			id: pageId,
			templateId: templateId,
			name: dijit.byId("itemTitle2").value,
			operation: 'update'
		},
		timeout:1000,
		error: function(){
		},
		load: function(response){
			if(templateDataStore.isItem(selectedItem)) {
				// Update page title
				templateDataStore.setValue(selectedItem, "title", dijit.byId("itemTitle2").value);
			}
			else {
				// Using redirect to force redraw (changing label didn't work)
				location.href = location.href;
			}
		}
	});
	dijit.byId("updateDialog").hide();
}

function doDelete(rowId)
{
	templateId = rowId;

	// Check the template hasn't had any content added to it
	dojo.xhrPost({
		url:ajaxProcessDispatcher,
		content:{
			a: "Template",
			id: templateId,
			operation: "check_delete"	//check if it's insertion or alteration
		},
		timeout:1000,
		error: function(er) {
			console.error(er);
		},
		load: function(data) {
			question = '<p>Are you sure you want to delete this template?</p>';
			if(data!='0') {
				question += "<p>" + data + "<strong> have content in this template.</strong></p>";
			}
			else {
				question += "<p>No users have content in this template yet.</p>";
			}
			showYesNoBox('Delete template', question, doDeleteConfirm);
		}
	});	
}

function doDeleteConfirm()
{
	dojo.xhrPost({
		url:ajaxProcessDispatcher,
		content:{
			a: "Template",
			id: templateId,
			operation: "delete"
		},
		timeout:1000,
		error: function() {
		},
		load: function(data) {
			if(data=='1') {
				var urlRedirection="?do=1";
				showNotification("Success", "Template has been deleted", urlRedirection, "auto");
			}
			else {
				showNotification("Error", "<p>Template was not deleted.</p><p>" + data + "</p>");
			}
		}
	});
}

/* -- Adding removing users and groups from template -- */

function doAddManyToTemplate()
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
	moveAjax(elementIds, "add_viewers");
}

function doRemoveManyFromTemplate()
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
	moveAjax(elementIds, "remove_viewers");
}



function doTabIconChoose()
{
	if(tabIconDialog == null) {
		//creating a notification dialog from dijit.Dialog
		tabIconDialog = new dijit.Dialog({
			id: 'dlgIcons',
			title: 'Login shape'
		});
		// set some content to it
		tabIconDialog.setContent("<p>Choose tab icon</p>");

		// Add image list
		var widgetNode = tabIconDialog.containerNode.appendChild(dojo.doc.createElement('div'));
		new widgets.ImageList({store:assetData,
								imagePath:"",
								showInfo: false,
								onSelect: onTabIconSelect}, widgetNode);
	}
	tabIconDialog.show();
}

function onTabIconSelect(icon)
{
	// Hide dialog
	tabIconDialog.hide();

	// Set shape
	dojo.xhrPost ({
		url: ajaxProcessDispatcher,
		content: {
			a: 'template',
			operation: 'seticon',
			icon_id: icon.id,
			id: dojo.byId("id").value
		},
		load: function (data) {
			if (data != '0') {
				showNotification("Error", "Could not set icon.<br />" + data, null);
			}
			else {
				showNotification("Success", "Icon has been set.");

				// Change shape on screen
				var image = dojo.byId('tabIcon');
				image.setAttribute('src', icon.imagePath);
			}
		},
		error: function (error) {
			console.error ('Error: ', error);
		}
	});
}













function moveUser(userElement, toContainerId, functionName)
{
	userElement.setAttribute("ondblclick", functionName);
	document.getElementById(toContainerId).appendChild(userElement);
}

function moveAjax(viewers, actionName)
{
	dojo.xhrPost({
		url:ajaxProcessDispatcher,
		content:{
			a: "template",
			id: dojo.byId("id").value,
			ids: viewers.join(','),
			operation: actionName	// add_viewers/remove_viewers
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