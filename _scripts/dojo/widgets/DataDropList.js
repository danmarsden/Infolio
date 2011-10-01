/**
 * A Dojo Widget that shows a list of data from a data source
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id: DataList.js 366 2009-02-01 22:12:37Z richard $
*/

dojo.provide("widgets.DataDropList");

dojo.require("dojo.parser");
dojo.require("dijit._Widget");
dojo.require("dijit._Templated");


dojo.declare("widgets.DataDropList", [dijit._Widget, dijit._Templated],
{
	// Holds the id attribute of the box on which to use YFT
	store: null,

	// Function called when a items are dropped on this list
	// params dropTraget, itemList
	onItemsDropped: null,

	onItemClick: null,

	// The type of things in this list
	type: null,

	// Template
	templateString: '<table class="droplist" width="100%">' +
	"<tbody dojoAttachPoint='containerNode'></tbody></table>",

	// Holds subscription handle
	changeboxSub: null,

	deselectAll: function() {
		dojo.forEach(this.containerNode.childNodes, function(tr) {
			dojo.removeClass(tr, 'selected');
		});
	},

	// postCreate is called after the widget has been constructed.
	postCreate: function() {
		var dropFunction = this.onItemsDropped;
		var itemClickFunction = this.onItemClick;
		var insertNode = this.containerNode;
		var listType = this.type;
		if(this.store != null) {
			var store = this.store;
			store.fetch({
				onError: function(errData, request) {
					console.debug("Error: " + errData);
				},
				onItem: function(item) {
					//this.domNode.appendChild(document.createTextNode(item.name));
					trItem = document.createElement('tr');
					trItem.setAttribute('id', 'i' + item.id)
					tdItem = document.createElement('td');
					tdItem.innerHTML = item.name;
					trItem.appendChild(tdItem);

					// Mouse over
					dojo.connect(trItem, 'mouseover', function(e) {
						dojo.addClass(e.currentTarget, 'hover');
					});
					// Mouse out
					dojo.connect(trItem, 'mouseout', function(e) {
						dojo.removeClass(e.currentTarget, 'hover');
					});
					// Mouse click
					dojo.connect(trItem, 'onclick', function(e) {
						dojo.forEach(insertNode.childNodes, function(tag){
							dojo.removeClass(tag, 'selected');
						});
						dojo.addClass(e.currentTarget, 'selected');

						if(itemClickFunction != null) {
							var clickItemId = (dojo.attr(e.currentTarget, 'id')).replace('i', '');
							// Find data item
							var returnItem;
							dojo.forEach(store._arrayOfAllItems, function(item){
								if(item.id == clickItemId) {
									returnItem = item;
								}
							});


							// Send data to click event function
							itemClickFunction(returnItem);
						}
					});
					// tr is drop target
					var dropTarget = new dojo.dnd.Target(trItem);
					dojo.connect(dropTarget, 'onDndDrop', function(sourceContainer, items, a1, targetContainer) {
							// Check this message is for me
							if(dojo.dnd.manager().target !== this){
								return;
							}

							
							// Loop through all the new items and get their ids
							var itemIds = [];
							for(i=0; i < items.length; i++) {
								itemIds[i] = (dojo.attr(items[i].childNodes[0], 'id')).replace('a', '');
								targetContainer.deleteSelectedNodes();
							}

							// Only works stuff out if there is an event function
							if(dropFunction != null) {
								targetId = targetContainer.node.id.replace('i', '');
								dropFunction(targetId, itemIds, listType);
							}
							
						}
					);
					

					insertNode.appendChild(trItem);
				}
			});
		}
		else {
			console.debug('DataDropList needs store param');
		}
	},


	destroy: function() {

	}
});

