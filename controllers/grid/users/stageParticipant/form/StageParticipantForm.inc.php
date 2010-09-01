<?php

/**
 * @file controllers/grid/users/stageParticipant/form/StageParticipantForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantForm
 * @ingroup controllers_grid_submit_stageParticipant_form
 *
 * @brief Form for adding/editing a stageParticipant
 */

import('lib.pkp.classes.form.Form');

class StageParticipantForm extends Form {
	/** The monograph associated with the submission participant being edited **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function StageParticipantForm($monographId) {
		parent::Form('controllers/grid/users/stageParticipant/form/stageParticipantForm.tpl');
		assert(is_numeric($monographId));
		$this->_monographId = (int) $monographId;

		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	* Get the stageParticipant
	* @return StageParticipant
	*/
	function &getStageParticipant() {
		return $this->_stageParticipant;
	}

	/**
	 * Get the MonographId
	 * @return int monographId
	 */
	function getMonographId() {
		return $this->_monographId;
	}

	/**
	 * Get the Monograph
	 * @return object monograph
	 */
	function getMonograph() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		return $monographDao->getMonograph($this->_monographId);
	}

	//
	// Template methods from Form
	//

	/**
	 * Fetch the form.
	 */
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();
		$monograph =& $this->getMonograph();

		/* Get the current possible roles for the submissions current stage
		  This will populate a drop-down which will reload the user
		  listbuilder (based on the selected role) */
		$userGroupStageAssignmentDao =& DAORegistry::getDAO('UserGroupStageAssignmentDAO');
		$userGroups =& $userGroupStageAssignmentDao->getUserGroupsByStage($monograph->getPressId(), $monograph->getCurrentStageId());

		$userGroupOptions = array();
		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$userGroupOptions[$userGroup->getId()] = $userGroup->getLocalizedName();
			unset($userGroup);
		}
		$templateMgr->assign('firstUserGroupId', key($userGroupOptions)); // Get the key of the first option to use for the pre-loaded listbuilder
		$templateMgr->assign('userGroupOptions', $userGroupOptions);

		$templateMgr->assign('monographId', $this->getMonographId());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('userGroupId', 'userId'));
	}
}

?>
