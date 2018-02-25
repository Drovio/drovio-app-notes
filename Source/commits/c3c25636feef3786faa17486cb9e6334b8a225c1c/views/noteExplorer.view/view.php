<?php
//#section#[header]
// Use Important Headers
use \API\Platform\importer;
use \API\Platform\engine;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");

// Import DOM, HTML
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");

use \UI\Html\DOM;
use \UI\Html\HTML;

// Import application for initialization
importer::import("AEL", "Platform", "application");
use \AEL\Platform\application;

// Increase application's view loading depth
application::incLoadingDepth();

// Set Application ID
$appID = 80;

// Init Application and Application literal
application::init(80);
// Secure Importer
importer::secure(TRUE);

// Import SDK Packages
importer::import("API", "Profile");
importer::import("UI", "Apps");

// Import APP Packages
application::import("Main");
//#section_end#
//#section#[view]
use \API\Profile\account;
use \UI\Apps\APPContent;

use \APP\Main\noteManager;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "noteExplorerContainer", TRUE);
$noteList = HTML::select(".noteExplorer .noteList")->item(0);

// Get all notes
$ntm = new noteManager(noteManager::PUBLIC_NOTE);
$publicNotes = $ntm->getAllNotes();
$ntm = new noteManager(noteManager::PRIVATE_NOTE);
$privateNotes = $ntm->getAllNotes();

$allNotes = $publicNotes + $privateNotes;
uksort($allNotes , "sortNotes");
if (!empty($allNotes))
	HTML::innerHTML($noteList, "");
foreach ($allNotes as $noteID => $noteInfo)
{
	// Create note element
	$ntrow = DOM::create("div", "", $noteID, "ntrow");
	DOM::append($noteList, $ntrow);
	if (isset($publicNotes[$noteID]))
		HTML::addClass($ntrow, "public");
	
	// Set static nav
	$appContent->setStaticNav($ntrow, $ref = "", $targetcontainer = "", $targetgroup = "", $navgroup = "ntGroup", $display = "none");
	
	// Set action to load note
	$attr = array();
	$attr['type'] = (isset($publicNotes[$noteID]) ? noteManager::PUBLIC_NOTE : noteManager::PRIVATE_NOTE);
	$attr['id'] = $noteID;
	$actionFactory->setAction($ntrow, $viewName = "noteEditor", $holder = ".simpleNotesApplication .noteContainer", $attr, $loading = TRUE);
		
	$ico = DOM::create("div", "", "", "ntico");
	DOM::append($ntrow, $ico);
		
	// Set header container
	$header = DOM::create("div", "", "", "ntheader");
	DOM::append($ntrow, $header);
	
	// Time
	$day = date("z-Y", time());
	$note_day = date("z-Y", $noteInfo['time_updated']);
	if ($day == $note_day)
		$noteDateFormat = "H:i";
	else
		$noteDateFormat = "d/n/y";
	$noteDate = date($noteDateFormat, $noteInfo['time_updated']);
	$ntdate = DOM::create("div", $noteDate, "", "ntdate");
	DOM::append($header, $ntdate);
	
	// title
	$nttitle = DOM::create("div", $noteInfo['title'], "", "nttitle");
	DOM::append($header, $nttitle);
	
	// Author
	if ($noteInfo['authorID'] == account::getAccountID())
		$authTitle = $appContent->getLiteral("notes.explorer", "lbl_author_you");
	else
	{
		$authorInfo = account::info($noteInfo['authorID']);
		$attr = array();
		$attr['author'] = $authorInfo['accountTitle'];
		$authTitle = $appContent->getLiteral("notes.explorer", "lbl_author", $attr);
	}
	$authorTitle = DOM::create("div", $authTitle, "", "ntauthor");
	DOM::append($header, $authorTitle);
}

// Return output
return $appContent->getReport();

function sortNotes($noteA, $noteB)
{
	return ($noteA['time_updated'] < $noteB['time_updated']) ? 1 : -1;
}
//#section_end#
?>