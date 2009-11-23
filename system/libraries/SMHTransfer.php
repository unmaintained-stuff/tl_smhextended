<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 *
 * The TYPOlight webCMS is an accessible web content management system that 
 * specializes in accessibility and generates W3C-compliant HTML code. It 
 * provides a wide range of functionality to develop professional websites 
 * including a built-in search engine, form generator, file and user manager, 
 * CSS engine, multi-language support and many more. For more information and 
 * additional TYPOlight applications like the TYPOlight MVC Framework please 
 * visit the project website http://www.typolight.org.
 * 
 * PHP version 5
 * @copyright	Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package		SMHExtended
 * @license		LGPL 
 * @filesource
 */


/**
 * Class SMHTransfer
 *
 * Base class for extended Safe Mode handling.
 * @copyright  CyberSpectrum 2009
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    Library
 */
class SMHTransfer extends Files
{
	/**
	 * Server connection
	 * @var resource
	 */
	var $resConnection=NULL;
	var $ftpHost=NULL;
	var $ftpPort=NULL;
	var $ftpUser=NULL;
	var $ftpPass=NULL;
	var $ftpPath=NULL;

	/**
	 * Files array
	 * @var array
	 */
	protected $arrFiles = array();
	
	protected function connect()
	{
		// empty wrapper, will get filled in child classes. Return no connection handle
		return NULL;
	}

	// empty wrapper, will get filled in child classes.
	protected function disconnect()	{}

	/**
	 * Create the object and store the resource and finally make sure that the temp folder is writable
	 */
	protected function __construct($foo=0)
	{
		// Use FTP to modify files
		$this->ftpHost = $GLOBALS['TL_CONFIG']['ftpHost'];
		$this->ftpPort = $GLOBALS['TL_CONFIG']['ftpPort'];
		$this->ftpUser = $GLOBALS['TL_CONFIG']['ftpUser'];
		$this->ftpPass = $GLOBALS['TL_CONFIG']['ftpPass'];
		$this->ftpPath = $GLOBALS['TL_CONFIG']['ftpPath'];
		// Connect to server
		if (!($resConnection = $this->connect()))
		{
			throw new Exception('Could not connect to ftp.');
		}
		$this->resConnection = $resConnection;
		// security, clean user id and password as we won't need them anymore.
		$this->ftpUser = NULL;
		$this->ftpPass = NULL;
		// Make folders writable
		if (!is_writable(TL_ROOT . '/system/tmp'))
		{
			$this->chmod('system/tmp', 0777);
		}
		if (!is_writable(TL_ROOT . '/system/html'))
		{
			$this->chmod('system/html', 0777);
		}
		if (!is_writable(TL_ROOT . '/system/logs'))
		{
			$this->chmod('system/logs', 0777);
		}
	}

	public function __destruct()
	{
		$this->disconnect();
		unset($this->resConnection);
	}


///////////////////////////////////////////////////
// Interface routines used by the framework      //
// All drivers must implement them               //
///////////////////////////////////////////////////

	/**
	 * Create a directory
	 * @param string
	 * @return boolean
	 */
	public function mkdir($strDirectory) {}

	/**
	 * Remove a directory
	 * @param string
	 * @return boolean
	 */
	public function rmdir($strDirectory) {}

	/**
	 * Open a file and return the handle
	 * @param string
	 * @param string
	 * @return resource
	 */
	public function fopen($strFile, $strMode) {}

	/**
	 * Close a file
	 * @param resource
	 * @return boolean
	 */
	public function fclose($resFile) {}

	/**
	 * Rename a file or folder
	 * @param string
	 * @param string
	 * @return boolean
	 */
	public function rename($strOldName, $strNewName) {}

	/**
	 * Copy a file or folder
	 * @param string
	 * @param string
	 * @return boolean
	 */
	public function copy($strSource, $strDestination) {}

	/**
	 * Delete a file
	 * @param string
	 * @return boolean
	 */
	public function delete($strFile) {}

	/**
	 * Change file mode
	 * @param string
	 * @param mixed
	 * @return boolean
	 */
	public function chmod($strFile, $varMode) {}

	/**
	 * Check whether a file is writeable
	 * @param string
	 * @return boolean
	 */
	public function is_writeable($strFile) {}

	/**
	 * Move an uploaded file to another folder
	 * @param string
	 * @param string
	 * @return string
	 */
	public function move_uploaded_file($strSource, $strDestination) {}
}
?>