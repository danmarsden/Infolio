<?php
/**
 * leaplib.php - imports a  LEAP export of a user's infolio
 * See http://wiki.cetis.ac.uk/LEAP2A_specification
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
*/

$gid = Safe::get('id');
     ?>

    <?php print render_messages(); ?>
    <script type="text/javascript" src="/admin/_scripts/bo-user-form.js"></script>
    <div id="dataFormContainer">
        
                <form method="post" id="userform" action="../../import.php" dojoType="dijit.form.Form" enctype="multipart/form-data">
                <input type="hidden" name="type" value="user" />
                <h2>User Import</h2>
                <table class="dataForm" id="loginTable">
                    <tr>
                        <td class="captionLabel">Username</td>
                        <td><input 
                            dojoType="dijit.form.ValidationTextBox" 
                            type="text"
                            name="username"
                            id="username"
                            required="true"
                            <? if(isset($gid)){?>disabled="disabled"<? } ?>
                            value="<? print (isset($user)) ? $user->getUsername() : ''; ?>" /> <? print TEXT_FIELD_REQUIRED; ?>
                            <div id="inlineNotificationUsername" class="inlineNotification"></div></td>
                    </tr>
                    <?
                    $passwordMessage = '';
                    $passwordValue = '';
                    $passwordRequired = '';
                    if (isset($user)) {
                        // Print password for existing students
                        if( $user->getPermissionManager()->isMemberOf(PermissionManager::USER_STUDENT) ) {
                            $passwordValue = 'value="' . $user->getPermissionManager()->getPassword() . '"';
                        }
                        else {
                            $passwordMessage = '<tr><td colspan="2">Existing password is not shown for admin users.</td></tr>';
                        }
                    }
                    else {
                        // New user requires password
                        $passwordRequired = 'required="true"';
                    }
                    
                    ?>
                    <tr>
                        <td class="captionLabel">Password</td>
                        <td><input 
                            dojoType="dijit.form.ValidationTextBox" 
                            type="text"
                            name="password"
                            id="password"
                            <? print $passwordValue . $passwordRequired; ?>
                            invalidMessage="Please type a password" /> <? print TEXT_FIELD_REQUIRED; ?>
                            <div id="inlineNotificationPassword" class="inlineNotification"></div></td>
                    </tr>
                    <tr>
                        <td class="captionLabel">Confirm password</td>
                        <td><input 
                            dojoType="dijit.form.ValidationTextBox"
                            type="text"
                            name="password2"
                            id="password2"
                            <? print $passwordValue . $passwordRequired;; ?>
                            validator="return theSame(this, dijit.byId('password'));"
                            invalidMessage="This password must match your first password" /> <? print TEXT_FIELD_REQUIRED; ?></td>
                    </tr>
                    <? print $passwordMessage; ?>
                    <tr>
                        <td class="captionLabel">User type</td>
                        <td>
                            <select 
                                dojoType="dijit.form.FilteringSelect" 
                                id="userType" 
                                name="userType">
                                <? print PermissionManager::HtmlSelectOptions($user); ?>
                            </select> <?php echo TEXT_FIELD_REQUIRED?>
                        </td>
                    </tr>
					<tr>
						<td class="captionLabel">Institution</td>
						<td>
                        <?php if($adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)) { ?>
                            <select dojoType="dijit.form.FilteringSelect"
                                name="institution_id"
                                id="institution_id"
                                <option></option>
                                <?
                                    $institutions = Institution::RetrieveAll();
                                    foreach($institutions as $institution)
                                    {
                                        ?><option value="<? print $institution->getId(); ?>"><? print $institution->getName(); ?></option><?
                                    }
                                ?>
                            </select>
<?php                          } else {
                                   echo '<input type="hidden" name="institution_id" value="'.$adminUser->getInstitution()->getId() .'"/>';
                                   echo $adminUser->getInstitution()->getName();
                               }
?>
						</td>
					</tr>
                    <tr>
                        <td class="captionLabel">LEAP2A File</td>
                        <td><input type="file" name="leapimport"/></td>
                    </tr>
                    <tr>
                        <td class="captionLabel"></td>
                        <td><input type="submit" value="Submit" /></td>
                    </tr>
                </table>
                </form>
            </div>
