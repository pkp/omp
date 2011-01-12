<?php

/**
 * @file classes/manager/form/AnnouncementTypeForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementTypeForm
 * @ingroup manager_form
 * @see AnnouncementType
 *
 * @brief Form for press managers to create/edit announcement types.
 */


import('lib.pkp.classes.manager.form.PKPAnnouncementTypeForm');

class AnnouncementTypeForm extends PKPAnnouncementTypeForm {
	/**
	 * Constructor
	 * @param typeId int leave as default for new announcement type
	 */
	function AnnouncementTypeForm($typeId = null) {
		parent::PKPAnnouncementTypeForm($typeId);
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'press.managementPages.announcements');

		parent::display();
	}

	/**
	 * Helper function to assign the AssocType and the AssocId
	 * @param Announcement the announcement to be modified
	 */
	function _setAnnouncementTypeAssocId(&$announcementType) {
		$press =& Request::getPress();
		$announcementType->setAssocType(ASSOC_TYPE_PRESS);
		$announcementType->setAssocId($press->getId());
	}
}

?>