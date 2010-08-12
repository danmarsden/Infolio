/* $Id$ */

dojo.require("dojo.data.ItemFileReadStore");
dojo.require("dojo.number");
dojo.require("widgets.ImageList");

var changeDialog;

function onClickChangePic()
{
	//creating a notification dialog from dijit.Dialog
	changeDialog = new dijit.Dialog({
		title: 'Change picture'
	});
	// set some content to it
	changeDialog.setContent("<p>Choose a picture from your own collection</p>");

	// Add image list
	var widgetNode = changeDialog.containerNode.appendChild(dojo.doc.createElement('div'));
	new widgets.ImageList({store:assetData,
							imagePath:"",
							showInfo: false,
							 onSelect: onAssetSelect}, widgetNode);
	changeDialog.show();
}

function onAssetSelect(item)
{
	changeDialog.hide();
	dojo.xhrPost({
		url:ajaxProcessDispatcher,
		content:{
			a: "institution",
			id: dojo.byId("institution_id").value,
			asset_id: item.id,
			operation: "setpic"
		},
		timeout:1000,
		error: function(er){
			console.error(er);
		},
		load: function(data){
			code = dojo.number.parse(data);
			if (isNaN(code)) {
				showNotification("Error", "Could not change picture.", null);
			}
			else {
				location.href = location.href;
			}
		}
	});

}