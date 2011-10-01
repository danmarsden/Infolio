<?php

/**
 * The code behind for print version of index.php
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: index.inc.php 825 2009-12-14 09:28:52Z richard $
 * @link       NA
 * @since      NA
*/

//include_once('conf.php');
//include_once("../system/initialise.php");
include_once('class/Events/HomePageEventDispatcher.class.php');
include_once('class/si/Theme.class.php');
include_once('class/si/SimplePage.class.php');
include_once('model/User.class.php');

/* ** Setup page ** */

$page = new SimplePage();
$page->getJavaScriptVariables()->addVariable('phpTabPlace', 0);
$page->setTitle("{$studentUser->getFirstName()}'s home page");
$page->setName('About-me');
$studentDetails = $studentUser->HtmlUserDetails($page, $studentTheme);
