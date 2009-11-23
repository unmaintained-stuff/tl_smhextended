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
 * Class SMHSFTP
 *
 * Provide methods to modify files and folders via SFTP. Based upon FTP.php (c) by Leo Feyer
 * @copyright  CyberSpectrum 2009
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    Library
 */

class SMHSFTP extends SMHTransfer
{
	public function getLog()
	{
		return $this->resConnection->getSFTPLog();
	}
	
	protected function connect()
	{
		// we have to mangle the include path a little to find our plugins
		$oldIncludePath=get_include_path();
		set_include_path($oldIncludePath . ':' . TL_ROOT . '/plugins/phpseclib/:' . TL_ROOT . '/plugins/phpseclib/Net');
		include('SFTP.php');
		set_include_path($oldIncludePath);
		// Connect to FTP server
		if(!is_numeric($this->ftpPort) || $this->ftpPort==0)
			$this->ftpPort = 22;
		//define('NET_SSH2_LOGGING', true);
		define('NET_SFTP_LOGGING', true);
		if (($resConnection = new Net_SFTP($this->ftpHost, $this->ftpPort, 5)) != false)
		{
			// Login
			if (!$resConnection->login($this->ftpUser, $this->ftpPass))
			{
				throw new Exception('Could not login to sftp: ' . $resConnection->getLog());
			}

			// change to root directory to ensure we can really work.
			$resConnection->chdir($this->ftpPath);
			return $resConnection;
		} else {
			throw new Exception('Could not connect to ftp.');
		}
	}
	
	protected function disconnect()
	{
		$this->resConnection->_disconnect(NET_SSH2_DISCONNECT_BY_APPLICATION);
	}

	/**
	 * Create a directory
	 * @param string
	 * @return boolean
	 */
	public function mkdir($strDirectory)
	{
		return (file_exists($this->ftpPath . $strDirectory) && is_dir($this->ftpPath . $strDirectory)) || $this->resConnection->mkdir($this->ftpPath . $strDirectory) ? true : false;
	}


	/**
	 * Remove a directory
	 * @param string
	 * @return boolean
	 */
	public function rmdir($strDirectory)
	{
		return $this->resConnection->rmdir($this->ftpPath . $strDirectory);
	}

	/**
	 * Open a file and return the handle
	 * @param string
	 * @param string
	 * @return resource
	 */
	public function fopen($strFile, $strMode)
	{
		$tmpFile = TL_ROOT . '/system/tmp/' . md5(uniqid('', true));
		$resFile = fopen($tmpFile, $strMode);
		// Copy temp file to server
		if (!file_exists(TL_ROOT . '/' . $strFile))
		{
			if (!$this->resConnection->put($this->ftpPath . $strFile, $tmpFile, NET_SFTP_LOCAL_FILE))
			{
				return false;
			}
		}
		// and keep uri in buffer to still have the mapping when closing the file.
		$arrData = stream_get_meta_data($resFile);
		$this->arrFiles[$arrData['uri']] = $strFile;
		return $resFile;
	}

	/**
	 * Close a file
	 * @param resource
	 * @return boolean
	 */
	public function fclose($resFile)
	{
		// no stream pointer => nothing to do.
		if (!is_resource($resFile))
		{
			return true;
		}
		// close stream and move file via SFTP to real location (overwrite the old one).
		$arrData = stream_get_meta_data($resFile);
		$fclose = fclose($resFile);
		if (isset($this->arrFiles[$arrData['uri']]))
		{
			$this->rename(preg_replace('/^' . preg_quote(TL_ROOT, '/') . '\//i', '', $arrData['uri']), $this->arrFiles[$arrData['uri']]);
		}
		return $fclose;
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
		// Rename directories
		if (is_dir(TL_ROOT . '/' . $strOldName))
		{
			return $this->resConnection->rename($this->ftpPath . $strOldName, TL_ROOT . '/' . $strNewName);
		}
		// Unix fix: rename case sensitively
		if (strcasecmp($strOldName, $strNewName) === 0 && strcmp($strOldName, $strNewName) !== 0)
		{
			$this->resConnection->rename($this->ftpPath . $strOldName, $this->ftpPath . $strOldName . '__');
			$strOldName .= '__';
		}
		// Copy files to set the correct owner
		$return = $this->copy($strOldName, $strNewName);

		// Delete the old file
		if (!@unlink(TL_ROOT . '/' . $strOldName))
		{
			$this->delete($strOldName);
		}
		return $return;
	}


	/**
	 * Copy a file or folder
	 * @param string
	 * @param string
	 * @return boolean
	 */
	public function copy($strSource, $strDestination)
	{
		if(!file_exists(TL_ROOT . '/' . $strSource))
			return false;
		if(is_file(TL_ROOT . '/' . $strSource))
		{
			$return = $this->resConnection->put($this->ftpPath . $strDestination, TL_ROOT . '/' . $strSource, NET_SFTP_LOCAL_FILE);
		} else {
			// do we have to copy recurively in here?
			$return = $this->mkdir($strDestination);
		}
		$this->chmod($strDestination, 0644);
		return $return;
	}


	/**
	 * Delete a file
	 * @param string
	 * @return boolean
	 */
	public function delete($strFile)
	{
		return $this->resConnection->delete($this->ftpPath . $strFile);
	}


	/**
	 * Change file mode
	 * @param string
	 * @param mixed
	 * @return boolean
	 */
	public function chmod($strFile, $varMode)
	{
		return $this->resConnection->chmod($varMode, $this->ftpPath . $strFile);
	}


	/**
	 * Check whether a file is writeable
	 * @param string
	 * @return boolean
	 */
	public function is_writeable($strFile)
	{
		return true;
	}


	/**
	 * Move an uploaded file to another folder
	 * @param string
	 * @param string
	 * @return string
	 */
	public function move_uploaded_file($strSource, $strDestination)
	{
		return $this->resConnection->put($this->ftpPath . $strDestination, $strSource, NET_SFTP_LOCAL_FILE);
	}
}

?>