<?php

/**
 * @file controllers/tab/pubIds/form/PublicIdentifiersForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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


