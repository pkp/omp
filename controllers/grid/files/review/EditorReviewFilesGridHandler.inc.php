<?php

/**
 * @file controllers/grid/files/review/EditorReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorReviewFilesGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Handle the editor review file grid (displays files that are to be reviewed in the current round)
 */

import('controllers.grid.files.review.ReviewFilesGridHandler');

class EditorReviewFilesGridHandler extends ReviewFilesGridHandler {

	/**
	 * Constructor
	 */
	function EditorReviewFilesGridHandler($canAdd = false, $isSelectable = false, $canManage = true) {
		parent::ReviewFilesGridHandler($canAdd, $isSelectable, true, $canManage);

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'downloadAllFiles', 'selectFiles',
						'uploadReviewFile', 'updateReviewFiles'));
	}
}