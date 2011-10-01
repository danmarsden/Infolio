/**
 * @author Richard garside
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$(document).ready(function()
{
	setupScrollingTabs();
	setupScrollingCollection();

	// Smaller button click
	$('a.bSmall').click(function() {
		$('body').addClass('s-small');
		$('body').removeClass('s-big');
		$.post('/system/ajax/size.php', {'s':'s-small'});
		return false;
	});

	// Medium button click
	$('a.bMedium').click(function() {
		$('body').removeClass('s-small');
		$('body').removeClass('s-big');
		$.post('/system/ajax/size.php', {'s':''});
		return false;
	});

	// Bigger button click
	$('a.bBig').click(function() {
		$('body').removeClass('s-small');
		$('body').addClass('s-big');
		$.post('/system/ajax/size.php', {'s':'s-big'});
		return false;
	});

	// Styling
	initStyledFileUploads();
});

function setupScrollingCollection()
{
	var scrollBox = $("#collection-scroller");
	if (scrollBox.length > 0) {
		var picW = $("#collection-scroller li").width();
		var numPics = Math.floor((scrollBox.width() - 100) / picW);
		scrollBox.scrollable({items:'.items',horizontal:true,size:numPics,prev:'p#scroll-left a',next:'p#scroll-right a'});
	}
}

function setupScrollingTabs()
{
	var scrollTabs = $("#nav-tabs");
	if (scrollTabs.length > 0) {
		var tabW = $("#nav-tabs ul.items li").width() + 60;
		var numTabs = Math.floor((scrollTabs.width() - 130) / tabW);
		scrollTabs.scrollable({items:'.items',horizontal:true,size:numTabs});
		scrollTabs.scrollable('seekTo', phpTabPlace);
	}

	$("#nav-tabs li").css("visibility", "visible");
}



// From: www.quirksmode.org/dom/inputfile.html
function initStyledFileUploads()
{
	// Breaks in FF2 so test for this
	if (/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
		var ffversion = new Number(RegExp.$1) // capture x.x portion and store as a number
		if (ffversion < 3) {
			return;
		}
	}

	var W3CDOM = (document.createElement && document.getElementsByTagName);
	if (!W3CDOM) return;
	var fakeFileUpload = document.createElement('div');
	fakeFileUpload.className = 'fakefile';
	fakeFileUpload.appendChild(document.createElement('input'));
	var image = document.createElement('img');
	image.src='/_images/si/browse.gif';
	fakeFileUpload.appendChild(image);
	var x = document.getElementsByTagName('input');
	for (var i=0;i<x.length;i++) {
		if (x[i].type != 'file') continue;
		if (x[i].parentNode.className != 'fileinputs') continue;
		x[i].className = 'file hidden';
		var clone = fakeFileUpload.cloneNode(true);
		x[i].parentNode.appendChild(clone);
		x[i].relatedElement = clone.getElementsByTagName('input')[0];
		x[i].onchange = x[i].onmouseout = function () {
			this.relatedElement.value = this.value;
		}
	}
}
