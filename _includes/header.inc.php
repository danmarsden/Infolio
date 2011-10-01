<?php

/**
 * header.inc.php
 * Produces the HTML for the header section of the page with all the correct menus.
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: header.inc.php 827 2009-12-18 16:36:47Z richard $
 * @link       NA
 * @since      NA
*/
include_once('ui.inc.php');

$helpHref = 'Help.php';
switch($page->getName())
{
	case 'Login':
		$helpHref .= "?section=" . HELP_LOGIN;
		break;
	case 'About-me':
		$helpHref .= "?section=" . HELP_ABOUT_ME;
		break;
	case 'Collection':
		$helpHref .= "?section=" . HELP_COLLECTION;
		break;
	case 'Settings':
		$helpHref .= "?section=" . HELP_SETTINGS;
		break;
	default:
		$helpHref .= "?section=" . HELP_PAGES;
		break;
}

$accessibilityMenu = new Menu( array(	Link::CreateImageLink($studentTheme->Icon('small-text', 'Smaller text'), $page->PathWithQueryString(array('fs'=>Theme::SIZE_SMALL)), Image::SIZE_ORIGINAL, array('class'=>'bSmall','alt'=>'Smaller text')),
										Link::CreateImageLink($studentTheme->Icon('med-text', 'Medium text'), $page->PathWithQueryString(array('fs'=>Theme::SIZE_MEDIUM)), Image::SIZE_ORIGINAL, array('class'=>'bMedium','alt'=>'Medium text')),
										Link::CreateImageLink($studentTheme->Icon('large-text', 'Bigger text'), $page->PathWithQueryString(array('fs'=>Theme::SIZE_BIG)), Image::SIZE_ORIGINAL, array('class'=>'bBig','alt'=>'Larger text')),
										Link::CreateImageLink($studentTheme->Icon('invert-text', 'Swap colours'), $page->PathWithQueryString(array('a'=>EventDispatcher::ACTION_COLOUR_SWAP)), Image::SIZE_ORIGINAL, array('class'=>'bSwap','alt'=>'Swap colours')) ),
								'nav-access' );

$toolMenu = new Menu( array(	Link::CreateImageLink($studentTheme->Icon('help', 'Help'), $helpHref, Image::SIZE_ORIGINAL, array('class'=>'bHelp','alt'=>'Help'))
								), 'nav-tools' );

// Get menu and set selected tab
if( isset($studentUser) ) {
	$tabsMenu =  $studentUser->getTabMenu();

	$tabsMenu->setAsActiveLink( $page->getSectionName() );

	$toolMenu->addLink( Link::CreateImageLink($studentTheme->Icon('logout', 'Logout'), 'login.php?log=out', Image::SIZE_ORIGINAL, array('alt'=>'Logout')) );
	$toolMenu->addLink( Link::CreateImageLink($studentTheme->Icon('settings', 'Settings'), 'settings.php', Image::SIZE_ORIGINAL, array('class'=>'bSettings','alt'=>'Settings')) );
}

?><div id="wrap-head"><?php
print $accessibilityMenu->Html();
print $toolMenu->Html();
?></div>
