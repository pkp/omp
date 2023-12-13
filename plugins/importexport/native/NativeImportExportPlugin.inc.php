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

import('lib.pkp.classes.plugins.ImportExportPlugin');

class NativeImportExportPlugin extends ImportExportPlugin {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return $success;
		if ($success && $this->getEnabled()) {
			$this->addLocaleData();
			$this->import('NativeImportExportDeployment');
		}
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'NativeImportExportPlugin';
	}

	/**
	 * Get the display name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.importexport.native.displayName');
	}

	/**
	 * Get the display description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.importexport.native.description');
	}

	/**
	 * @copydoc ImportExportPlugin::getPluginSettingsPrefix()
	 */
	function getPluginSettingsPrefix() {
		return 'native';
	}

	/**
	 * Display the plugin.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function display($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$context = $request->getContext();

		parent::display($args, $request);

		$templateMgr->assign('plugin', $this);

		switch (array_shift($args)) {
			case 'index':
			case '':
				$apiUrl = $request->getDispatcher()->url($request, ROUTE_API, $context->getPath(), 'submissions');
				$submissionsListPanel = new \APP\components\listPanels\SubmissionsListPanel(
					'submissions',
					__('common.publications'),
					[
						'apiUrl' => $apiUrl,
						'count' => 100,
						'getParams' => new stdClass(),
						'lazyLoad' => true,
					]
				);
				$submissionsConfig = $submissionsListPanel->getConfig();
				$submissionsConfig['addUrl'] = '';
				$submissionsConfig['filters'] = array_slice($submissionsConfig['filters'], 1);
				$templateMgr->setState([
					'components' => [
						'submissions' => $submissionsConfig,
					],
				]);
				$templateMgr->assign([
					'pageComponent' => 'ImportExportPage',
				]);
				$templateMgr->display($this->getTemplateResource('index.tpl'));
				break;
			case 'uploadImportXML':
				$user = $request->getUser();
				import('lib.pkp.classes.file.TemporaryFileManager');
				$temporaryFileManager = new TemporaryFileManager();
				$temporaryFile = $temporaryFileManager->handleUpload('uploadedFile', $user->getId());
				if ($temporaryFile) {
					$json = new JSONMessage(true);
					$json->setAdditionalAttributes(array(
						'temporaryFileId' => $temporaryFile->getId()
					));
				} else {
					$json = new JSONMessage(false, __('common.uploadFailed'));
				}
				header('Content-Type: application/json');
				return $json->getString();
			case 'importBounce':
				if (!$request->checkCSRF()) throw new Exception('CSRF mismatch!');
				$json = new JSONMessage(true);
				$json->setEvent('addTab', array(
					'title' => __('plugins.importexport.native.results'),
					'url' => $request->url(null, null, null, array('plugin', $this->getName(), 'import'), array('temporaryFileId' => $request->getUserVar('temporaryFileId'), 'csrfToken' => $request->getSession()->getCSRFToken())),
				));
				header('Content-Type: application/json');
				return $json->getString();
			case 'import':
				if (!$request->checkCSRF()) throw new Exception('CSRF mismatch!');
				AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);
				$temporaryFileId = $request->getUserVar('temporaryFileId');
				$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO'); /* @var $temporaryFileDao TemporaryFileDAO */
				$user = $request->getUser();
				$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $user->getId());
				if (!$temporaryFile) {
					$json = new JSONMessage(true, __('plugins.inportexport.native.uploadFile'));
					header('Content-Type: application/json');
					return $json->getString();
				}
				$temporaryFilePath = $temporaryFile->getFilePath();

				$deployment = new NativeImportExportDeployment($context, $user);

				libxml_use_internal_errors(true);
				$submissions = $this->importSubmissions(file_get_contents($temporaryFilePath), $deployment);
				$templateMgr->assign('submissions', $submissions);
				$validationErrors = array_filter(libxml_get_errors(), function($a) {
					return $a->level == LIBXML_ERR_ERROR ||  $a->level == LIBXML_ERR_FATAL;
				});
				$templateMgr->assign('validationErrors', $validationErrors);
				libxml_clear_errors();

				// Are there any submissions import errors?
				$submissionsErrors = $deployment->getProcessedObjectsErrors(ASSOC_TYPE_SUBMISSION);
				if (!empty($submissionsErrors)) {
					$templateMgr->assign('submissionsErrors', $submissionsErrors);
				}
				// Are there any submissions import warnings?
				$submissionsWarnings = $deployment->getProcessedObjectsWarnings(ASSOC_TYPE_SUBMISSION);
				if (!empty($submissionsWarnings)) {
					$templateMgr->assign('submissionsWarnings', $submissionsWarnings);
				}
				// If there are any submissions or validation errors
				// delete imported submissions.
				if (!empty($submissionsErrors) || !empty($validationErrors)) {
					// remove all imported submissions
					$deployment->removeImportedObjects(ASSOC_TYPE_SUBMISSION);
				}
				// Display the results
				$json = new JSONMessage(true, $templateMgr->fetch($this->getTemplateResource('results.tpl')));
				header('Content-Type: application/json');
				return $json->getString();
			case 'export':
				$exportXml = $this->exportSubmissions(
					(array) $request->getUserVar('selectedSubmissions'),
					$request->getContext(),
					$request->getUser()
				);
				import('lib.pkp.classes.file.FileManager');
				$fileManager = new FileManager();
				$exportFileName = $this->getExportFileName($this->getExportPath(), 'monographs', $context, '.xml');
				$fileManager->writeFile($exportFileName, $exportXml);
				$fileManager->downloadByPath($exportFileName);
				$fileManager->deleteByPath($exportFileName);
				break;
			default:
				$dispatcher = $request->getDispatcher();
				$dispatcher->handle404();
		}
	}

	/**
	 * Get the XML for a set of submissions.
	 * @param $submissionIds array Array of submission IDs
	 * @param $context Context
	 * @param $user User
	 * @param $opts array
	 * @return string XML contents representing the supplied submission IDs.
	 */
	function exportSubmissions($submissionIds, $context, $user, $opts = array()) {
		$submissionDao = DAORegistry::getDAO('SubmissionDAO'); /* @var $submissionDao SubmissionDAO */
		$filterDao = DAORegistry::getDAO('FilterDAO'); /* @var $filterDao FilterDAO */
		$nativeExportFilters = $filterDao->getObjectsByGroup('monograph=>native-xml');
		assert(count($nativeExportFilters) == 1); // Assert only a single serialization filter
		$exportFilter = array_shift($nativeExportFilters);
		$exportFilter->setDeployment(new NativeImportExportDeployment($context, $user));
		$submissions = array();
		foreach ($submissionIds as $submissionId) {
			$submission = $submissionDao->getById($submissionId, $context->getId());
			if ($submission) $submissions[] = $submission;
		}
		libxml_use_internal_errors(true);
		$exportFilter->setOpts($opts);
		$submissionXml = $exportFilter->execute($submissions, true);
		$xml = $submissionXml->saveXml();
		$errors = array_filter(libxml_get_errors(), function($a) {
			return $a->level == LIBXML_ERR_ERROR || $a->level == LIBXML_ERR_FATAL;
		});
		if (!empty($errors)) {
			$this->displayXMLValidationErrors($errors, $xml);
		}
		return $xml;
	}

	/**
	 * Get the XML for a set of submissions.
	 * @param $importXml string XML contents to import
	 * @param $deployment PKPImportExportDeployment
	 * @return array Set of imported submissions
	 */
	function importSubmissions($importXml, $deployment) {
		$filterDao = DAORegistry::getDAO('FilterDAO'); /* @var $filterDao FilterDAO */
		$nativeImportFilters = $filterDao->getObjectsByGroup('native-xml=>monograph');
		assert(count($nativeImportFilters) == 1); // Assert only a single unserialization filter
		$importFilter = array_shift($nativeImportFilters);
		$importFilter->setDeployment($deployment);
		return $importFilter->execute($importXml);
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
						echo __('plugins.importexport.common.cliError') . "\n";
						echo __('plugins.importexport.native.error.unknownUser', array('userName' => $userName)) . "\n\n";
					}
					$this->usage($scriptName);
					return;
				}

				if (!file_exists($xmlFile)) {
					echo __('plugins.importexport.common.cliError') . "\n";
					echo __('plugins.importexport.common.export.error.inputFileNotReadable', array('param' => $xmlFile)) . "\n\n";
					$this->usage($scriptName);
					return;
				}

				$request = Application::get()->getRequest();
				// Set global user
				if (!$request->getUser()) {
					Registry::set('user', $user);
				}
				// Set global context
				if (!$request->getContext()) {
					HookRegistry::register('Router::getRequestedContextPaths', function (string $hook, array $args) use ($press): bool {
						$args[0] = [$press->getPath()];
						return false;
					});
					$router = new PageRouter();
					$router->setApplication(Application::get());
					$request->setRouter($router);
				}

				$xmlString = file_get_contents($xmlFile);
				$document = new DOMDocument('1.0', 'utf-8');
				$document->loadXml($xmlString);
				$deployment = new NativeImportExportDeployment($press, $user);
				$deployment->setImportPath(dirname($xmlFile));
				$content = $this->importSubmissions($xmlString, $deployment);
				$validationErrors = array_filter(libxml_get_errors(), function($a) {
					return $a->level == LIBXML_ERR_ERROR || $a->level == LIBXML_ERR_FATAL;
				});

				// Are there any import warnings? Display them.
				$errorTypes = array(
					ASSOC_TYPE_SUBMISSION => 'submission.submission',
					ASSOC_TYPE_SECTION => 'section.section',
				);
				foreach ($errorTypes as $assocType => $localeKey) {
					$foundWarnings = $deployment->getProcessedObjectsWarnings($assocType);
					if (!empty($foundWarnings)) {
						echo __('plugins.importexport.common.warningsEncountered') . "\n";
						$i = 0;
						foreach ($foundWarnings as $foundWarningMessages) {
							if (count($foundWarningMessages) > 0) {
								echo ++$i . '.' . __($localeKey) . "\n";
								foreach ($foundWarningMessages as $foundWarningMessage) {
									echo '- ' . $foundWarningMessage . "\n";
								}
							}
						}
					}
				}

				// Are there any import errors? Display them.
				$foundErrors = false;
				foreach ($errorTypes as $assocType => $localeKey) {
					$currentErrors = $deployment->getProcessedObjectsErrors($assocType);
					if (!empty($currentErrors)) {
						echo __('plugins.importexport.common.errorsOccured') . "\n";
						$i = 0;
						foreach ($currentErrors as $currentErrorMessages) {
							if (count($currentErrorMessages) > 0) {
								echo ++$i . '.' . __($localeKey) . "\n";
								foreach ($currentErrorMessages as $currentErrorMessage) {
									echo '- ' . $currentErrorMessage . "\n";
								}
							}
						}
						$foundErrors = true;
					}
				}
				// If there are any data or validation errors
				// delete imported objects.
				if ($foundErrors || !empty($validationErrors)) {
					// remove all imported issues and submissions
					foreach (array_keys($errorTypes) as $assocType) {
						$deployment->removeImportedObjects($assocType);
					}
					echo __('plugins.importexport.common.validationErrors') . "\n";
					$i = 0;
					foreach ($validationErrors as $validationError) {
						echo ++$i . '. Line: ' . $validationError->line . ' Column: ' . $validationError->column . ' > ' . $validationError->message . "\n";
					}
				}
				return;
			case 'export':
				$outputDir = dirname($xmlFile);
				if (!is_writable($outputDir) || (file_exists($xmlFile) && !is_writable($xmlFile))) {
					echo __('plugins.importexport.common.cliError') . "\n";
					echo __('plugins.importexport.common.export.error.outputFileNotWritable', array('param' => $xmlFile)) . "\n\n";
					$this->usage($scriptName);
					return;
				}
				if ($xmlFile != '') switch (array_shift($args)) {
					case 'monograph':
					case 'monographs':
						file_put_contents($xmlFile, $this->exportSubmissions(
							$args,
							$press,
							null,
							$opts
						));
						return;
				}
				break;
		}
		$this->usage($scriptName);
	}

	/**
	 * @copydoc ImportExportPlugin::usage
	 */
	function usage($scriptName) {
		fatalError('Not implemented.');
	}

	/**
	 * Pull out getopt style long options.
	 * @param &$args array
	 * #param $optCodes array
	 */
	function parseOpts(&$args, $optCodes) {
		$newArgs = [];
		$opts = [];
		$sticky = null;
		foreach ($args as $arg) {
			if ($sticky) {
				$opts[$sticky] = $arg;
				$sticky = null;
				continue;
			}
			if (substr($arg, 0, 2) != '--') {
				$newArgs[] = $arg;
				continue;
			}
			$opt = substr($arg, 2);
			if (in_array($opt, $optCodes)) {
				$opts[$opt] = true;
				continue;
			}
			if (in_array($opt . ":", $optCodes)) {
				$sticky = $opt;
				continue;
			}
		}
		$args = $newArgs;
		return $opts;
	}
}
