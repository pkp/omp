<?php

/**
 * @file classes/submission/form/ProductionAssignmentForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionAssignmentForm
 * @ingroup submission_form
 *
 * @brief Form to create or edit a production assignment.
 */

// $Id$


import('lib.pkp.classes.form.Form');

class ProductionAssignmentForm extends Form {

	var $productionAssignment;
	var $monographId;

	/**
	 * Constructor.
	 */
	function ProductionAssignmentForm($monographId, &$productionAssignment) {
		$this->monographId = $monographId;
		$this->productionAssignment =& $productionAssignment;

		parent::Form('productionEditor/productionAssignment.tpl');
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {

		if (isset($this->productionAssignment)) {
			$productionAssignment =& $this->productionAssignment;
			$this->_data = array(
				'type' => $productionAssignment->getType(), 
				'label' => $productionAssignment->getLabel()
			);
		}

	}

	/**
	 * Display the form.
	 */
	function display() {
		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('helpTopicId','production.productionAssignments');

		$productionAssignmentDao =& DAORegistry::getDAO('ProductionAssignmentDAO');
		$templateMgr->assign('assignmentTypeOptions', $productionAssignmentDao->productionAssignmentTypeToLocaleKey());
		$templateMgr->assign('assignmentId', isset($this->productionAssignment) ? $this->productionAssignment->getId() : null);
		$templateMgr->assign('monographId', $this->monographId);

		parent::display();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('type', 'label'));
	}

	/**
	 * Save changes to monograph.
	 * @return int the monograph ID
	 */
	function execute() {
		$productionAssignmentDao =& DAORegistry::getDAO('ProductionAssignmentDAO');

		if ($this->productionAssignment) {
			$productionAssignment =& $this->productionAssignment;
		} else {
			$productionAssignment =& $productionAssignmentDao->newDataObject();
		}

		$productionAssignment->setType($this->getData('type'));
		$productionAssignment->setLabel($this->getData('label'));
		$productionAssignment->setMonographId($this->monographId);

		if ($this->productionAssignment) {
			$productionAssignmentDao->updateObject($productionAssignment);
			$productionAssignmentId = $productionAssignment->getId();
		} else {
			$productionAssignmentId = $productionAssignmentDao->insertObject($productionAssignment);
		}

		return $productionAssignmentId;
	}
}

?>