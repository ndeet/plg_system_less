<?php
/**
 * @package System Plugin - automatic Less compiler - for Joomla 2.5 and 3.0
 * @version 0.1 Alpha
 * @author Andreas Tasch 
 * @copyright (C) 2012 - Andreas Tasch
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

require_once('lessc.php');

/**
 * Plugin checks and compiles updated .less files on page load. No need to manually compile your .less files again.
 * Less compiler lessphp; see http://leafo.net/lessphp/
 */
class plgSystemLess extends JPlugin
{
	/**
	 * Constructor.
	 *
	 * @access protected
	 * @param object $subject The object to observe
	 * @param array   $config  An array that holds the plugin configuration
	 * @since 1.0
	 */
	public function __construct( &$subject, $config )
	{
		parent::__construct( $subject, $config );
	
		//TODO check config params 
	}

	/**
	 * Do something onAfterDispatch
	 */
	function onAfterDispatch()
	{
	}

	/**
	 * Check if 
	 */
	function onBeforeRender()
	{
		//check if less folder exists
		$app = JFactory::getApplication();
		$templateURI = JURI::base() . 'templates/' . $app->getTemplate();
		
		$templatePath = JPATH_BASE . DIRECTORY_SEPARATOR . 'templates/' . $app->getTemplate() . DIRECTORY_SEPARATOR;
		
		//TODO: make this configurable; default is template.less
		$lessFile = $templatePath . "less/template.less";
		//force recompilation regardless of change 
		//TODO: get from config
		$force = false;
		
		//TODO: only if plugin check option is set to yes (config param)
		if(JFile::exists($lessFile))
		{
			// css output file
			$cssFile = $templatePath . "css/template.css";
			
			//initialse less compiler
			try {
				
				$this->autoCompileLess($lessFile, $cssFile, $force);
			} 
			catch(Exception $e) 
			{
				echo "lessphp error: " . $e->getMessage();
			}
		
		}
		return false;
	}
	
	/**
	 * Checks if .less file has been updated and stores it in cache for quick comparison. 
	 * 
	 * Use $force=true to force compiling regardless of cache.
	 * 
	 * This function is taken and modified from documentation of lessphp
	 * 
	 * 
	 * @param String $inputFile
	 * @param String $outputFile
	 * @param boolean $force
	 */
	function autoCompileLess($inputFile, $outputFile, $force) {
		// load config file
		$configFile = JPATH_BASE . DIRECTORY_SEPARATOR . 'configuration.php';
		$config = JFactory::getConfig($configFile);
		$tmpPath = $config->get('tmp_path');
		
		//load chached file
		$cacheFile = $tmpPath . DIRECTORY_SEPARATOR . basename($inputFile) . ".cache";
	
		if (file_exists($cacheFile)) {
			$cache = unserialize(file_get_contents($cacheFile));
		} else {
			$cache = $inputFile;
		}
	
		$less = new lessc;
		$newCache = $less->cachedCompile($cache, $force);
	
		if (!is_array($cache) || $newCache["updated"] > $cache["updated"]) {
			file_put_contents($cacheFile, serialize($newCache));
			file_put_contents($outputFile, $newCache['compiled']);
		}
	}
}