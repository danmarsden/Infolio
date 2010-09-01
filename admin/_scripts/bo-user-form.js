/* $Id: bo-user-form.js 848 2010-01-07 09:14:09Z richard $ */

dojo.require("dojo.data.ItemFileReadStore");
dojo.require("dojo.number");
dojo.require("dijit.form.Button");
dojo.require("dijit.form.Form");
dojo.require("dijit.form.TextBox");
dojo.require("dijit.form.CheckBox");
dojo.require("dijit.form.ValidationTextBox");
dojo.require("dijit.form.Textarea");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.Toolbar");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.layout.TabContainer");
dojo.require("dijit.Tooltip");
dojo.require("widgets.ImageList");

var act_idRestore = 0;
var photoDialog;
var shapeDialog;

dojo.addOnLoad( function() {
	// Add delete recovery buttons
	var restoreBtn = dojo.create('td', { innerHTML: '(restore)' });
	var tr = dojo.query("tr.delete");
	tr.addContent(restoreBtn);
	tr.connect('onclick', 'doRestore');

	// Add symbol password buttons
	if(dojo.byId("passShape") != null) {
		dojo.connect(dojo.byId("passShape"), "onclick", "doSelectPassShape");
		dojo.connect(dojo.byId("passPhoto"), "onclick", "doSelectPassPhoto");
	}

	// After export go to correct tab
	if(document.$_GET['tab'] == "export") {
		dijit.byId("mainTabContainer").selectChild(dijit.byId("exportTab"));
	}

    // After enable/disable tabs go to correct tab
	if(document.$_GET['tab'] == "tabs") {
		dijit.byId("mainTabContainer").selectChild(dijit.byId("manageTabs"));
	}
});

// Deletes the user
function doDelete()
{
	if (confirm("Are you sure you want to delete this user?")) {
		userId = dojo.number.parse( dojo.byId('id').value );
		dojo.xhrGet ({
			url: path.AJAX_DISPATCHER + "?a=User&operation=delete&id=" + userId,
			load: function (data) {
				deletedId = dojo.number.parse(data);
				if (isNaN(deletedId)) {
					showNotification("Error", "Could not delete user.", null);
				}
				else {
					dojo._destroyElement('u' + deletedId);
					showNotification("Success", "User has been deleted.", path.BACKOFFICE_INDEX_FILENAME + "?do=6");
				}
			},
			error: function (error) {
				console.error ('Error: ', error);
			}
		});
	}
}

function doRestore(ev)
{
	// Get id from tr inner HTML
	trBits = /\(id:(\d+)\)/.exec(ev.currentTarget.innerHTML);
	act_idRestore = trBits[1];

	// Tab or asset?
	if(/<td>Deleted asset/.test(ev.currentTarget.innerHTML)) {
		// Show are you sure message for asset
		showYesNoBox('Restore asset ' + act_idRestore,
			'Are you sure you want to restore this asset?',
			restoreAsset);
	}
	else if(/<td>Deleted tab/.test(ev.currentTarget.innerHTML)) {
		// Show are you sure message for tab
		showYesNoBox('Restore tab ' + act_idRestore,
			'Are you sure you want to restore this tab?',
			restoreTab);
	}
}

function doSelectPassPhoto()
{
	if(photoDialog == null) {
		//creating a notification dialog from dijit.Dialog
		photoDialog = new dijit.Dialog({
			id: 'dlgPhotos',
			title: 'Login photo'
		});
		// set some content to it
		photoDialog.setContent("<p>Choose your login photo</p>");

		// Add image list
		var widgetNode = photoDialog.containerNode.appendChild(dojo.doc.createElement('div'));
		new widgets.ImageList({store:loginPhotoData,
								imagePath:"",
								showInfo: false,
								onSelect: onPhotoSelect}, widgetNode);
	}
	photoDialog.show();
}

function onPhotoSelect(photo)
{
	// Hide dialog
	photoDialog.hide();

	// Set shape
	dojo.xhrPost ({
		url: ajaxProcessDispatcher,
		content: {
			a: 'switchpassword',
			operation: 'setphoto',
			id: photo.fid,
			user_id: dojo.byId("userid").value
		},
		load: function (data) {
			deletedId = dojo.number.parse(data);
			if (isNaN(deletedId)) {
				showNotification("Error", "Could not set photo.<br />" + data, null);
			}
			else {
				showNotification("Success", "Photo has been set.");

				// Change photo on screen
				var image = dojo.byId('passPhoto');
					image.setAttribute('src', photo.imagePath);
			}
		},
		error: function (error) {
			console.error ('Error: ', error);
		}
	});
}

