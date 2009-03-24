<?php
/**
 * @file ReviewWorkflowElement.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewWorkflowElement
 * @ingroup workflow
 *
 * @brief ReviewWorkflowElement definition. 
 */

// $Id$

define('REVIEW_TYPE_INTERNAL', 1);
define('REVIEW_TYPE_EXTERNAL', 2); 

import('workflow.WorkflowProcess');

class ReviewProcess extends WorkflowProcess {

	/**
	 * get the review type identifier
	 * @return int
	 */
	function getLocalizedName() {
		return $this->getLocalizedData('reviewTypeName');
	}

	/**
	 * set the review type name
	 * @param $name int
	 * @param $locale string
	 */
	function setName($name, $locale) {
		return $this->setData('reviewTypeName', $name, $locale);
	}


	/**
	 * get the review type
	 * @return int
	 */
	function getType() {
		return $this->getData('reviewType');
	}

	/**
	 * set the review type
	 * @param $reviewType int
	 */
	function setType($reviewType) {
		return $this->setData('reviewType', $reviewType);
	}


}

?>