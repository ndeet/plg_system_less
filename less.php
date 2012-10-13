<?php
/**
 * @package System Plugin - automatic Less compiler - for Joomla 2.5 and 3.0
 * @version 0.2 Beta
 * @author Andreas Tasch 
 * @copyright (C) 2012 - Andreas Tasch
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/

// no direct access
defined( '_JEXEC' ) or die();

require_once('lessc.php');

/**
 * Plugin checks and compiles updated .less files on page load. No need to manually compile your .less files again.
 * Less compiler lessphp; see http://leafo.net/lessphp/
 */
class plgSystemLess extends JPlugin
{
	/**
	 * Compile .less files on change
	 */
	function onBeforeRender()
	{
		$app = JFactory::getApplication();
		
		//path to less file
		$lessFile = '';
		
		// 0 = frontend only
		// 1 = backend only
		// 2 = front + backend
		$mode = $this->params->get('mode', 0);
		
		//only execute frontend
		if($app->isSite() && ($mode == 0 || $mode == 2))
		{	
			$templatePath = JPATH_BASE . DIRECTORY_SEPARATOR . 'templates/' . $app->getTemplate() . DIRECTORY_SEPARATOR;
			
			//entrypoint for main .less file, default is less/template.less
			$lessFile = $templatePath . $this->params->get('lessfile','less/template.less');
			
			//destination .css file, default css/template.css
			$cssFile = $templatePath . $this->params->get('cssfile','css/template.css');
			
			//force recompilation regardless of change 
			$force = (boolean) $this->params->get('less_force', 0);
		}
		
		//execute backend
		if($app->isAdmin() && ($mode == 1 || $mode == 2)) 
		{
			$templatePath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'templates/' . $app->getTemplate() . DIRECTORY_SEPARATOR;
			
			//entrypoint for main .less file, default is less/template.less
			$lessFile = $templatePath . $this->params->get('admin_lessfile','less/template.less');
			
			//destination .css file, default css/template.css
			$cssFile = $templatePath . $this->params->get('admin_cssfile','css/template.css');
			
			//force recompilation regardless of change 
			$force = (boolean) $this->params->get('less_force', 0);
		}
		
		//check if .less file exists and is readable
		if(is_readable($lessFile))
		{
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