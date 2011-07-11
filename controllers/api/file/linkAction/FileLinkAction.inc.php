<?php
/**
 * @file controllers/api/file/linkAction/FileLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	 * @return array
	 */
	function getActionArgs(&$monographFile) {
		assert(is_a($monographFile, 'MonographFile'));

		// Create the action arguments array.
		return array(
			'fileId' => $monographFile->getFileId(),
			'revision' => $monographFile->getRevision()
		);
	}
}

?>
