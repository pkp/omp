<?php

/**
 * @file plugins/importexport/onix30/Onix30ExportPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Onix30ExportPlugin
 * @ingroup plugins_importexport_onix30
 *
 * @brief ONIX 3.0 XML import/export plugin
 */

import('lib.pkp.classes.plugins.ImportExportPlugin');

class Onix30ExportPlugin extends ImportExportPlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return $success;
		if ($success && $this->getEnabled()) {
			$this->addLocaleData();
			$this->import('Onix30ExportDeployment');
		}
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

	/**
	 * Get the display name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.importexport.onix30.displayName');
	}

	/**
	 * Get the display description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.importexport.onix30.description');
	}

	/**
	 * @copydoc ImportExportPlugin::getPluginSettingsPrefix()
	 */
	function getPluginSettingsPrefix() {
		return 'onix30';
	}

	/**
	 * Display the plugin.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function display($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$context = $request->getContext();
		$user = $request->getUser();

		$exportFileDatePartFormat = 'Ymd-His';

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
			case 'exportSubmissionsBounce':
				if (!$request->checkCSRF()) throw new Exception('CSRF mismatch!');
				$json = new JSONMessage(true);
				$json->setEvent('addTab', array(
					'title' => __('plugins.importexport.native.results'),
					'url' => $request->url(null, null, null, array('plugin', $this->getName(), 'export'), array('selectedSubmissions' => $request->getUserVar('selectedSubmissions'), 'validation' => $request->getUserVar('validation'), 'csrfToken' => $request->getSession()->getCSRFToken())),
				));
				header('Content-Type: application/json');
				return $json->getString();
			case 'export':
				$onixDeployment = new Onix30ExportDeployment($context, $user);

				$noValidation = !$request->getUserVar('validation');

				$exportXml = $this->exportSubmissions(
					(array) $request->getUserVar('selectedSubmissions'),
					$context,
					$user,
					$onixDeployment,
					$noValidation
			);

				$problems = $onixDeployment->getWarningsAndErrors();
				$foundErrors = $onixDeployment->isProcessFailed();

				if ($exportXml) {
					$dateFilenamePart = new DateTime();
					$this->writeExportedFile($exportXml, $onixDeployment->getContext(), $dateFilenamePart);

					$templateMgr->assign('exportPath', $dateFilenamePart->format($exportFileDatePartFormat));
				}

				$templateMgr->assign('validationErrors', $onixDeployment->getXMLValidationErrors());

				$templateMgr->assign('errorsAndWarnings', $problems);
				$templateMgr->assign('errorsFound', $foundErrors);
				$templateMgr->assign('onixPlugin', $this);

				$json = new JSONMessage(true, $templateMgr->fetch($this->getTemplateResource('resultsExport.tpl')));
				header('Content-Type: application/json');
				return $json->getString();
			case 'downloadExportFile':
				if (!$request->checkCSRF()) throw new Exception('CSRF mismatch!');
				$downloadPath = $request->getUserVar('downloadFilePath');
				
				$date = DateTime::createFromFormat($exportFileDatePartFormat, $downloadPath);
				if (!$date) {
					$dispatcher = $request->getDispatcher();
					$dispatcher->handle404();
				} 

				$exportFileName = $this->getExportFileName($this->getExportPath(), 'monographs', $context, '.xml', $date);
				
				import('lib.pkp.classes.file.FileManager');
				$fileManager = new FileManager();

				if (!$fileManager->fileExists($exportFileName)) {
					$dispatcher = $request->getDispatcher();
					$dispatcher->handle404();
				} 

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
	 * @param $onixDeployment Onix30ExportDeployment
	 * @return string XML contents representing the supplied submission IDs.
	 */
	function exportSubmissions($submissionIds, $context, $user, $onixDeployment, $noValidation) {
		import('lib.pkp.classes.metadata.MetadataTypeDescription');

		$submissionDao = DAORegistry::getDAO('SubmissionDAO'); /* @var $submissionDao SubmissionDAO */
		$xml = '';
		$filterDao = DAORegistry::getDAO('FilterDAO'); /* @var $filterDao FilterDAO */
		$nativeExportFilters = $filterDao->getObjectsByGroup('monographs=>onix30-xml');
		assert(count($nativeExportFilters) == 1); // Assert only a single serialization filter
		$exportFilter = array_shift($nativeExportFilters);

		if ($noValidation) $exportFilter->setNoValidation($noValidation);

		$exportFilter->setDeployment($onixDeployment);
		$submissions = array();
		foreach ($submissionIds as $submissionId) {
			$submission = $submissionDao->getById($submissionId, $context->getId());
			if ($submission) $submissions[] = $submission;
		}

		libxml_use_internal_errors(true);

		$submissionXml = $exportFilter->execute($submissions);

		$onixDeployment->xmlValidationErrors = array_filter(libxml_get_errors(), function ($a) {
			return $a->level == LIBXML_ERR_ERROR || $a->level == LIBXML_ERR_FATAL;
		});

		libxml_clear_errors();

		if ($submissionXml) 
			$xml = $submissionXml->saveXml();
		else 
			$onixDeployment->addError(ASSOC_TYPE_ANY, 0, __('plugins.importexport.onix30.processFailed'));
	
		return $xml;
	}

	/**
	 * Create file given its name and content
	 *
	 * @param string $filename
	 * @param ?DateTime $fileContent
	 * @param Context $context
	 *
	 * @return string
	 */
	public function writeExportedFile($fileContent, $context, ?DateTime $dateFilenamePart = null) {
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();
		
		$exportFileName = $this->getExportFileName($this->getExportPath(), 'monographs', $context, '.xml', $dateFilenamePart);
		
		$fileManager->writeFile($exportFileName, $fileContent);

		return $exportFileName;
	}

	/**
	 * @copydoc ImportExportPlugin::executeCLI($scriptName, $args)
	 */
	function executeCLI($scriptName, &$args) {
		fatalError('Not implemented.');
	}

	/**
	 * @copydoc ImportExportPlugin::usage
	 */
	function usage($scriptName) {
		fatalError('Not implemented.');
	}

	public function getBounceTab($request, $title, $bounceUrl, $bounceParameterArray) {
		if (!$request->checkCSRF()) {
			throw new Exception('CSRF mismatch!');
		}
		$json = new JSONMessage(true);
		$json->setEvent('addTab', [
			'title' => $title,
			'url' => $request->url(
				null,
				null,
				null,
				['plugin', $this->getName(), $bounceUrl],
				array_merge($bounceParameterArray, ['csrfToken' => $request->getSession()->getCSRFToken()])
			),
		]);
		header('Content-Type: application/json');
		return $json->getString();
	}
}
