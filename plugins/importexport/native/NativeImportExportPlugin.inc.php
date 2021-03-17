<?php

/**
 * @file plugins/importexport/native/NativeImportExportPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeImportExportPlugin
 * @ingroup plugins_importexport_native
 *
 * @brief Native XML import/export plugin
 */

import('lib.pkp.plugins.importexport.native.PKPNativeImportExportPlugin');

class NativeImportExportPlugin extends PKPNativeImportExportPlugin {
	/**
	 * Display the plugin.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function display($args, $request) {
		$context = $request->getContext();
		$user = $request->getUser();
		$deployment = new NativeImportExportDeployment($context, $user);

		$this->setDeployment($deployment);

		$templateMgr = TemplateManager::getManager($request);

		list ($returnString, $managed) = parent::display($args, $request);

		if ($managed) {
			if ($returnString) {
				return $returnString;
			}

			return;
		}

		$templateMgr->assign('plugin', $this);

		switch (array_shift($args)) {
			default:
				$dispatcher = $request->getDispatcher();
				$dispatcher->handle404();
		}
	}

	/**
	 * Get the XML for a set of submissions.
	 * @param $submissionIds array Array of submission IDs
	 * @param $context Context
	 * @param $user User|null
	 * @param $opts array
	 * @return string XML contents representing the supplied submission IDs.
	 */
	function exportSubmissions($submissionIds, $context, $user, $opts = array()) {
		$deployment = new NativeImportExportDeployment($context, $user);
		$this->getExportSubmissionsDeployment($submissionIds, $deployment, $opts);

		return $this->exportResultXML($deployment);
	}

	function getImportFilter($xmlFile) {
		$filter = 'native-xml=>monograph';

		$xmlString = file_get_contents($xmlFile);

		return array($filter, $xmlString);
	}

	function getExportFilter($exportType) {
		$filter = false;
		if ($exportType == 'exportSubmissions') {
			$filter = 'monograph=>native-xml';
		}

		return $filter;
	}

	/**
	 * @copydoc ImportExportPlugin::executeCLI
	 */
	function executeCLI($scriptName, &$args) {
		$opts = $this->parseOpts($args, ['no-embed', 'use-file-urls']);
		$command = array_shift($args);
		$xmlFile = array_shift($args);
		$pressPath = array_shift($args);

		AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_PKP_SUBMISSION);
		$pressDao = DAORegistry::getDAO('PressDAO');
		$userDao = DAORegistry::getDAO('UserDAO');
		$press = $pressDao->getByPath($pressPath);

		if (!$press) {
			if ($pressPath != '') {
				echo __('plugins.importexport.common.cliError') . "\n";
				echo __('plugins.importexport.common.error.unknownPress', array('pressPath' => $pressPath)) . "\n\n";
			}
			$this->usage($scriptName);
			return;
		}

		if ($xmlFile && $this->isRelativePath($xmlFile)) {
			$xmlFile = PWD . '/' . $xmlFile;
		}

		switch ($command) {
			case 'import':
				$userName = array_shift($args);
				$user = $userDao->getByUsername($userName);

				if (!$user) {
					if ($userName != '') {
						$this->echoCLIError(__('plugins.importexport.native.error.unknownUser', array('userName' => $userName)));
					}
					$this->usage($scriptName);
					return;
				}

				if (!file_exists($xmlFile)) {
					$this->echoCLIError(__('plugins.importexport.common.export.error.inputFileNotReadable', array('param' => $xmlFile)));

					$this->usage($scriptName);
					return;
				}

				list ($filter, $xmlString) = $this->getImportFilter($xmlFile);

				$deployment = new NativeImportExportDeployment($press, $user);
				$deployment->setImportPath(dirname($xmlFile));

				$deployment->import($filter, $xmlString);

				$this->getCLIImportResult($deployment);
				$this->getCLIProblems($deployment);
				return;

			case 'export':
				$deployment = new NativeImportExportDeployment($journal, null);

				$outputDir = dirname($xmlFile);
				if (!is_writable($outputDir) || (file_exists($xmlFile) && !is_writable($xmlFile))) {
					$this->echoCLIError(__('plugins.importexport.common.export.error.outputFileNotWritable', array('param' => $xmlFile)));

					$this->usage($scriptName);
					return;
				}
				if ($xmlFile != '') switch (array_shift($args)) {
					case 'monograph':
					case 'monographs':
						$this->getExportSubmissionsDeployment(
							$args,
							$deployment,
							$opts
						);

						$this->getCLIExportResult($deployment, $xmlFile);
						$this->getCLIProblems($deployment);
						return;
				}
				break;
		}
		$this->usage($scriptName);
	}
}
