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
 * Add palettes to tl_settings
 */

$GLOBALS['TL_DCA']['tl_settings']['subpalettes']['useFTP'] = 'useSMHClass,ftpPort,' . $GLOBALS['TL_DCA']['tl_settings']['subpalettes']['useFTP'];

/**
 * Add fields to tl_settings
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['useSMHClass'] = array
	(
		'label'						=> &$GLOBALS['TL_LANG']['tl_settings']['useSMHClass'],
		'exclude'					=> true,
		'inputType'					=> 'select',
		'default'					=> false,
		'options_callback'        => array('tl_settings_smhextended', 'getSMHClasses'),
		'eval'						=> array('nospace'=>true, 'tl_class' => 'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['ftpPort'] = array
	(
		'label'						=> &$GLOBALS['TL_LANG']['tl_settings']['ftpPort'],
		'exclude'					=> true,
		'inputType'					=> 'text',
		'default'					=> '0',
		'eval'						=> array('rgxp' => 'decimal', 'nospace'=>true, 'tl_class' => 'w50')
);

class tl_settings_smhextended extends Backend
{
	public function getSMHClasses()
	{
		$classes=array('' => $GLOBALS['TL_LANG']['smhextended']['FTP']);
		foreach($GLOBALS['TL_SMH'] as $k=>$v)
		{
			$classes[$v] = $GLOBALS['TL_LANG']['smhextended'][$k];
		}
		return $classes;
	}
}

?>