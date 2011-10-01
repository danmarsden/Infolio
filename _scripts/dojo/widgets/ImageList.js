/**
 * A Dojo Widget that shows a list of images from a data source
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id: ImageList.js 792 2009-09-01 19:49:26Z richard $
 * @link       NA
 * @since      NA
*/

dojo.provide("widgets.ImageList");

dojo.require("dojo.dnd.Source");
dojo.require("dojo.dnd.Moveable");
dojo.require("dojo.parser");
dojo.require("dijit._Widget");
dojo.require("dijit._Templated");


dojo.declare("widgets.ImageList", [dijit._Widget, dijit._Templated],
{
	// Holds the id attribute of the box on which to use YFT
	store: null,

	onSelect: null,

	showInfo: true,

	// Where the images are
	imagePath: '/_images/',

	// Template
	templateString: '<div class="ImageList">' +
	'<ul dojoAttachPoint="containerNode" id="imageThumbs"></ul></div>',

	// Holds subscription handle
	changeboxSub: null,

	_fetchParams: {
		onError: function(errData, request) {
			console.debug("Error: " + errData);
		},
		onItem: function(item) {
			item.imagePath = this.imagePath + item.href;
			insertNode.insertNodes(false, [item]);
		},
		sort: [{attribute: "name", descending: false}]
	},

	onImgClick: function(e) {
		if(onSelect != null) {
			var clickItemId = (dojo.attr(e.currentTarget, 'id')).replace('a', '');
			var clickItemSrc = (dojo.attr(e.currentTarget, 'src'));

			// Find data item
			dojo.forEach(store._arrayOfAllItems, function(item){
				// Check id, or href if there is no id
				if((item.id == clickItemId && item.id != 0) || item.href == clickItemSrc) {
					onSelect(item);
				}
			});
		}
	},

	// postCreate is called after the widget has been constructed.
	postCreate: function() {
		this._createList();
	},

	postMixInProperties: function() {
		onImgClick = this.onImgClick;
		onSelect = this.onSelect;
		store = this.store;
	},

	destroy: function() {
		console.info('Destroyed');
	},

	reSort: function(sortMethod) {
		switch(sortMethod) {
			case "date-asc":
				this._fetchParams.sort = [{attribute: "date", descending: false}];
				break;
			case "date-desc":
				this._fetchParams.sort = [{attribute: "date", descending: true}];
				break;
			case "name-asc":
				this._fetchParams.sort = [{attribute: "name", descending: false}];
				break;
			case "name-desc":
			default:
				this._fetchParams.sort = [{attribute: "name", descending: true}];
		}

		insertNode.selectAll();
		insertNode.deleteSelectedNodes();
		this.store.fetch(this._fetchParams);
	},

	_createList: function() {
		imagePath = this.imagePath;
		showInfo = this.showInfo;
		insertNode = new dojo.dnd.Source(this.containerNode, {creator: this.createImageNode, copyOnly: true});

		if(this.store != null) {
			this.store.fetch(this._fetchParams);
		}
		else {
			alert('Must specify store.');
		}
	},

	// Creates a draggable node to put in the image list
	createImageNode: function(item) {
		var img = document.createElement('img');
		img.setAttribute("src", item.imagePath);
		img.setAttribute('id', 'a' + item.id);
		var li = document.createElement('li');
		li.appendChild(img);
		dojo.connect(img, 'onclick', onImgClick);

		if(showInfo) {
			var status = document.createElement('div');
			dojo.addClass(status, 'status');
			var status = document.createElement('div');
			if(item.view_public == 'true') {
				status.innerHTML = 'Public';
			}
			else {
				status.innerHTML = 'Private';
			}
			if(item.use_count==0) {
				status.innerHTML += " | Unassigned";
			}
			li.appendChild(status);
		}

		return {node: li, data: item};
	}

});