<?php
/**
 * @file controllers/api/file/linkAction/DownloadCopyeditedFileLinkAction.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DownloadCopyeditedFileLinkAction
 * @ingroup controllers_api_file_linkAction
 *
 * @brief An action to download a file.
 */

import('lib.pkp.controllers.api.file.linkAction.DownloadFileLinkAction');

class DownloadCopyeditedFileLinkAction extends DownloadFileLinkAction {
	/** @var $_user User */
	var $_user;

	/** @var $_userGroup UserGroup */
	var $_userGroup;

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographFile MonographFile the monograph file to
	 *  link to.
	 */
	function DownloadCopyeditedFileLinkAction($request, $monographFile, $user, $userGroup) {
		$this->_user = $user;
		$this->_userGroup = $userGroup;

		parent::DownloadFileLinkAction($request, $monographFile);
	}

	/**
	 * Get the label for the file download action.
	 * @param $monographFile MonographFile
	 * @return string
	 */
	function getLabel(&$monographFile) {
		return $this->_user->getFullName() . ' (' . $this->_userGroup->getLocalizedName() . '): ' . $monographFile->getFileLabel();
	}
}

?>
