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
 * Class SMHExtended
 *
 * Base class for extended Safe Mode handling.
 * @copyright  CyberSpectrum 2009
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    Library
 */
class SMHExtended extends Files
{
	/**
	 * Server connection
	 * @var resource
	 */
	protected $objConnection=NULL;

	/**
	 * Create the connection object and store it, finally make sure that the temp folders are writable
	 */
	protected function connect()
	{
		if($this->objConnection)
			return $this->objConnection;
		$objConnection = new $GLOBALS['TL_CONFIG']['useSMHClass']();
		// Connect to server
		if($objConnection->connect())
			$this->objConnection = $objConnection;
		else
			throw new Exception('SMHExtended could not connect to server.');
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
		return $objConnection;
	}

	protected function disconnect()
	{
		$this->objConnection->disconnect();
		unset($this->objConnection);
	}

	/**
	 * Create the object and store the resource and finally make sure that the temp folder is writable
	 */
	protected function __construct()
	{
		// nothing to do anymore due to lazy initialization.
	}

	public function __destruct()
	{
		$this->disconnect();
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
	public function mkdir($strDirectory)
	{
		$this->connect();
		$this->objConnection->mkdir($strDirectory);
	}

	/**
	 * Remove a directory
	 * @param string
	 * @return boolean
	 */
	public function rmdir($strDirectory)
	{
		$this->connect();
		return $this->objConnection->rmdir($strDirectory);
	}


	/**
	 * Open a file and return the handle
	 * @param string
	 * @param string
	 * @return resource
	 */
	public function fopen($strFile, $strMode)
	{
		$this->connect();
		return $this->objConnection->fopen($strFile, $strMode);
	}

	/**
	 * Close a file
	 * @param resource
	 * @return boolean
	 */
	public function fclose($resFile)
	{
		$this->connect();
		return $this->objConnection->fclose($resFile);
	}

	/**
	 * Rename a file or folder
	 * @param string
	 * @param string
	 * @return boolean
	 */
	public function rename($strOldName, $strNewName)
	{
		$this->connect();
		return $this->objConnection->rename($strOldName, $strNewName);
	}

	/**
	 * Copy a file or folder
	 * @param string
	 * @param string
	 * @return boolean
	 */
	public function copy($strSource, $strDestination)
	{
		$this->connect();
		return $this->objConnection->copy($strSource, $strDestination);
	}

	/**
	 * Delete a file
	 * @param string
	 * @return boolean
	 */
	public function delete($strFile)
	{
		$this->connect();
		return $this->objConnection->delete($strFile);
	}

	/**
	 * Change file mode
	 * @param string
	 * @param mixed
	 * @return boolean
	 */
	public function chmod($strFile, $varMode)
	{
		$this->connect();
		return $this->objConnection->chmod($strFile, $varMode);
	}

	/**
	 * Check whether a file is writeable
	 * @param string
	 * @return boolean
	 */
	public function is_writeable($strFile)
	{
		$this->connect();
		return $this->objConnection->is_writeable($strFile);
	}

	/**
	 * Move an uploaded file to another folder
	 * @param string
	 * @param string
	 * @return string
	 */
	public function move_uploaded_file($strSource, $strDestination)
	{
		$this->connect();
		return $this->objConnection->move_uploaded_file($strSource, $strDestination);
	}
}
?>