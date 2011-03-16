<?php

/**
 * @file controllers/grid/users/stageParticipant/form/StageParticipantForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantForm
 * @ingroup controllers_grid_users_stageParticipant_form
 *
 * @brief Form for adding/editing a stage participant
 * FIXME: Having a form for such a simple mark-up without validation or submission
 *  is overkill, see #6505.
 */

import('lib.pkp.classes.form.Form');

class StageParticipantForm extends Form {

	/**
	 * @var Monograph The monograph associated with the
	 * submission participant being edited
	 */
	var $_monograph;

	/** @var integer */
	var $_stageId;


	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $stageId integer
	 */
	function StageParticipantForm(&$monograph, $stageId) {
		parent::Form('controllers/grid/users/stageParticipant/form/stageParticipantForm.tpl');
		assert(is_a($monograph, 'Monograph'));
		$this->_monograph =& $monograph;
		$this->_stageId = (int)$stageId;
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the stage id.
	 * @return int stageId
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the Monograph.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		// Assign the monograph id to the template.
		$templateMgr =& TemplateManager::getManager();
		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());

		// Assign the stage id to the template.
		$templateMgr->assign('stageId', $this->getStageId());

		// Get the current possible roles for the submissions current
		// stage. This will populate a drop-down which will reload the user
		// listbuilder (based on the selected role).
		$userGroupStageAssignmentDao =& DAORegistry::getDAO('UserGroupStageAssignmentDAO'); /* @var $userGroupStageAssignmentDao UserGroupStageAssignmentDAO */
		$userGroups =& $userGroupStageAssignmentDao->getUserGroupsByStage($monograph->getPressId(), $this->getStageId());
		$userGroupOptions = array();
		while ($userGroup =& $userGroups->next()) {
			$userGroupOptions[$userGroup->getId()] = $userGroup->getLocalizedName();
			unset($userGroup);
		}
		$templateMgr->assign('userGroupOptions', $userGroupOptions);

		// Get the key of the first option to use for the pre-loaded listbuilder.
		if (!empty($userGroupOptions)) {
			$templateMgr->assign('firstUserGroupId', key($userGroupOptions));
		}

		// Render the form.
		return parent::fetch($request);
	}
}
?>
