/**
 * @author Richard garside
 * @copyright 2008 Rix Centre
 * @license http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version $Id$
 */
$(document).ready(function()
{
	$('.del_attach').bind('click', onDeleteAttachment);
	$('.btnDelete').bind('click', onDeleteBlock);
	$('.btnDown').bind('click', onBlockDown);
	$('.btnUp').bind('click', onBlockUp);
	$('#bDeletePage').bind('click', onDeletePage);
	$('a.btnIEdit').bind('click', onEditImage);
	$('#upload-btn').bind('click', onUpload);
});

function onBlockDown(ev)
{
	blockId = getBlockIdFromClass(this.className);

	ajaxMoveBlock(blockId, 'down');

	// Move block
	blockToMove = $('#b'+blockId);
	blockToSwapWith = blockToMove.next();
	blockToMove.fadeOut("normal", function(){
		blockToMove.insertAfter(blockToSwapWith);
		blockToMove.fadeIn("normal");

		// Change buttons
		showHideUpDownButtons(blockToMove, blockToSwapWith);
	});

	return false;
}

function onBlockUp(ev)
{
	blockId = getBlockIdFromClass(this.className);

	ajaxMoveBlock(blockId, 'up');

	// Move block
	blockToMove = $('#b'+blockId);
	blockToSwapWith = blockToMove.prev();
	blockToMove.fadeOut("normal", function(){
		blockToMove.insertBefore(blockToSwapWith);
		blockToMove.fadeIn("normal");

		// Change buttons
		showHideUpDownButtons(blockToSwapWith, blockToMove);
	});
	
	return false;
}

function onDeleteAttachment()
{
	$.prompt('Do you want to delete this asset?', { buttons: {Yes: this.href, No: false}, callback:onAnswer });
	return false;
}

function onDeleteBlock()
{
	$.prompt('Do you want to delete this block?', { buttons: {Yes: this.href, No: false}, callback:onAnswer });
	return false;
}

function onDeletePage()
{
	$.prompt('Do you want to delete this page?', { buttons: {Yes: this.href, No: false}, callback:onAnswer });
	return false;
}

function onEditImage(ev1)
{
	// Only load scroller once
	if($('#collection-scroller').length == 0) {
		// get picture and block id
		qString = this.href;
		qBits = /imageedit=(\d+)/.exec(qString);
		imageId = qBits[1];
		qBits = /blockedit=(\d+)/.exec(qString);
		blockId = qBits[1];


		loadCollectionScroller(blockId, imageId);
	}
	
	window.location = String(window.location).replace(/\#.*$/, "") + "#wrap-main";
	return false;
}

function onSaveImage(ev)
{
	// page-65?c=217&blockedit=102&a=setimage&imageedit=0
	
	qString = this.href;
	qBits = /blockedit=(\d+)/.exec(qString);
	blockId = qBits[1];
	qBits = /c=(\d+)/.exec(qString);
	pictureId = qBits[1];
	qBits = /imageedit=(\d+)/.exec(qString);
	picturePlace = qBits[1];

	$.post('/system/ajax/block.action.savepicture.php', {'block_id': blockId, 'picture_place': picturePlace, 'picture_id': pictureId});

	// Change picture
	selector = "div#b" + blockId + " a.p" + picturePlace + " img.edit";
	console.info(selector);
	console.info($(selector));
	
	image = $(selector);
	imageHolder = image.parent();
	console.info(imageHolder);
	
	image.remove();
	imageHolder.load('/system/ajax/block.action.showpicture.php', {'block_id': blockId, 'picture_place': picturePlace, 'picture_id': pictureId});
	//

	//$("div.box-head").load('/system/ajax/block.action.showpicture.php', {'block_id': blockId, 'picture_place': picturePlace, 'picture_id': pictureId});
// eg $("#myElement").remove().after("<div>new element<\/div>"); 

	// Remove collection scroller
	$("div#scroll").remove();

	return false;
}


function onAnswer(answer, m)
{
	if(answer != false) {
		window.location = answer;
	}
}

function onUpload()
{
	$('#loaderImg').removeClass("hideme");
	return true;
}

function ajaxMoveBlock(blockId, dir)
{
	pageId = getPageIdFromClass($("#wrap-main").attr("class"));
	$.post('/system/ajax/block.action.move.php', {'page_id': pageId, 'block_id': blockId, 'dir': dir});
}

function getBlockIdFromClass(classString)
{
	classBits = /bl(\d+)/.exec(classString);
	return (classBits != null) ? classBits[1] : 0;
}

function getPageIdFromClass(classString)
{
	console.info(classString);
	classBits = /p(\d+)/.exec(classString);
	return (classBits != null) ? classBits[1] : 0;
}

function loadCollectionScroller(blockId, pictureId)
{
	// Show loading animation
	$("#loader").css("display", "block");

	// Load scroller HTML
	$('div.fix').after('<div id="scroll">');
	$('div#scroll').load('/system/ajax/html/assetscroller.php', {'blockId':blockId, 'pictureId':pictureId}, function(){
		setupScrollingCollection();
		$("div#scroll ul.items a").bind('click', onSaveImage);
		$("#loader").css("display", "none");
	});
}

function showHideUpDownButtons(lowerBlock, higherBlock)
{
	if(lowerBlock.next().length == 0) {
		$('.btnDown', lowerBlock).addClass('hidden');
		$('.btnDown', higherBlock).removeClass('hidden');
	}
	else if(higherBlock.prev().length == 0) {
		$('.btnUp', higherBlock).addClass('hidden');
		$('.btnUp', lowerBlock).removeClass('hidden');
	}
}