function doSelectPassShape()
{
	if(shapeDialog == null) {
		//creating a notification dialog from dijit.Dialog
		shapeDialog = new dijit.Dialog({
			id: 'dlgShapes',
			title: 'Login shape'
		});
		// set some content to it
		shapeDialog.setContent("<p>Choose your login shape</p>");

		// Add image list
		var widgetNode = shapeDialog.containerNode.appendChild(dojo.doc.createElement('div'));
		new widgets.ImageList({store:loginShapeData,
								imagePath:"",
								showInfo: false,
								onSelect: onShapeSelect}, widgetNode);
	}
	shapeDialog.show();
}

function onShapeSelect(shape)
{
	// Hide dialog
	shapeDialog.hide();

	// Set shape
	dojo.xhrPost ({
		url: ajaxProcessDispatcher,
		content: {
			a: 'switchpassword',
			operation: 'setshape',
			id: shape.fid,
			user_id: dojo.byId("userid").value
		},
		load: function (data) {
			deletedId = dojo.number.parse(data);
			if (isNaN(deletedId)) {
				showNotification("Error", "Could not set shape.<br />" + data, null);
			}
			else {
				showNotification("Success", "Shape has been set.");

				// Change shape on screen
				var image = dojo.byId('passShape');
				image.setAttribute('src', shape.imagePath);
			}
		},
		error: function (error) {
			console.error ('Error: ', error);
		}
	});
}

/**
 * Restores an asset to a user
 */
function restoreAsset()
{
	dojo.xhrGet ({
			url: ajaxProcessDispatcher,
			content: {
				a: 'asset',
				operation: 'restore',
				id: act_idRestore,
				user_id: dojo.byId("userid").value
			},
			load: function (data) {
				newId = dojo.number.parse(data);
				if(isNaN(newId)) {
					showNotification(
						"Error",
						"Unable to restore asset<br />" + data,
						null);
				}
				else {
					/*dojo.byId('operation').value = 'Update';
					dojo.byId('userid').value = newId;
					dijit.byId('username').attr('disabled', 'disabled');
					dijit.byId('btnDelete').domNode.style.display = "inline";*/
					showNotification(
						"Success",
						"Asset has been restored");
				}

			},
			error: function (error) {
				console.error ('Error: ', error);
			}
		});
}

function restoreTab()
{
	dojo.xhrGet ({
			url: ajaxProcessDispatcher,
			content: {
				a: 'tab',
				operation: 'restore',
				id: act_idRestore
			},
			load: function (data) {
				newId = dojo.number.parse(data);
				if(isNaN(newId)) {
					showNotification(
						"Error",
						"Unable to restore tab<br />" + data,
						null);
				}
				else {
					/*dojo.byId('operation').value = 'Update';
					dojo.byId('userid').value = newId;
					dijit.byId('username').attr('disabled', 'disabled');
					dijit.byId('btnDelete').domNode.style.display = "inline";*/
					showNotification(
						"Success",
						"Tab has been restored");
				}

			},
			error: function (error) {
				console.error ('Error: ', error);
			}
		});
}

// Saves the user
function doSave()
{
	if(dijit.byId('userform').validate()){
		dojo.xhrGet ({
			url: ajaxProcessDispatcher,
			form: 'userform',
			load: function (data) {
				newId = dojo.number.parse(data);
				if(isNaN(newId)) {
					showNotification(
						"Error",
						"Unable to save user<br />" + data, 
						null);
				}
				else {
					dojo.byId('operation').value = 'Update';
					dojo.byId('userid').value = newId;
					dijit.byId('username').attr('disabled', 'disabled');
					dijit.byId('btnDelete').domNode.style.display = "inline";
					showNotification(
						"Success", 
						"User has been saved", 
						null);
				}
				
			},
			error: function (error) {
				console.error ('Error: ', error);
			}
		});				
	}
	else {
		showNotification("Error", "Please enter all required fields");
	}
}

// Cancels and takes user to user view page
function doCancel() {
	location.href = ".?do=6";
}

function theSame(dojoTxt1, dojoTxt2) {
	return dojoTxt1.getValue() == dojoTxt2.getValue();
}
