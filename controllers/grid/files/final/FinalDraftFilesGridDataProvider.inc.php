<?php

/**
 * @file controllers/grid/files/final/FinalDraftFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FinalDraftFilesGridDataProvider
 * @ingroup controllers_grid_files_final
 *
 * @brief Provide access to final draft files management.
 */


import('controllers.grid.files.SubmissionFilesGridDataProvider');

class FinalDraftFilesGridDataProvider extends SubmissionFilesGridDataProvider {
	/**
	 * Constructor
	 */
	function FinalDraftFilesGridDataProvider() {
		parent::SubmissionFilesGridDataProvider(MONOGRAPH_FILE_FINAL);
	}

	//
	// Overridden public methods from FilesGridDataProvider
	//
	/**
	 * @see GridDataProvider::loadData()
	 */
	function &loadData() {
		// Get all MONOGRAPH_FILE_FINAL files, but filter out non-viewable ones
		// (that have been de-selected using the "manage" tool)
		$data =& parent::loadData();

		foreach ($data as $i => $fileData) {
			$submissionFile =& $fileData['submissionFile'];
			if (!$submissionFile->getViewable()) unset($data[$i]);
			unset($submissionFile);
		}

		return $data;
	}

	/**
	 * @see FilesGridDataProvider::getSelectAction()
	 */
	function &getSelectAction($request) {
		import('controllers.grid.files.fileList.linkAction.SelectFilesLinkAction');
		$monograph =& $this->getMonograph();
		$actionArgs = array(
			'monographId' => $monograph->getId(),
			'stageId' => $this->_getStageId()
		);
		$selectAction = new SelectFilesLinkAction(
			&$request, $actionArgs,
			__('editor.monograph.final.manageFinalDraftFiles')
		);
		return $selectAction;
	}
}

?>
