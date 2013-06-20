<?php
/**
 * @package   System Plugin - automatic Less compiler - for Joomla 2.5 and 3.x
 * @version   0.7.1 Beta
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
			if ((bool) $this->params->get('clientside_enable', 0))
			{
				$this->clientsideLess();
			}
			else
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

	/**
	 * Configure and add Client-side Less library
	 * @author piotr-cz
	 * @return  void
	 *
	 * @see     LESS: Ussage  http://lesscss.org/#usage
	 */
	function clientsideLess()
	{
		// Initialise variables
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();


		// Early exit
		if ($doc->getType() !== 'html')
		{
			return;
		}

		// Get asset paths
		$templateRel = 'templates/' . $doc->template . '/';
		$templateUri = JUri::base() . $templateRel;


		// Determine which param to use (admin/ site)
		$mode = $this->params->get('mode', 0);
		$lessKey = 'lessfile';
		$cssKey = 'cssfile';

		if ($app->isAdmin() && ($mode == 1 || $mode == 2))
		{
			$lessKey = 'admin_' . $lessKey;
			$cssKey = 'admin_' . $cssKey;
		}


		// Get template css filenames
		$lessUri = $templateRel . $this->params->get($lessKey, 'less/template.less');
		$cssUri = $templateRel . $this->params->get($cssKey, 'css/template.css');


		// Add less file to document
		$doc->addHeadLink($lessUri, 'stylesheet/less', 'rel', array('type' => 'text/css'));

		/*
		 * Configure Less options
		 *  async			: false,
		 *  fileAsync		: false,
		 *  poll			: 1500,
		 *  relativeUrls	: false,
		 *  rootpath		: $templateUrl
		 */
		$options = array(
			'env' => 'development',
			'dumpLineNumbers' => 'mediaquery', // default: 'comments'
		);

		$doc->addScriptDeclaration('
				// Less options
				var less = ' . json_encode($options, JSON_FORCE_OBJECT | (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false)) . ';
		');


		// Load less.js (pick latest version in media folder)
		// Joomla adds JS code after libraries in head. We need it other way around
		$mediaJsDestination = '/media/plg_less/js/';
		$mediaPath = JPATH_SITE . $mediaJsDestination;
		$mediaUri = JUri::root(true) . $mediaJsDestination;

		$lessVersions = glob($mediaPath . 'less-*.js');

		if (!empty($lessVersions))
		{
			rsort($lessVersions);

			// Load at the end of head
			$doc->addCustomTag('<script src="' . $mediaUri . basename($lessVersions[0]) . '" type="text/javascript"></script>');

			// Load after options (experimental, problem with XHTML)
			/*
				$doc->addScriptDeclaration('
						// Less library
						document.write( unescape( \'%3Cscript src="' . $mediaUri . basename($lessVersions[0]) . '" type="text/javascript"%3E%3C/script%3E\' ) );
				');
			*/
		}


		/*
		 * Remove template.css from document head
		 *
		 * Note:  css file must be added either using `JFactory::getDocument->addStylesheet($cssFile)` or `JHtml::_('stylesheet', $cssFile)`
		 * Note:  Cannot rely on removing stylesheet using JDocumentHTML methods.
		 * Note:  template.css may be added to $doc['stylesheets'] using following keys:
		 *	- relative						: `templates/...`
		 *	- semi		JUri::base(true)	: `/[path-to-root]/templates/...`
		 * 	- absolute 	JUri::base()		: `http://[host]/[path-to-root]/templates/...`
		 *	- or outside $doc->_styleSheets
		 */
		$keys = array($cssUri, JUri::base() . $cssUri, JUri::base(false) . $cssUri);

		foreach ($keys as $key)
		{
			// Note: doesn't find non-cached links (ie. template.css?123);
			if (!isset($doc->_styleSheets[$key]))
			{
				continue;
			}

			unset($doc->_styleSheets[$key]);
			return;
		}

		// Didn't find css file, register event to remove from template html body.
		$app->registerEvent('onAfterRender', array($this, 'removeCss'));

		return;
	}

	/**
	 * Remove template.css from document html
	 * @author piotr-cz
	 *
	 * @return  void
	 */
	public function removeCss()
	{
		// TODO: doesn't work for admin yet
		// TODO: no path, open for bugs when more files of same name are present (ie. /css/system/template.css)
		return;

		$cssUri = $this->params->get('cssfile');
		$body = JResponse::getBody();

		$replaced = preg_replace('~(\r?\n.*<link .*/' . $cssUri . '".* />)~', '', $body);

		if ($replaced)
		{
			JResponse::setBody($replaced);
		}

		return;
	}
}