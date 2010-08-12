/* $Id: general.js 848 2010-01-07 09:14:09Z richard $ */
var ajaxProcessDispatcher=path.AJAX_DISPATCHER;

dojo.require("dojo.parser");
dojo.require("dijit.Dialog");
dojo.require("dijit.form.Button");
	
//function to show a dialog box
var showNotification=function(msgTitle, msg2, url){
	//creating a notification dialog from dijit.Dialog
	var notificationDialog = new dijit.Dialog({
		title: msgTitle
	});
	// set some content to it
	notificationDialog.setContent("<br /><br />" + msg2 + "<br /><br /><br />");

	// make a close button
	var buttonNode = notificationDialog.containerNode.appendChild(dojo.doc.createElement('div'));
	new dijit.form.Button({
		label:"OK",
		type:"button",
		onClick: function(){
			notificationDialog.hide();
			if(url!=null) location.href=url; 
		}
	},buttonNode);
	// show a dialog
	notificationDialog.show();
}

function showHelpWindow(url)
{
	var helpDialog = new dijit.Dialog({
		title: "Help"
	});
	// set some content to it
	console.info("Showing help page " + url)
	helpDialog.setHref("/admin/help.php#" + url);
	helpDialog.resize({width:"500px; height: 80%;"});
	// make a close button
	var buttonNode = helpDialog.containerNode.appendChild(dojo.doc.createElement('div'));
	new dijit.form.Button({
		label:"OK",
		type:"button",
		onClick: function(){
			helpDialog.hide();
			if(url!="") location.href=url;
		}
	},buttonNode);
	// show a dialog
	helpDialog.show();		
}

/* Utility function to raise a dialog asking the user a yes/no question. */
function showYesNoBox(title, question, yesCallbackFn)
{
	var yesNoDialog = new dijit.Dialog({ id: 'queryDialog', title: title });
	// When either button is pressed, kill the dialog and call the callbackFn.
	var commonCallback = function(mouseEvent) {
		yesNoDialog.hide();
		yesNoDialog.destroyRecursive();
		if (mouseEvent.explicitOriginalTarget.id == 'yesButton') {
			yesCallbackFn();
		}
	};
	var questionDiv = dojo.create('p', { innerHTML: question });
	var yesButton = new dijit.form.Button(
		{ label: 'Yes', id: 'yesButton', onClick: commonCallback });
	var noButton = new dijit.form.Button(
		{ label: 'No', id: 'noButton', onClick: commonCallback });

	yesNoDialog.containerNode.appendChild(questionDiv);
	yesNoDialog.containerNode.appendChild(yesButton.domNode);
	yesNoDialog.containerNode.appendChild(noButton.domNode);

	yesNoDialog.show();
}

/*
 * Extension method on document to access GET vars
 * Src: http://failchad.blogspot.com/2009/01/accessing-get-variables-in-javascript.html
 */
(function(){
   document.$_GET = [];
   var urlHalves = String(document.location).split('?');
   if(urlHalves[1]){
      var urlVars = urlHalves[1].split('&');
      for(var i=0; i<=(urlVars.length); i++){
         if(urlVars[i]){
            var urlVarPair = urlVars[i].split('=');
            document.$_GET[urlVarPair[0]] = urlVarPair[1];
         }
      }
   }
})();