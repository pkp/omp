<?php

/**
 * @file tools/fbvVisualResults.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class fbvVisualResults
 * @ingroup tools
 *
 * @brief Tool that generates a page containing the visual presentation of forms coded with the form builder vocabulary.
 * 	Use this tool to quickly inspect the results of an fbv-coded test form within the context of the application.
 * @see lib/pkp/tests/ui/fbv/*
 */

define('INDEX_FILE_LOCATION', dirname(dirname(__FILE__)) . '/index.php');
chdir(dirname(INDEX_FILE_LOCATION)); /* Change to base directory */
require('lib/pkp/includes/bootstrap.inc.php');

$application = Application::getApplication();
$request = $application->getRequest();

// FIXME: Write and use a CLIRouter here (see classdoc)
import('classes.core.PageRouter');
$router = new PageRouter();
$router->setApplication($application);
$request->setRouter($router);

import('classes.template.TemplateManager');
import('lib.pkp.classes.form.Form');

// Extend the TemplateManager class to:
// - access test templates
// - adjust $baseUrl to obtain proper paths to application js+css
// - prevent the creation of urls from within templates
// - modify the initialization procedure,
//      allowing Form::display() to use FBVTemplateManager
class FBVTemplateManager extends TemplateManager {

	function __construct() {
		parent::__construct();

		$this->caching = 0;

		// look for templates in the test directory
		$baseDir = Core::getBaseDir();
		$test_template_dir = $baseDir . DIRECTORY_SEPARATOR . PKP_LIB_PATH . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'fbv';
		$this->template_dir[] = $test_template_dir;

		// $baseUrl has to be reset to properly reference the javascript files and stylesheets
		$baseUrl = '';
		$uriComponents = explode('/', $_SERVER['REQUEST_URI']);

		for ($i=0, $count=count($uriComponents); $i<$count; $i++) {
			if ($uriComponents[$i] == 'tools' && $uriComponents[$i+1] == 'fbvVisualResults.php') break;
			else if (empty($uriComponents[$i])) continue;
			else $baseUrl .= '/' . $uriComponents[$i];
		}

		$this->assign('baseUrl', $baseUrl);
	}

	/** see lib/pkp/classes/template/PKPTemplateManager.inc.php */
	function initialize() {
		$this->initialized = true;
	}

	/** see lib/pkp/classes/template/PKPTemplateManager.inc.php */
	function &getManager($request = null) {
		$instance =& Registry::get('templateManager', true, null);

		if ($instance === null) {
			$instance = new FBVTemplateManager($request);
		}
		return $instance;
	}

	/** see lib/pkp/classes/template/PKPTemplateManager.inc.php */
	function smartyUrl($params, &$smarty) {
		return null;
	}
}

// main class for this tool
class fbvVisualResults {
	// constructor: set FBVTemplateManager instance in the registry
	function __construct() {
		FBVTemplateManager::getManager();
	}

	// generate the results
	function execute() {
		if (isset($_GET['display'])) {
			switch ($_GET['display']) {
				case 'modal':
					$testForm = new Form('fbvTestForm.tpl');
					break;
				default:
					$testForm = new Form('fbvTestFormWrapper.tpl');
			}
		} else {
			$testForm = new Form('fbvTestFormWrapper.tpl');
		}

		$testForm->display();
	}

}

$tool = new fbvVisualResults();
$tool->execute();

?>
