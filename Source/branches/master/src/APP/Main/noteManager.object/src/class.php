<?php
//#section#[header]
// Namespace
namespace APP\Main;

require_once($_SERVER['DOCUMENT_ROOT'].'/_domainConfig.php');

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");

// Import application loader
importer::import("AEL", "Platform", "application");
use \AEL\Platform\application;
//#section_end#
//#section#[class]
/**
 * @library	APP
 * @package	Main
 * 
 * @copyright	Copyright (C) 2015 WebNotes. All rights reserved.
 */

importer::import("AEL", "Resources", "DOMParser");
importer::import("AEL", "Resources", "filesystem/fileManager");
importer::import("API", "Profile", "account");
importer::import("DEV", "Tools", "codeParser");

use \AEL\Resources\DOMParser;
use \AEL\Resources\filesystem\fileManager;
use \API\Profile\account;
use \DEV\Tools\codeParser;

/**
 * Note manager
 * 
 * Handles all notes for the application, both team and personal notes.
 * 
 * @version	2.0-1
 * @created	August 23, 2015, 13:11 (EEST)
 * @updated	August 24, 2015, 18:56 (EEST)
 */
class noteManager
{
	/**
	 * Public note.
	 * 
	 * @type	integer
	 */
	const PUBLIC_NOTE = 1;
	
	/**
	 * Personal note.
	 * 
	 * @type	integer
	 */
	const PRIVATE_NOTE = 2;
	
	/**
	 * The current note id.
	 * 
	 * @type	string
	 */
	private $noteID;
	
	/**
	 * The fileManager instance.
	 * 
	 * @type	fileManager
	 */
	private $fileManager;
	/**
	 * The DOMParser instance.
	 * 
	 * @type	DOMParser
	 */
	private $xmlParser;
	
	/**
	 * Create a new instance of the note manager.
	 * 
	 * @param	integer	$type
	 * 		The note type.
	 * 		This defines whether it is a team/public note or a personal/private note.
	 * 
	 * @param	string	$noteID
	 * 		The note id.
	 * 		Leave empty for new notes.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($type, $noteID = "")
	{
		// Set note id
		$this->noteID = $noteID;
		
		// Initialize file manager and xml parser
		$fltype = ($type == self::PUBLIC_NOTE ? fileManager::TEAM_MODE : fileManager::ACCOUNT_MODE);
		$this->fileManager = new fileManager($fltype, $shared = FALSE);
		
		$xmltype = ($type == self::PUBLIC_NOTE ? DOMParser::TEAM_MODE : DOMParser::ACCOUNT_MODE);
		$this->xmlParser = new DOMParser($xmltype, $shared = FALSE);
		
		// Initialize app
		$this->initializeApp();
	}
	
	/**
	 * Create a new note.
	 * 
	 * @param	string	$note
	 * 		The note contents.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($note = "New Note")
	{
		// Create snippet for title
		$title = $this->createTitle($note);
		
		// Create note entry at index file
		$this->noteID = "nt".mt_rand()."_".time();
		$entry = $this->xmlParser->create("nt", "", $this->noteID);
		$this->xmlParser->attr($entry, "title", $title);
		$this->xmlParser->attr($entry, "authorID", account::getAccountID());
		$this->xmlParser->attr($entry, "time_created", time());
		$this->xmlParser->attr($entry, "time_updated", time());
		
		// Append to root element
		$root = $this->xmlParser->evaluate("/notes")->item(0);
		$this->xmlParser->append($root, $entry);
		
		// Update index file
		$status = $this->xmlParser->update();
		if (!$status)
			return FALSE;
		
		// Create note file
		$note = codeParser::clear($note);
		return $this->fileManager->create($file = $this->noteID.".html", $contents = $note, $recursive = TRUE);
	}
	
	/**
	 * Get the note contents.
	 * 
	 * @return	string
	 * 		The note contents.
	 */
	public function get()
	{
		return $this->fileManager->get($file = $this->noteID.".html");
	}
	
	/**
	 * Get note info including author, time created and updated.
	 * 
	 * @return	array
	 * 		The note info array.
	 */
	public function info()
	{
		// Find entry
		$entry = $this->xmlParser->find($this->noteID);
		
		// Get all the information from the index file
		$noteInfo = array();
		$noteInfo['title'] = $this->xmlParser->attr($entry, "title");
		$noteInfo['authorID'] = $this->xmlParser->attr($entry, "authorID");
		$noteInfo['time_created'] = $this->xmlParser->attr($entry, "time_created");
		$noteInfo['time_updated'] = $this->xmlParser->attr($entry, "time_updated");
		
		// Return the info array
		return $noteInfo;
	}
	
	/**
	 * Update the note contents.
	 * 
	 * @param	string	$note
	 * 		The new note contents.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($note)
	{
		// Create snippet for title
		$title = $this->createTitle($note);
		
		// Set new title
		$entry = $this->xmlParser->find($this->noteID);
		$this->xmlParser->attr($entry, "title", $title);
		$this->xmlParser->attr($entry, "time_updated", time());
		$this->xmlParser->update();
		
		// Set note
		$note = codeParser::clear($note);
		return $this->fileManager->put($file = $this->noteID.".html", $contents = $note);
	}
	
	/**
	 * Remove the current note.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove entry from index
		$entry = $this->xmlParser->find($this->noteID);
		$this->xmlParser->replace($entry, NULL);
		$this->xmlParser->update();
		
		// Remove file
		return $this->fileManager->remove($file = $this->noteID.".html");
	}
	
	/**
	 * Get all notes from the index file.
	 * 
	 * @return	array
	 * 		An array of all notes by noteID and note title (snippet).
	 */
	public function getAllNotes()
	{
		// Initialize array of notes
		$allNotes = array();
		
		$noteElements = $this->xmlParser->evaluate("//nt");
		foreach ($noteElements as $ntElement)
		{
			// Get note id
			$noteID = $this->xmlParser->attr($ntElement, "id");
			
			// Get all note info
			$noteInfo = array();
			$noteInfo['title'] = $this->xmlParser->attr($ntElement, "title");
			$noteInfo['authorID'] = $this->xmlParser->attr($ntElement, "authorID");
			$noteInfo['time_created'] = $this->xmlParser->attr($ntElement, "time_created");
			$noteInfo['time_updated'] = $this->xmlParser->attr($ntElement, "time_updated");
			
			// Add to list
			$allNotes[$noteID] = $noteInfo;
		}
		
		return $allNotes;
	}
	
	/**
	 * Get the note id.
	 * 
	 * @return	string
	 * 		The note id.
	 */
	public function getNoteID()
	{
		return $this->noteID;
	}
	
	/**
	 * Create a snippet title given the note contents.
	 * 
	 * @param	string	$note
	 * 		The note contents.
	 * 
	 * @return	string
	 * 		The snippet title.
	 */
	private function createTitle($note)
	{
		// Create snippet for title
		return substr($note, 0, 100).(strlen($note) > 100 ? "..." : "");
	}
	
	/**
	 * Initialize the application files by creating the index file.
	 * 
	 * @return	void
	 */
	private function initializeApp()
	{
		// Load the index file
		try
		{
			$this->xmlParser->load($path = "/index.xml", $preserve = TRUE);
		}
		catch (Exception $ex)
		{
			// Create the index file if not exist
			$root = $this->xmlParser->create("notes");
			$this->xmlParser->append($root);
			
			// Save file
			$this->xmlParser->save($path = "/", $fileName = "index.xml", $format = TRUE);
		}
	}
}
//#section_end#
?>