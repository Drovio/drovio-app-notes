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
importer::import("API", "Geoloc");
importer::import("UI", "Apps");

// Import APP Packages
application::import("Main");
//#section_end#
//#section#[view]
use \API\Geoloc\datetimer;
use \UI\Apps\APPContent;

use \APP\Main\noteManager;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "simpleNotesApplicationContainer", TRUE);

// Append note explorer
$sidebar = HTML::select(".simpleNotesApplication .sidebar")->item(0);
$explorer = $appContent->getAppViewContainer($viewName = "noteExplorer", $attr = array(), $startup = FALSE, $containerID = "", $loading = FALSE, $preload = TRUE);
DOM::append($sidebar, $explorer);


// Note container toolbar
$currentTime = HTML::select(".noteOuterContainer .current_time")->item(0);
$dateTime = date($format = 'F d, Y, H:i', $time = time());
DOM::innerHTML($currentTime, $dateTime);

// Create new note
$newPublicNote = HTML::select(".create_new .sbutton.new_public")->item(0);
$attr = array();
$attr['type'] = noteManager::PUBLIC_NOTE;
$attr['create_new'] = 1;
$actionFactory->setAction($newPublicNote, $viewName = "noteEditor", $holder = ".simpleNotesApplication .noteContainer", $attr, $loading = TRUE);

$newPrivateNote = HTML::select(".create_new .sbutton.new_private")->item(0);
$attr = array();
$attr['type'] = noteManager::PRIVATE_NOTE;
$attr['create_new'] = 1;
$actionFactory->setAction($newPrivateNote, $viewName = "noteEditor", $holder = ".simpleNotesApplication .noteContainer", $attr, $loading = TRUE);

// Return output
return $appContent->getReport();
//#section_end#
?>