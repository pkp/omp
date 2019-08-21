<?php

/**
 * @file controllers/grid/users/author/form/AuthorForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorForm
 * @ingroup controllers_grid_users_author_form
 *
 * @brief Form for adding/editing a author
 */

import('lib.pkp.controllers.grid.users.author.form.PKPAuthorForm');

class AuthorForm extends PKPAuthorForm {
	//
	// Overridden template methods
	//
	/**
	 * @copydoc Form::initData()
	 */
	function initData() {
    parent::initData();
    if ($this->getAuthor()) {
      $this->_data['isVolumeEditor'] = $this->getAuthor()->getIsVolumeEditor();
    }
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('submission', Services::get('submission')->get($this->getPublication()->getData('submissionId')));
		return parent::fetch($request, $template, $display);
	}

	/**
	 * @copydoc Form::readInputData()
	 */
	function readInputData() {
    parent::readInputData();
    $this->readUserVars(['isVolumeEditor']);
	}

	/**
	 * @copydoc Form::execute()
	 */
	function execute() {
    $authorId = parent::execute();
    $author = Services::get('author')->get($authorId);
    if ($author) {
      $author->setIsVolumeEditor($this->getData('isVolumeEditor'));
      DAORegistry::getDAO('AuthorDAO')->updateObject($author);
    }
    return $author->getId();
	}
}


