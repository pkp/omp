<?php

/**
 * @file controllers/grid/users/author/AuthorGridHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorGridHandler
 * @ingroup controllers_grid_users_author
 *
 * @brief Class to handle author grid requests for OMP.
 */
import('lib.pkp.controllers.grid.users.author.PKPAuthorGridHandler');
import('controllers.grid.users.author.AuthorGridCellProvider');

class AuthorGridHandler extends PKPAuthorGridHandler {

	/**
	 * @copydoc GridHandler::initialize()
	 */
	public function initialize($request, $args = null) {
		parent::initialize($request, $args);

		if ($this->getSubmission()->getWorkType() == WORK_TYPE_EDITED_VOLUME) {
			AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR);
			$cellProvider = new AuthorGridCellProvider();
			$this->addColumn(
				new GridColumn(
					'isVolumeEditor',
					'submission.submit.isVolumeEditor.grid',
					null,
					'controllers/grid/common/cell/checkMarkCell.tpl',
					$cellProvider
				)
			);
		}
	}

	/**
	 * Retrieve the app-specific author form
	 *
	 * @param Submission $submission
	 * @param Author $author
	 * @param string $submissionIdFieldName
	 */
	public function getAuthorForm($submission, $author, $submissionIdFieldName) {
		import('controllers.grid.users.author.form.AuthorForm');
		return new AuthorForm($submission, $author, $submissionIdFieldName);
	}
}
