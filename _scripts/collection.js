/**
 * @author Richard garside
 * @copyright  2009 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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