<?php
/**
 * @file classes/security/authorization/SubmissionStepsPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionStepsPolicy
 * @ingroup security_authorization
 *
 * @brief Class to check proper use of Submission steps in OMP's submission wizard
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class SubmissionStepsPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/** @var array */
	var $_args;

	//
	// Getters and Setters
	//
	/**
	 * Return the request.
	 * @return PKPRequest
	 */
	function &getRequest() {
		return $this->_request;
	}

	/**
	 * Return the request arguments
	 * @return array
	 */
	function &getArgs() {
		return $this->_args;
	}

	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $roleAssignments array
	 */
	function SubmissionStepsPolicy(&$request, &$args) {
		parent::AuthorizationPolicy();
		$this->_request =& $request;
		assert(is_array($args));
		$this->_args =& $args;
	}

	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		// Get the step.
		$step = $this->getStep();
		if ($step === false) return AUTHORIZATION_DENY;

		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// permit if there is no monograph set, but request is for initial step.
		if (!is_a($monograph, 'Monograph') && $step == 1) return AUTHORIZATION_PERMIT;

		// deny if there is no monograph and the request is any step other than the first.
		if ( !is_a($monograph, 'Monograph') ) return AUTHORIZATION_DENY;

		// deny if submission is complete (==0 means complete) and at any step other than the "complete" step (=4)
		if ( $monograph->getSubmissionProgress() == 0 && $step != 4 ) return AUTHORIZATION_DENY;
		// deny if trying to access a step greater than the current progress
		if ( $monograph->getSubmissionProgress() != 0 && $step > $monograph->getSubmissionProgress() ) return AUTHORIZATION_DENY;

		return AUTHORIZATION_PERMIT;
	}

	//
	// Protected helper method
	//
	/**
	 * Identifies the submission step of the request.
	 * @return integer|false returns false if no valid step could be found.
	 */
	function getStep() {
		// if there is no step given, assume 1
		if (empty($this->_args)) return 1;
		// if the step is not numeric or its not between 1 and 4, return false
		if (!is_numeric($this->_args[0]) || $this->_args[0] < 1 || $this->_args[0] > 4) return false;
		// otherwise, return the step in the args list
		return (int)$this->_args[0];
	}
}

?>
