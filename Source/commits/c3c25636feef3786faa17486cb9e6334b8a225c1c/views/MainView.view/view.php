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
importer::import("UI", "Apps");

// Import APP Packages
//#section_end#
//#section#[view]
use \UI\Apps\APPContent;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "simpleNotesApplicationContainer", TRUE);

// Append note explorer
$sidebar = HTML::select(".simpleNotesApplication .sidebar")->item(0);
$explorer = $appContent->getAppViewContainer($viewName = "noteExplorer", $attr = array(), $startup = FALSE, $containerID = "noteExplorerContainer", $loading = TRUE, $preload = TRUE);
DOM::append($sidebar, $explorer);

// Return output
return $appContent->getReport();
//#section_end#
?>