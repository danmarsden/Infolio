/**
 * $Id: login.js 639 2009-06-23 08:55:47Z richard $
 */

function closePopupLoginBox(){
	if(confirm("Are you sure you dont want to login to administration area?")){
		document.getElementById("popupLoginBox").style.display="none";
		document.getElementById("popupContainer").style.display="none";	
		location.href=path.DOMAIN_NAME;
	}
}

function loginCancelButton(){
	location.href=".";
}

var doLogin=function(){
	var xmlHttp;
	try{ xmlHttp=new XMLHttpRequest(); }catch (e){ try{ xmlHttp=new ActiveXObject("Msxml2.XMLHTTP"); }catch (e){ try{xmlHttp=new ActiveXObject("Microsoft.XMLHTTP"); }catch (e){ alert("Your browser does not support AJAX!"); return false; } } }

	xmlHttp.onreadystatechange=function(){
		if(xmlHttp.readyState==4){
			switch(xmlHttp.responseText) {
				case '0':
					document.getElementById("errorNotificationContainer").innerHTML="<p>Wrong username or password</p>";
					break;
				case '1':
					location.href = '../';
					break;
				case '2':
					document.getElementById("errorNotificationContainer").innerHTML="<p>You're not allowed to administer this site.</p>";
					break;
				default:
					document.getElementById("errorNotificationContainer").innerHTML="<p>Something went wrong</p>";
					break;
			}
		}
	}

	var username=document.getElementById("usernameInput").value;
	var password=document.getElementById("passwordInput").value;
	var institution=document.getElementById("institution").value;

	//var url=path.AJAX_DISPATCHER + "?a=Login&operation=login&referrer=" + referrer + "&username=" + username + "&password=" + password;
	var url = path.AJAX_DISPATCHER + "?a=Login&operation=login&username=" + username + "&password=" + password + "&institution=" + institution;
	//alert ('Url:' + url);

	xmlHttp.open("GET", url, true);
	xmlHttp.send(null);		
}

function doNotify(msgH1, msgH2, url, mode){
	if(mode=="show"){
		dijit.byId('actionNotification').show();
		document.getElementById("notificationH1").innerHTML=msgH1;
		document.getElementById("notificationH2").innerHTML=msgH2;
		if(url=="")	document.getElementById("redirectionContainer").style.display="none";
	}else if(mode=="hide"){
		dijit.byId('actionNotification').hide();
		if(url!="") location.href=url;
	}else if(mode=="auto"){
		doNotify(msgH1, msgH2, url, "show")
		dijit.byId('actionNotification').show();
		setTimeout(
		   "doNotify('"+msgH1+"', '"+msgH2+"', '"+url+"', 'hide')",
		   redirectionInMS
		);			
	}
}