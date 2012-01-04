<?php
/**
 * @file controllers/api/file/linkAction/FileLinkAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileLinkAction
 * @ingroup controllers_api_file_linkAction
 *
 * @brief An abstract file action.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class FileLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @see LinkAction::LinkAction()
	 */
	function FileLinkAction($id, &$actionRequest, $title = null, $image = null) {
		parent::LinkAction($id, $actionRequest, $title, $image);
	}


	//
	// Protected helper function
	//
	/**
	 * Return the action arguments to address a file.
	 * @param $monographFile MonographFile
	 * @param $stageId int (optional)
	 * @return array
	 */
	function getActionArgs(&$monographFile, $stageId = null) {
		assert(is_a($monographFile, 'MonographFile'));

		// Create the action arguments array.
		$args =  array(
			'fileId' => $monographFile->getFileId(),
			'revision' => $monographFile->getRevision(),
			'monographId' => $monographFile->getMonographId()
		);
		if ($stageId) $args['stageId'] = $stageId;

		return $args;
	}
}

?>
