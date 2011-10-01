/**
 * A Dojo Widget that shows a list of data from a data source
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id: DataList.js 366 2009-02-01 22:12:37Z richard $
 * @link       NA
 * @since      NA
*/

dojo.provide("widgets.DataList");

dojo.require("dojo.parser");
dojo.require("dijit._Widget");
dojo.require("dijit._Templated");


dojo.declare("widgets.DataList", [dijit._Widget, dijit._Templated],
{
	// Holds the id attribute of the box on which to use YFT
	store: null,

	// Template
	templateString: "<div>DataList"+
	"<ul dojoAttachPoint='containerNode'></ul></div>",

	// Holds subscription handle
	changeboxSub: null,

	// postCreate is called after the widget has been constructed.
	postCreate: function() {
		console.debug(this);
		var insertNode = this.containerNode;
		if(this.store != null) {
			this.store.fetch({
				onError: function(errData, request) {
					console.debug("Error: " + errData);
				},
				onItem: function(item) {
					//this.domNode.appendChild(document.createTextNode(item.name));
					liItem = document.createElement('li');
					liItem.innerHTML = item.name;
					insertNode.appendChild(liItem)
				}
			});
		}
		else {
			alert('Really big help!!');
		}
	},

	destroy: function() {

	}
});