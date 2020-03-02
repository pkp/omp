<?php

/**
 * @file controllers/tab/pubIds/form/PublicIdentifiersForm.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicIdentifiersForm
 * @ingroup controllers_tab_pubIds_form
 *
 * @brief Displays a pub ids form.
 */

import('lib.pkp.controllers.tab.pubIds.form.PKPPublicIdentifiersForm');

class PublicIdentifiersForm extends PKPPublicIdentifiersForm {

	/**
	 * Constructor.
	 * @param $pubObject object
	 * @param $stageId integer
	 * @param $formParams array
	 */
	public function __construct($pubObject, $stageId = null, $formParams = null) {
		parent::__construct($pubObject, $stageId, $formParams);
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$enablePublisherId = $request->getContext()->getData('enablePublisherId');
		$templateMgr->assign([
			'enablePublisherId' => (is_a($this->getPubObject(), 'Chapter') && in_array('chapter', $enablePublisherId)) ||
					(is_a($this->getPubObject(), 'Representation') && in_array('representation', $enablePublisherId)) ||
					(is_a($this->getPubObject(), 'SubmissionFile') && in_array('file', $enablePublisherId)),
		]);

		return parent::fetch($request, $template, $display);
	}

	/**
	 * @copydoc Form::execute()
	 */
	public function execute(...$functionArgs) {
		parent::execute(...$functionArgs);
		$pubObject = $this->getPubObject();
		if (is_a($pubObject, 'Chapter')) {
			$chapterDao = DAORegistry::getDAO('ChapterDAO'); /* @var $chapterDao ChapterDAO */
			$chapterDao->updateObject($pubObject);
		}
	}

	/**
	 * @copydoc PKPPublicIdentifiersForm::getAssocType()
	 */
	public function getAssocType($pubObject) {
		if (is_a($pubObject, 'Chapter')) {
			return ASSOC_TYPE_CHAPTER;
		}
		return parent::getAssocType($pubObject);
	}

}


