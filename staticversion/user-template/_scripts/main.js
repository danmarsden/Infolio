/**
 * @author Richard garside
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 */
$(document).ready(function()
{
	setupScrollingTabs();
	setupScrollingCollection();
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