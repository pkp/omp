<?php
/**
 * @file controllers/informationCenter/linkAction/FileNotesLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileNotesLinkAction
 * @ingroup controllers_informationCenter_linkAction
 *
 * @brief An action to open up the notes IC for a file.
 */

import('controllers.api.file.linkAction.FileLinkAction');

class FileNotesLinkAction extends FileLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographFile MonographFile the monograph file
	 *  to show information about.
	 */
	function FileNotesLinkAction(&$request, &$monographFile, $user) {
		// Instantiate the information center modal.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$ajaxModal = new AjaxModal(
			$router->url(
				$request, null,
				'informationCenter.FileInformationCenterHandler', 'viewInformationCenter',
				null, $this->getActionArgs($monographFile)
			)
		);

		// Configure the file link action.
		parent::FileLinkAction(
			'moreInfo', $ajaxModal,
			'', $this->getNotesState($monographFile, $user)
		);
	}

	function getNotesState($monographFile, $user) {
		$noteDao =& DAORegistry::getDAO('NoteDAO');

		// If no notes exist, display a dimmed icon.
		if (!$noteDao->notesExistByAssoc(ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileId())) {
			return 'notes_none';
		}

		// If new notes exist, display a bold icon.
		if ($noteDao->unreadNotesExistByAssoc(ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileId(), $user->getId())) {
			return 'notes_new';
		}

		// Otherwise, notes exist but not new ones.
		return 'notes';
	}
}

?>
