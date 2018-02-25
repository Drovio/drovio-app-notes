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
importer::import("AEL", "Literals");
importer::import("UI", "Apps");

// Import APP Packages
application::import("Main");
//#section_end#
//#section#[view]
use \UI\Apps\APPContent;
use \APP\Main\noteManager;

$noteType = engine::getVar("type");
$noteID = engine::getVar("id");
if (engine::isPost())
{
	// Remove note
	$ntManager = new noteManager($noteType, $noteID);
	$status = $ntManager->remove();

	// Return reload action
	$appContent = new APPContent();
	$appContent->addReportAction($name = "notes.remove", $value = $noteID);
	return $appContent->getReport();
}
//#section_end#
?>