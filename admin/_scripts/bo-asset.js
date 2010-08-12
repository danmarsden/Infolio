/* $Id: bo-asset.js 792 2009-09-01 19:49:26Z richard $ */

//dojo.require("dijit.Dialog");
dojo.require("dijit.form.Button");
dojo.require("dijit.form.ComboBox");
dojo.require("dijit.form.TextBox");
dojo.require("dijit.layout.TabContainer");
dojo.require("dijit.Toolbar");
dojo.require("widgets.ImageList");
dojo.require("widgets.DataDropList");
dojo.require("dojo.data.ItemFileReadStore");


// The direction of sorting
var nameSortDir = 'asc';
var dateSortDir = 'asc';

var assetUrl = "/admin/ajax/assets.list.php"
var lastQueriedAssetUrl;


var userOrGroup = null;
var userOrGroupId = 0;


// On load function
dojo.addOnLoad( function() {
	dojo.style(bRemove.domNode, 'visibility', 'hidden');
	dojo.style(bWhoCan.domNode, 'visibility', 'hidden');
	dojo.style(paneTagEdit.domNode, 'visibility', 'hidden');
	dojo.style(dojo.byId("cbPublic"), 'visibility', 'hidden');

	dojo.connect(dojo.byId("lnkAddTags"), "click", onShowAddTags);
	dojo.connect(dojo.byId("cbPublic"), "click", onClickCheckboxPublic);
});

function deselectAll() {
	groupList.deselectAll();
	userList.deselectAll();
	dojo.style(bRemove.domNode, 'visibility', 'hidden');
	// Blank global vars
	userOrGroup = null;
	userOrGroupId = 0;
}

function getInstituteFilter()
{
	var instSelect = dojo.byId('iInst');
	if(instSelect != null) {
		return "&inst=" + instSelect.value;
	}
	else {
		return '';
	}
}

function getSelectedAssetId()
{
	return dojo.byId('SelectedImage').getAttribute('class').replace('a', '');
}

function onAddTags() {
	var assetId = dojo.byId('SelectedImage').getAttribute('class').replace('a', '');
	dojo.xhrPost ({
		url: "/admin/ajax/asset_tag.action.php",
		content: {'aId': assetId, 'tags': iNewTags.value},
		load: function (data) {
			dojo.byId('tags').innerHTML = tagLinks(data);
			iNewTags.reset();
			// Redirect required because data store seems to cache old version
			var urlRedirection = "?do=8";
			showNotification('Success', 'Added tags', urlRedirection);
		},
		error: function (error) {
			console.error ('Error: ', error);
		}
	});
}

/**
 * User has selected one or more assets
 */
function onAssetSelect(item) {
	var assetDate = new Date(item.date*1000);

	var image = dojo.byId('SelectedImage');
	image.setAttribute('src', item.imagePath);
	image.setAttribute('class', 'a' + item.id);
	dojo.byId('title').innerHTML = item.name;
	dojo.byId('description').innerHTML = item.description;
	dojo.byId('date').innerHTML = assetDate.toDateString();
	dojo.byId('owner').innerHTML = item.owner;

	// Sort out tag HTML
	var tagsValue = '';
	if(item.tags != null) {
		tagsValue = tagLinks(item.tags[0]);
	}
	dojo.byId('tags').innerHTML = tagsValue;
	dojo.query('.tagLink').connect("click", onClickTags);

	dojo.style(bWhoCan.domNode, 'visibility', 'visible');
	dojo.style(paneTagEdit.domNode, 'visibility', 'hidden');
	
	// Public/private checkbox
	var cbPublic = dojo.byId("cbPublic");
	cbPublic.checked = (item.view_public == "true");
	dojo.style(cbPublic, 'visibility', 'visible');
}

function onClickCheckboxPublic(ev)
{
	itemIsPublic = ev.target.checked;
	publicOrPrivate = (ev.target.checked) ? "public" : "private";
	dojo.xhrPost ({
		url: "/admin/ajax/assets.action.php",
		content: {'view_public': itemIsPublic, 'id': getSelectedAssetId()},
		load: function (data) {
			if(data=='1') {
				aUrl = (lastQueriedAssetUrl != null) ? lastQueriedAssetUrl :assetUrl + "?r=g" + getInstituteFilter();
				newAssetStore(aUrl);
				// Redirect required because data store seems to cache old version
				var urlRedirection = "?do=8";
				showNotification('Success', 'Item is now ' + publicOrPrivate, urlRedirection);
			}
			else {
				showNotification('Error', data);
			}
		},
		error: function (error) {
			showNotification('Error', error);
		}
	});
}

/**
 * The user has clicked a tag, show assets with that tag
 */
function onClickTags(ev)
{
	var tagName = ev.target.innerHTML;
	newAssetStore( assetUrl + "?tag=" + tagName);
	deselectAll();
	
	var tagSelect = dojo.byId('iFilterTags')
	var numTags = tagSelect.length;
	for(var i=1; i<numTags; i++){
		if (tagSelect[i].value == tagName) {
			dojo.byId('iFilterTags').selectedIndex = i;
			break;
		}
	}
	
}

