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
 * Class Files
 *
 * Provide methods to modify files and folders.
 * @copyright  Leo Feyer 2005-2009, CyberSpectrum 2009
 * @author     Leo Feyer <leo@typolight.org>, Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    Library
 */
class Files
{

	/**
	 * Current object instance (Singleton)
	 * @var object
	 */
	protected static $objInstance;


	/**
	 * Prevent direct instantiation (Singleton)
	 */
	protected function __construct() {}


	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final private function __clone() {}


	/**
	 * Instantiate a files driver object and return it (Factory)
	 * @return object
	 */
	public static function getInstance()
	{
		if (!is_object(self::$objInstance))
		{
			// Use FTP to modify files
			if (($GLOBALS['TL_CONFIG']['useFTP'] && strlen($GLOBALS['TL_CONFIG']['ftpHost']) && strlen($GLOBALS['TL_CONFIG']['ftpUser']) && strlen($GLOBALS['TL_CONFIG']['ftpPass'])) || strlen($GLOBALS['TL_CONFIG']['useSMHClass']))
			{
				// allow "custom" SMH handlers to register themselves.
				if($GLOBALS['TL_CONFIG']['useSMHClass'])
				{
					// Connect to SMH server
					self::$objInstance = new $GLOBALS['TL_CONFIG']['useSMHClass']();
					return self::$objInstance;
				} else {
					// Connect to FTP server
					if (($resConnection = ftp_connect($GLOBALS['TL_CONFIG']['ftpHost'])) != false)
					{
						// Login
						if (ftp_login($resConnection, $GLOBALS['TL_CONFIG']['ftpUser'], $GLOBALS['TL_CONFIG']['ftpPass']))
						{
							self::$objInstance = new FTP($resConnection);
							return self::$objInstance;
						}
					}
				}
			}

			self::$objInstance = new Files();
		}

		return self::$objInstance;
	}


	/**
	 * Create a directory
	 * @param string
	 * @return boolean
	 */
	public function mkdir($strDirectory)
	{
		return @mkdir(TL_ROOT . '/' . $strDirectory);
	}


	/**
	 * Remove a directory
	 * @param string
	 * @return boolean
	 */
	public function rmdir($strDirectory)
	{
		return @rmdir(TL_ROOT. '/' . $strDirectory);
	}


	/**
	 * Open a file and return the handle
	 * @param string
	 * @param string
	 * @return resource
	 */
	public function fopen($strFile, $strMode)
	{
		return @fopen(TL_ROOT . '/' . $strFile, $strMode);
	}


	/**
	 * Write content to a file
	 * @param string
	 * @param string
	 * @return boolean
	 */
	public function fputs($resFile, $strContent)
	{
		return @fputs($resFile, $strContent);
	}


	/**
	 * Close a file
	 * @param resource
	 * @return boolean
	 */
	public function fclose($resFile)
	{
		return @fclose($resFile);
	}


	/**
	 * Rename a file or folder
	 * @param string
	 * @param string
	 * @return boolean
	 */
	public function rename($strOldName, $strNewName)
	{
		// Windows fix: delete target file
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && file_exists(TL_ROOT . '/' . $strNewName))
		{
			$this->delete($strNewName);
		}

		// Unix fix: rename case sensitively
		if (strcasecmp($strOldName, $strNewName) === 0 && strcmp($strOldName, $strNewName) !== 0)
		{
			@rename(TL_ROOT . '/' . $strOldName, TL_ROOT . '/' . $strOldName . '__');
			$strOldName .= '__';
		}

		return @rename(TL_ROOT . '/' . $strOldName, TL_ROOT . '/' . $strNewName);
	}


	/**
	 * Copy a file or folder
	 * @param string
	 * @param string
	 * @return boolean
	 */
	public function copy($strSource, $strDestination)
	{
		return @copy(TL_ROOT . '/' . $strSource, TL_ROOT . '/' . $strDestination);
	}


	/**
	 * Delete a file
	 * @param string
	 * @return boolean
	 */
	public function delete($strFile)
	{
		return @unlink(TL_ROOT . '/' . $strFile);
	}


	/**
	 * Change file mode
	 * @param string
	 * @param mixed
	 * @return boolean
	 */
	public function chmod($strFile, $varMode)
	{
		return @chmod(TL_ROOT . '/' . $strFile, $varMode);
	}


	/**
	 * Check whether a file is writeable
	 * @param string
	 * @return boolean
	 */
	public function is_writeable($strFile)
	{
		return @is_writeable(TL_ROOT . '/' . $strFile);
	}


	/**
	 * Move an uploaded file to another folder
	 * @param string
	 * @param string
	 * @return string
	 */
	public function move_uploaded_file($strSource, $strDestination)
	{
		return @move_uploaded_file($strSource, TL_ROOT . '/' . $strDestination);
	}
}

?>