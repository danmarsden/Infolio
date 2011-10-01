/**
 * @author Richard garside
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
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