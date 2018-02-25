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
importer::import("UI", "Developer");
importer::import("UI", "Forms");
importer::import("UI", "Navigation");
importer::import("UI", "Presentation");

// Import APP Packages
application::import("Main");
//#section_end#
//#section#[view]
use \UI\Apps\APPContent;
use \UI\Developer\editors\HTML5Editor;
use \UI\Forms\templates\simpleForm;
use \UI\Navigation\navigationBar;
use \UI\Presentation\notification;
use \UI\Presentation\popups\popup;

use \APP\Main\noteManager;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContentContainer = $appContent->build("", "noteEditorContainer")->get();

$noteType = engine::getVar("type");
$noteID = engine::getVar("id");
if (engine::isPost())
{
	// Create note manager
	$ntManager = new noteManager($noteType, $noteID);

	// Update note
	$status = $ntManager->update($_POST['note']);
	
	// Create notification popup
	$reportNtf = new notification();
	if ($status)
	{
		$reportNtf->build($type = notification::SUCCESS, $header = FALSE, $timeout = FALSE, $disposable = FALSE);
		$reportMessage = $reportNtf->getMessage("success", "success.save_success");
	}
	else
	{
		$reportNtf->build($type = notification::ERROR, $header = FALSE, $timeout = FALSE, $disposable = FALSE);
		$reportMessage = $reportNtf->getMessage("error", "err.save_error");
	}
	
	$reportNtf->append($reportMessage);
	$notification = $reportNtf->get();
	
	// Create popup
	$pp = new popup();
	$pp->fade(TRUE);
	$pp->timeout(TRUE);
	$pp->build($notification);
	
	// Reload note list
	$pp->addReportAction($type = "notes.list.reload", $value = "");
	
	return $pp->getReport();
}


// Get whether to create a new note
$newNote = engine::getVar("create_new");
if ($newNote)
{
	// Create new note
	$ntManager = new noteManager($noteType);
	$ntManager->create();
	$noteID = $ntManager->getNoteID();
	
	// Set to reload note list
	$appContent->addReportAction($type = "notes.list.reload");
}

// Set id
HTML::attr($appContentContainer, "id", $noteID);

// Initialize note manager
$ntManager = new noteManager($noteType, $noteID);

// Create generic form
$form = new simpleForm();
$noteForm = $form->build("", FALSE)->engageApp("noteEditor")->get();
$appContent->append($noteForm);

// Create note info
$input = $form->getInput($type ="hidden", $name = "type", $value = $noteType, $class = "", $autofocus = FALSE, $required = FALSE);
$form->append($input);

$input = $form->getInput($type ="hidden", $name = "id", $value = $noteID, $class = "", $autofocus = FALSE, $required = FALSE);
$form->append($input);

$outerContainer = DOM::create("div", "", "", "editorOuterContainer");
$form->append($outerContainer);

// navigation bar
$navbar = new navigationBar();
$navigationBar = $navbar->build($dock = navigationBar::TOP, $outerContainer)->get();
DOM::append($outerContainer, $navigationBar);

// Save button
$saveTool = DOM::create("button", "", "", "objTool save");
DOM::attr($saveTool, "type", "submit");
$navbar->insertToolbarItem($saveTool);

// Get note content
$noteContent = $ntManager->get();

$htmleditor = new HTML5Editor("note");
$editor = $htmleditor->build($noteContent)->get();
DOM::append($outerContainer, $editor);

// Return output
return $appContent->getReport();
//#section_end#
?>