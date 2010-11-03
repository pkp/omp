<?php

/**
 * @file controllers/modals/editorDecision/form/EditorDecisionForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorDecisionForm
 * @ingroup controllers_modals_editorDecision_form
 *
 * @brief Base class for the editor decision forms.
 */

import('lib.pkp.classes.form.Form');

class EditorDecisionForm extends Form {
	/** The monograph associated with the editor decision **/
	var $_monograph;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $template string The template to display
	 */
	function EditorDecisionForm($monograph, $template) {
		parent::Form($template);
		$this->setMonograph($monograph);

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the Monograph
	 * @param $monograph Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph = $monograph;
	}

	//
	// Overridden template methods
	//
	/**
	 * Initialize form data with the author name and the monograph id.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION));
	}

	/**
	 * Fetch the modal content
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();

		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());
		$templateMgr->assign_by_ref('monograph', $monograph);

		return parent::fetch($request);
	}

}

?>
