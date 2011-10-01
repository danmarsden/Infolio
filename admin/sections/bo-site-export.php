<?php
/**
 * leaplib.php - Creates a LEAP export of a user's infolio
 * See http://wiki.cetis.ac.uk/LEAP2A_specification
 *
 * @author     Dan Marsden, Catalyst IT Ltd, (http://danmarsden.com)
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
        $html = '<h2>Export Institution</h2>';
        $html .= '<form method="post" action="/export.php"><ul style="list-style:none;">';
        $html .= '<input type="hidden" name="tab_count" value="0" />';
        $html .= '</ul>';
        $html .= "<h3>Choose an export format</h3><div class=\"element\"><div><input type=\"radio\" class=\"radio\" id=\"export_formate091\" name=\"format\" tabindex=\"1\" value=\"html\" checked=\"checked\">"
                ."<label for=\"export_formate091\">Standalone HTML Website</label><div class=\"radio-description\">Creates a self-contained website for each user with their portfolio data. You cannot import this again, but it's readable in a standard web browser.</div></div><div>"
                ."<input type=\"radio\" class=\"radio\" id=\"export_format442a\" name=\"format\" tabindex=\"1\" value=\"leap\"> <label for=\"export_format442a\">LEAP2A</label><div class=\"radio-description\">Gives you an export for each user in the LEAP2A standard format. You can use this to import your data into other LEAP2A compliant systems, although the export is hard for humans to read.</div></div></div>";

        $rinst = Safe::request('inst');
        $chosenInstituteId = (isset($rinst)) ?
			$rinst :
			$adminUser->getInstitution()->getId();

		$institutions = Institution::RetrieveAll();
		$html.= '<label for "inst">Select Institution</label><select name="inst">';
        $html.="<option value=''>Export All</option>";
		foreach($institutions as $institution) {
            //check if institution has users.
            $numusers= $institution->getNumUsers();
            if (!empty($numusers)) {
			    $html.= "<option value=\"{$institution->getId()}\"";
			    if(isset($chosenInstituteId) && $institution->getId() == $chosenInstituteId) $html.= ' selected="selected"';
		        $html.= ">{$institution->getName()}</option>";
		    }
        }
		$html.= '</select><br/><br/>';
        $html .= '<input type="hidden" name="siteexport" value="true" />';
        $html .= '<input type="submit" value="Generate new institution export" />';

        $html .= '</form>';
        echo $html;
?>
            <div id="siteimport">
            <h2>Import Institution</h2>
            <form enctype="multipart/form-data" method="post" id="userform" action="../../import.php">
            <input type="hidden" name="type" value="site" />
            <table class="dataForm" id="leapimport">
                <tr>
                    <td class="captionLabel">File</td>
                    <td><input type="file" name="leapimport"/></td>
                </tr>
                <tr>
                    <td class="captionLabel"></td>
                    <td><input type="submit" value="Submit" /></td>
                </tr>
            </table>

            </form>
            </div>