/**
 * The user has dragged and dropped some assets on a user/group.
 * Send AJAX request to commit them to DB
 */
function onDrop(newOwner, items, type){
	dojo.xhrPost ({
		url: "/admin/ajax/assets.action.php",
		content: {'newOwner': newOwner, 'items': items.join(), 'type': type},
		load: function (data) {
			if(data=='1') {
				showNotification('Success', 'You have assigned ' + items.length + ' items');
			}
			else {
				showNotification('Error', data);
			}
		},
		error: function (error) {
			showNotification('Error', error);
		}
	});
}

function onFindWhoCanSee()
{
	dojo.xhrGet ({
		url: "/admin/ajax/asset-who-can-see.php",
		content: {'id': getSelectedAssetId()},
		load: function (data) {
			showNotification('Users and groups that can see this', data);
		},
		error: function (error) {
			console.error ('Error: ', error);
		}
	});
}

 function onFilterAll()
{
	newAssetStore( assetUrl + "?r=g" + getInstituteFilter());
	deselectAll();
	dojo.byId('iFilterTags').selectedIndex = 0;
}

function onFilterMine() {
	newAssetStore( assetUrl + "?filter=mine" + getInstituteFilter() );
	deselectAll();
	dojo.byId('iFilterTags').selectedIndex = 0;
}

function onFilterRecent() {
	newAssetStore( assetUrl + "?filter=recent" + getInstituteFilter() );
	deselectAll();
	dojo.byId('iFilterTags').selectedIndex = 0;
}

function onFilterTags() {
	var tagFilter = dojo.byId('iFilterTags').value;
	if(tagFilter.length > 0)tagFilter = "&tag=" + tagFilter;
	
	newAssetStore( assetUrl + "?r=g" + getInstituteFilter() + tagFilter);
	deselectAll();
}

function onFilterUnassigned() {
	newAssetStore( assetUrl + "?filter=unassigned" + getInstituteFilter() );
	deselectAll();
	dojo.byId('iFilterTags').selectedIndex = 0;
}

function onGroupClick(item) {
	newAssetStore( assetUrl + "?group_id=" + item.id );
	userList.deselectAll();
	dojo.byId('iFilterTags').selectedIndex = 0;

	// Set up remove button
	dojo.style(bRemove.domNode, 'visibility', 'visible');
	dojo.byId('userOrGroupName').innerHTML = item.name;

	// Store group details
	userOrGroup = 'group';
	userOrGroupId = item.id;
}

/**
 * Removes an asset from a user or group
 */
function onRemove() {
	// Get image id
	var imageId = dojo.byId('SelectedImage').getAttribute('class').replace('a', '');;

	dojo.xhrPost ({
		url: "/admin/ajax/assets.action.php",
		content: {'owner': userOrGroupId, 'removeAsset': imageId, 'type': userOrGroup},
		load: function (data) {
			showNotification('Remove asset', data);
			newAssetStore(assetThumbnails.store._jsonFileUrl);
		},
		error: function (error) {
			console.error ('Error: ', error);
		}
	});
}

function onSortDate() {
	dateSortDir = switchSortDir(dateSortDir);
	assetThumbnails.reSort('date-' + dateSortDir);
}

function onSortName() {
	nameSortDir = switchSortDir(nameSortDir);
	assetThumbnails.reSort('name-' + nameSortDir);
}

function onShowAddTags() {
	dojo.style(paneTagEdit.domNode, 'visibility', 'visible');
	iNewTags.focus();
}

function onUserClick(item) {
	newAssetStore( assetUrl + "?user_id=" + item.id );
	groupList.deselectAll();
	dojo.byId('iFilterTags').selectedIndex = 0;

	// Set up remove button
	dojo.style(bRemove.domNode, 'visibility', 'visible');
	dojo.byId('userOrGroupName').innerHTML = item.name;

	// Store user details
	userOrGroup = 'user';
	userOrGroupId = item.id;
}

// Refreshes the assets display from a new json source
function newAssetStore(theUrl) {
	lastQueriedAssetUrl = theUrl;
	var assetData = new dojo.data.ItemFileReadStore({url: theUrl });
	// assetThumbnails.newStore(assetData);

	/*assetThumbnails.selectAll();
	assetThumbnails.deleteSelectedNodes();
*/

	assetThumbnails.store = assetData;
	assetThumbnails.reSort('name-asc');
}

// Switches between sorting descending or ascending direction
function switchSortDir(dir) {
	return (dir=='asc') ? 'desc' : 'asc';
}

function tagLinks(tagCommaList)
{
	var tags = tagCommaList.split(",");
	for (var i in tags)
	{
		// TODO: Sort out correct link
		tags[i] = '<a id="tag_' + tags[i] + '" class="tagLink" href="#tag">' + tags[i] + '</a>';
	}
	return tags.join(", ");
}