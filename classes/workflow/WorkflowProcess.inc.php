<?php

/**
 * @file classes/workflow/WorkflowElement.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Process
 * @ingroup signoff
 * @see SignoffEntityDAO
 *
 * @brief Extend this class for workflow processes that contain signoff information.
 */

// $Id$

define('WORKFLOW_PROCESS_TYPE_REVIEW_PROCESS', 1);
define('WORKFLOW_PROCESS_TYPE_REVIEW', 2);
define('WORKFLOW_PROCESS_TYPE_EDITING_PROCESS', 3);
define('WORKFLOW_PROCESS_TYPE_COPYEDIT', 4);
define('WORKFLOW_PROCESS_TYPE_PROOFREAD', 5);

class WorkflowProcess extends DataObject {
	//
	// Get/set methods
	//

	/**
	 * get the signoff data
	 * @return ProcessSignoff
	 */
	function getWorkflowProcess() {
		return $this->getData('processSignoff');
	}

	/**
	 * set the signoff data
	 * @param ProcessSignoff object
	 */
	function setWorkflowProcess($processSignoff) {
		return $this->setData('processSignoff', $processSignoff);
	}

}

?>
