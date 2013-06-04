<?php
/**
 * @package   System Plugin - automatic Less compiler - for Joomla 2.5 and 3.x
 * @version   0.7.2 Beta
 * @author    Andreas Tasch
 * @copyright (C) 2012-2013 - Andreas Tasch
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/

// no direct access
defined('_JEXEC') or die();

if (!class_exists('lessc'))
{
	require_once('lessc.php');
}

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
		if ($app->isSite() && ($mode == 0 || $mode == 2))
		{
			$templatePath = JPATH_BASE . DIRECTORY_SEPARATOR . 'templates/' . $app->getTemplate() . DIRECTORY_SEPARATOR;

			//entrypoint for main .less file, default is less/template.less
			$lessFile = $templatePath . $this->params->get('lessfile', 'less/template.less');

			//destination .css file, default css/template.css
			$cssFile = $templatePath . $this->params->get('cssfile', 'css/template.css');

		}

		//execute backend
		if ($app->isAdmin() && ($mode == 1 || $mode == 2))
		{
			$templatePath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'templates/' . $app->getTemplate() . DIRECTORY_SEPARATOR;

			//entrypoint for main .less file, default is less/template.less
			$lessFile = $templatePath . $this->params->get('admin_lessfile', 'less/template.less');

			//destination .css file, default css/template.css
			$cssFile = $templatePath . $this->params->get('admin_cssfile', 'css/template.css');

		}

		//check if .less file exists and is readable
		if (is_readable($lessFile))
		{
			//initialse less compiler
			try
			{
				$this->autoCompileLess($lessFile, $cssFile);
			}
			catch (Exception $e)
			{
				echo "lessphp error: " . $e->getMessage();
			}
		}

		return false;
	}

	/**
	 * Checks if .less file has been updated and stores it in cache for quick comparison.
	 *
	 * This function is taken and modified from documentation of lessphp
	 *
	 * @param String $inputFile
	 * @param String $outputFile
	 */
	function autoCompileLess($inputFile, $outputFile)
	{
		// load config file
		$config = JFactory::getConfig();
		//path to temp folder
		$tmpPath = $config->get('tmp_path');
		//get Application
		$app = JFactory::getApplication();

		//load chached file
		$cacheFile = $tmpPath . DIRECTORY_SEPARATOR . $app->getTemplate() . "_" . basename($inputFile) . ".cache";

		if (file_exists($cacheFile))
		{
			$tmpCache = unserialize(file_get_contents($cacheFile));
			if ($tmpCache['root'] === $cacheFile)
			{
				$cache = $tmpCache;
			}
			else
			{
				$cache = $inputFile;
				unlink($cacheFile);
			}
		}
		else
		{
			$cache = $inputFile;
		}

		//instantiate less compiler
		$less = new lessc;

		//set less options
		//option: force recompilation regardless of change
		$force = (boolean) $this->params->get('less_force', 0);

		//option: preserve comments
		if ($this->params->get('less_comments', 0))
		{
			$less->setPreserveComments(true);
		}

		//option: compression
		if ($this->params->get('less_compress', 0))
		{
			$less->setFormatter("compressed");
		}
		else
		{
			$formatter = new lessc_formatter_classic;
			$formatter->disableSingle = true;
			$formatter->breakSelectors = true;
			$formatter->assignSeparator = ": ";
			$formatter->selectorSeparator = ",";
			$formatter->indentChar = "\t";
		}

		//compile cache file
		$newCache = $less->cachedCompile($cache, $force);

		if (!is_array($cache) || $newCache["updated"] > $cache["updated"])
		{
			file_put_contents($cacheFile, serialize($newCache));
			file_put_contents($outputFile, $newCache['compiled']);
		}
	}
}