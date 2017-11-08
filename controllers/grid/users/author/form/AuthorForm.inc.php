<?php

/**
 * @file controllers/grid/users/author/form/AuthorForm.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorForm
 * @ingroup controllers_grid_users_author_form
 *
 * @brief Form for adding/editing an author in OMP
 */

import('lib.pkp.controllers.grid.users.author.form.PKPAuthorForm');

class AuthorForm extends PKPAuthorForm {

	/**
	 * @copydoc PKPAuthorForm::initData()
	 */
	public function initData() {
		parent::initData();

		$author = $this->getAuthor();
		if ($author) {
			$this->setData('isVolumeEditor', $author->getIsVolumeEditor());
		}
	}

	/**
	 * @copydoc PKPAuthorForm::fetch()
	 */
	public function fetch($request) {
		$submission = $this->getSubmission();
		$templateMgr = TemplateManager::getManager($request);
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;

		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$userGroups = $userGroupDao->getByContextId($contextId);
		$volumeEditorGroupIds = array();
		while ($userGroup = $userGroups->next()) {
			if ($userGroup->getIsVolumeEditor()) {
				$volumeEditorGroupIds[] = $userGroup->getId();
			}
		}

		$templateMgr->assign(array(
			'isVolumeEditor' => $this->getData('isVolumeEditor'),
			'volumeEditorGroupIds' => $volumeEditorGroupIds,
			'workType' => $submission->getWorkType(),
		));

		return parent::fetch($request);
	}

	/**
	 * @copydoc PKPAuthorForm::readInputData()
	 */
	public function readInputData() {
		$this->readUserVars(array(
			'isVolumeEditor',
		));
		parent::readInputData();
	}

	/**
	 * @copydoc PKPAuthorForm::execute()
	 */
	public function execute() {
		$authorId = parent::execute();

		$author = $this->getAuthor();
		$author->setIsVolumeEditor($this->getData('isVolumeEditor'));

		$authorDao = DAORegistry::getDAO('AuthorDAO');
		$authorDao->updateObject($author);

		return $authorId;
	}
}
