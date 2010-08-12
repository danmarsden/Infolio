/**
 * @author Richard garside
 * @copyright  2009 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 */
$(document).ready(function()
{
	$('#upload-btn').bind('click', onUpload);
});

function onUpload()
{
	$('#loaderImg').removeClass("hideme");
	return true;
}