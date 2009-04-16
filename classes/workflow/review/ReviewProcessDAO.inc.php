<?php

/**
 * @file classes/review/ReviewTypeDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewTypeDAO
 * @ingroup review
 * @see ReviewType
 *
 * @brief Operations for retrieving and modifying ReviewType objects.
 */

// $Id$

import('workflow.review.ReviewProcess');

class ReviewProcessDAO extends DAO {

	function idExists($reviewTypeId) {
		$returner = false;

		switch ($reviewTypeId) {
		case REVIEW_TYPE_INTERNAL:
			$returner = true;
			break;
		case REVIEW_TYPE_EXTERNAL:
			$returner = true;
			break;
		}

		return $returner;
	}

	/**
	 * Retrieve a review type by ID.
	 * @param $reviewTypeId int
	 * @return ReviewType
	 */
	function &getById($reviewTypeId) {

		$obj = null;
		switch ($reviewTypeId) {
		case REVIEW_TYPE_INTERNAL:
			$obj =& $this->newDataObject();
			$obj->setId(REVIEW_TYPE_INTERNAL);
			$obj->setName('Internal Review', Locale::getLocale());
			break;
		case REVIEW_TYPE_EXTERNAL:
			$obj =& $this->newDataObject();
			$obj->setId(REVIEW_TYPE_EXTERNAL);
			$obj->setName('External Review', Locale::getLocale());
			break;
		}

		return $obj;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return ReviewWorkflowElement
	 */
	function newDataObject() {
		return new ReviewProcess();
	}
	function &getEnabledObjects() {
		$obj1 =& $this->newDataObject();
		$obj2 =& $this->newDataObject();
		$workflowDao =& DAORegistry::getDAO('WorkflowDAO');

		$obj1->setProcessId(REVIEW_TYPE_INTERNAL);
		$obj1->setName('Internalx Review', Locale::getLocale());
//		$obj1->setWorkflowProcess($processSignoffDao->getProcessSignoff($monographId, WORKFLOW_PROCESS_TYPE_REVIEW, REVIEW_TYPE_INTERNAL));

		$obj2->setProcessId(REVIEW_TYPE_EXTERNAL);
		$obj2->setName('External Review', Locale::getLocale());
//		$obj2->setWorkflowProcess($processSignoffDao->getProcessSignoff($monographId, WORKFLOW_PROCESS_TYPE_REVIEW, REVIEW_TYPE_EXTERNAL));

		$returner = array($obj1, $obj2);
		return $returner;
	}

}

?>