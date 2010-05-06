<?php
/**
 * @file SubmissionFormSequence.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFormSequence
 * @ingroup submission
 *
 * @brief Represents a group of forms. Subclasses would be able to examine and react to state before displaying the sequence. Forms in groups should inherit from SequenceForm. 
 */

// $Id$

class SubmissionFormSequence
{
	var $stepForms;
	var $monograph;
	var $currentStep;
	var $currentStepAlias;
	var $aliasLookup;
	var $contextSteps;

	function getNextStep() {
		return $this->currentStep+1;
	}
	function SubmissionFormSequence($monographId = null) {
		if (isset($monographId)) {
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$this->monograph =& $monographDao->getMonograph((int) $monographId);
		} else {
			$this->monograph = null;
		}
		$this->stepForms = array();
		$this->contextSteps = array();
		$this->currentStep = 0;
		$this->currentStepAlias = null;
	}
	function isLastStep() {
		return count($this->stepForms) == $this->currentStep;
	}
	function display() {

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('submitStep', $this->currentStep);
		if (isset($this->contextSteps[$this->aliasLookup[$this->currentStepAlias]])) { // has context steps
			$templateMgr->assign('contextSteps', $this->contextSteps[$this->aliasLookup[$this->currentStepAlias]]);
		}
		$stepTitle = isset($this->stepForms[$this->currentStep]) ? $this->stepForms[$this->currentStep]['title'] : 'common.none';
		$templateMgr->assign('stepTitle', $stepTitle);
		$templateMgr->assign('submitStepAlias', $this->currentStepAlias);
		$templateMgr->assign_by_ref('steplist', $this->stepForms);
		if(isset($this->monograph))
			$templateMgr->assign('monographId', $this->monograph->getId());

	}
	function addForm($fullImportPath, $class, $guideTag, $title, $alias, $contextParent = null) {
		$step = count($this->stepForms) + 1;
		$this->aliasLookup[$alias] = $step;

		$this->stepForms[$step] = array(
					'path'  => $fullImportPath, 
					'class' => $class, 
					'tag'   => $guideTag, 
					'title' => $title, 
					'alias' => $alias,
					'context' => isset($contextParent) ? 1 : 0
					);

		if (isset($contextParent) && isset($this->aliasLookup[$contextParent])) {
			$this->contextSteps[$this->aliasLookup[$contextParent]][] = array('step' => $step);
		}
	}

	function &getFormForStep($stepAlias) {
		$step = isset($this->aliasLookup[$stepAlias]) ? $this->aliasLookup[$stepAlias] : 0;
		$this->validate($step);
		$this->currentStepAlias = $stepAlias;
		$this->currentStep = $step;

		import($this->stepForms[$step]['path']);
		$submitForm = new $this->stepForms[$step]['class']($this->monograph);
		$submitForm->registerFormWithSequence($this);

		return $submitForm;
	}

	function isValidStep($stepIndex, $isAliasIndex = false) {

		$press =& Request::getPress();

		if (!($stepIndex>0 && $stepIndex<=count($this->stepForms)) || (!isset($this->monograph) && $stepIndex != 1))
			return false;

		if (isset($this->monograph)) {
			if ($this->monograph->getPressId() !== $press->getId())
				return false;
		}

		return true;
	}

	function validate(){}
}
?>
