<?php

/**
 * @file controllers/grid/MiscTranslationFileGridHandler.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MiscTranslationFileGridHandler
 * @ingroup controllers_grid_locale
 *
 * @brief Handle static pages grid requests.
 */

import('plugins.generic.translator.controllers.grid.BaseLocaleFileGridHandler');

class MiscTranslationFileGridHandler extends BaseLocaleFileGridHandler {
	/** @var EditableFile File. NOTE: This is only used in certain cases and may not be available */
	var $file;

	/**
	 * Constructor
	 */
	function MiscTranslationFileGridHandler() {
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
		$this->setTitle('plugins.generic.translator.miscFiles');
		$this->setInstructions('plugins.generic.translator.miscFileDescription');
		$files = TranslatorAction::getMiscLocaleFiles($this->locale);
		sort($files);
		$this->setGridDataElements($files);
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Display the grid's containing page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function edit($args, $request) {
		$filename = $this->_getFilename($request);
		$referenceFilename = TranslatorAction::determineReferenceFilename($this->locale, $filename);

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('locale', $this->locale);
		$templateMgr->assign('filename', $filename);

		$templateMgr->assign('referenceContents', file_get_contents($referenceFilename));
		$templateMgr->assign('fileContents', file_exists($filename)?file_get_contents($filename):'');
		return $templateMgr->fetchJson(self::$plugin->getTemplatePath() . 'editMiscFile.tpl');
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

		$contents = $this->correctCr($request->getUserVar('fileContents'));

		if (file_put_contents($filename, $contents)) {
			$notificationManager->createTrivialNotification($user->getId());
		} else {
			// Could not write the file
			$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_ERROR, array('contents' => __('plugins.generic.translator.couldNotWriteFile', array('filename' => $filename))));
		}
		$message = new JSONMessage(true);
		return $message->getString();
	}

	/**
	 * Get the (validated) filename for the current request.
	 * @param $request PKPRequest
	 * @return string Filename
	 */
	protected function _getFilename($request) {
		$filename = $request->getUserVar('filename');
		if (!in_array($filename, TranslatorAction::getMiscLocaleFiles($this->locale))) {
			fatalError('Invalid locale file specified!');
		}
		return $filename;
	}
}

?>
