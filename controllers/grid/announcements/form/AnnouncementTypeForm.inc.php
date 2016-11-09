<?php
/**
 * @file controllers/grid/announcements/form/AnnouncementTypeForm.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementTypeForm
 * @ingroup controllers_grid_announcements_form
 *
 * @brief Form for to read/create/edit announcement types.
 */


import('lib.pkp.classes.manager.form.PKPAnnouncementTypeForm');

class AnnouncementTypeForm extends PKPAnnouncementTypeForm {
	/** @var $pressId int */
	var $pressId;

	/**
	 * Constructor
	 * @param $pressId int
	 * @param $announcementTypeId int leave as default for new announcement
	 */
	function __construct($pressId, $announcementTypeId = null) {
		parent::__construct($announcementTypeId);
		$this->pressId = $pressId;
	}


	//
	// Extended methods from Form
	//
	/**
	 * @see Form::fetch()
	 * @param $request PKPRequest
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('typeId', $this->typeId);
		return parent::fetch($request, 'controllers/grid/announcements/form/announcementTypeForm.tpl');
	}

	//
	// Private helper methdos.
	//
	/**
	 * Helper function to assign the AssocType and the AssocId
	 * @param AnnouncementType the announcement type to be modified
	 */
	function _setAnnouncementTypeAssocId($announcementType) {
		$pressId = $this->pressId;
		$announcementType->setAssocType(ASSOC_TYPE_PRESS);
		$announcementType->setAssocId($pressId);
	}
}

?>
