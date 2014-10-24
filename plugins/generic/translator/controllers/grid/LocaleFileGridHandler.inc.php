<?php

/**
 * @file controllers/grid/LocaleFileGridHandler.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LocaleFileGridHandler
 * @ingroup controllers_grid_locale
 *
 * @brief Handle grid requests for conventional locale files.
 */

import('plugins.generic.translator.controllers.grid.BaseLocaleFileGridHandler');

class LocaleFileGridHandler extends BaseLocaleFileGridHandler {
	/** @var EditableLocaleFile File. NOTE: This is only used in certain cases and may not be available */
	var $file;

	/**
	 * Constructor
	 */
	function LocaleFileGridHandler() {
		parent::BaseLocaleFileGridHandler();
	}


	//
	// Overridden template methods
	//
	/**
	 * @copydoc Gridhandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request);

		// Set the grid details.
		$this->setTitle('plugins.generic.translator.localeFiles');
		$this->setInstructions('plugins.generic.translator.localeFileDescription');

		// Fetch and prepare the grid data.
		$fileList = TranslatorAction::getLocaleFiles($this->locale);
		sort($fileList);

		$fileData = array();
		foreach ($fileList as $file) {
			$referenceData = LocaleFile::load(str_replace($this->locale, MASTER_LOCALE, $file));
			$referenceCount = count($referenceData);

			if ($exists = file_exists($file)) {
				$localeData = LocaleFile::load($file);
				$completeCount = $this->_getTranslatedCount($referenceData, $localeData);
			}

			$fileData[] = array(
				'filename' => $file,
				'status' => $exists?
					($completeCount == $referenceCount ?
						__('plugins.generic.translator.localeFile.complete', array('reference' => $referenceCount)):
						__('plugins.generic.translator.localeFile.incompleteCount', array('complete' => $completeCount, 'reference' => $referenceCount, 'percent' => (int) ($completeCount*100/$referenceCount)))
					):
					__('plugins.generic.translator.localeFile.doesNotExist', array('reference' => $referenceCount))
			);
		}
		$this->setGridDataElements($fileData);
	}

	//
	// Public Grid Actions
	//
	/**
	 * Display the grid's containing page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function edit($args, $request) {
		$filename = $this->_getFilename($request);

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('locale', $this->locale);
		$templateMgr->assign('filename', $filename);
		return $templateMgr->fetchJson(self::$plugin->getTemplatePath() . 'localeFile.tpl');
	}

	/**
	 * Display the grid's containing page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function save($args, $request) {
		$filename = $this->_getFilename($request);
		$notificationManager = new NotificationManager();
		$user = $request->getUser();

		// Use the EditableLocaleFile class to handle changes.
		import('lib.pkp.classes.file.EditableLocaleFile');
		$this->file = new EditableLocaleFile($this->locale, $filename);

		// Delegate processing to the listbuilder handler. This will invoke the callbacks below.
		self::$plugin->import('controllers.listbuilder.LocaleFileListbuilderHandler');
		if (LocaleFileListbuilderHandler::unpack($request, $request->getUserVar('localeKeys'))) {
			if ($this->file->write()) {
				$notificationManager->createTrivialNotification($user->getId());
			} else {
				// Could not write the file
				$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_ERROR, array('contents' => __('plugins.generic.translator.couldNotWriteFile', array('filename' => $filename))));
			}
		} else {
			// Some kind of error occurred (probably garbled formatting)
			$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_ERROR, array('contents' => __('plugins.generic.translator.errorEditingFile', array('filename' => $filename))));
		}
		$message = new JSONMessage(true);
		return $message->getString();
	}

	/**
	 * @copydoc ListbuilderHandler::insertEntry()
	 */
	function insertEntry($request, $newRowId) {
		if ($newRowId['value'][$this->locale] === '') return true; // Do not insert blanks
		return $this->file->insert($newRowId['key'], $newRowId['value'][$this->locale]);
	}

	/**
	 * @copydoc ListbuilderHandler::updateEntry()
	 */
	function updateEntry($request, $rowId, $newRowId) {
		if ($newRowId['value'][$this->locale] === '') return true; // Do not insert blanks
		if (!$this->file->update($newRowId['key'], $newRowId['value'][$this->locale])) {
			return $this->insertEntry($request, $newRowId);
		}
		return true;
	}

	/**
	 * @copydoc ListbuilderHandler::deleteEntry()
	 */
	function deleteEntry($request, $rowId) {
		return $this->file->delete($rowId);
	}

	/**
	 * Get the (validated) filename for the current request.
	 * @param $request PKPRequest
	 * @return string Filename
	 */
	protected function _getFilename($request) {
		$filename = $request->getUserVar('filename');
		if (!in_array($filename, TranslatorAction::getLocaleFiles($this->locale))) {
			fatalError('Invalid locale file specified!');
		}
		return $filename;
	}

	protected function _getTranslatedCount($referenceLocaleData, $localeData) {
		$completeCount = 0;
		foreach ($referenceLocaleData as $key => $value) {
			if (!isset($localeData[$key])) continue; // Not translated
			if (0 != count(array_diff(
				PKPLocale::getParameterNames($value),
				PKPLocale::getParameterNames($localeData[$key])
			))) {
				continue; // Parameters differ
			}
			$completeCount++;
		}
		return $completeCount;
	}
}

?>
