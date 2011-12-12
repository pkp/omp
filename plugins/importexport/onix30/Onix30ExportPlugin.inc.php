<?php

/**
 * @file Onix30ExportPlugin.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Onix30ExportPlugin
 * @ingroup plugins_importexport_onix30
 *
 * @brief ONIX 3.0 XML export plugin for monographs
 */

// $Id$


import('classes.plugins.ImportExportPlugin');

import('lib.pkp.classes.xml.XMLCustomWriter');

class Onix30ExportPlugin extends ImportExportPlugin {
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'Onix30ExportPlugin';
	}

	function getDisplayName() {
		return __('plugins.importexport.onix30.displayName');
	}

	function getDescription() {
		return __('plugins.importexport.onix30.description');
	}

	function display(&$args) {
		$templateMgr =& TemplateManager::getManager();
		parent::display($args);
	}

	function exportMonograph(&$press, &$monograph, $outputFile = null) {
		$this->import('Onix30ExportDom');
		$doc =& XMLCustomWriter::createDocument();
		$monographNode =& Onix30ExportDom::generateMonographDom($doc, $press, $monograph);
		XMLCustomWriter::appendChild($doc, $monographNode);

		if (!empty($outputFile)) {
			if (($h = fopen($outputFile, 'wb'))===false) return false;
			fwrite($h, XMLCustomWriter::getXML($doc));
			fclose($h);
		} else {
			header("Content-Type: application/xml");
			header("Cache-Control: private");
			header("Content-Disposition: attachment; filename=\"onix30.xml\"");
			XMLCustomWriter::printXML($doc);
		}
		return true;
	}

	/**
	 * Execute export tasks using the command-line interface.
	 * @param $args Parameters to the plugin
	 */
	function executeCLI($scriptName, &$args) {
		$xmlFile = array_shift($args);
		$pressPath = array_shift($args);
		$monographId = array_shift($args);

		$pressDao =& DAORegistry::getDAO('PressDAO');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$press =& $pressDao->getPressByPath($pressPath);

		if (!$press) {
			if ($pressPath != '') {
				echo __('plugins.importexport.onix30.cliError') . "\n";
				echo __('plugins.importexport.onix30.error.unknownPress', array('pressPath' => $pressPath)) . "\n\n";
			}
			$this->usage($scriptName);
			return;
		}

		$monograph =& $monographDao->getById($monographId);

		if ($monograph == null) {
			echo __('plugins.importexport.onix30.cliError') . "\n";
			echo __('plugins.importexport.onix30.export.error.monographNotFound', array('monographId' => $monographId)) . "\n\n";
			return;
		}

		if (!$this->exportMonograph($press, $monograph, $xmlFile)) {
			echo __('plugins.importexport.onix30.cliError') . "\n";
			echo __('plugins.importexport.onix30.export.error.couldNotWrite', array('fileName' => $xmlFile)) . "\n\n";
		}
	}

	/**
	 * Display the command-line usage information
	 */
	function usage($scriptName) {
		echo __('plugins.importexport.onix30.cliUsage', array(
			'scriptName' => $scriptName,
			'pluginName' => $this->getName()
		)) . "\n";
	}
}

?>
