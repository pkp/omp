<?php
/**
 * @file controllers/grid/files/proof/form/ApprovedProofForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApprovedProofForm
 * @ingroup controllers_grid_files_proof_form
 *
 * @brief Form for editing approved proofs (available for direct sales).
 */


import('lib.pkp.classes.form.Form');

class ApprovedProofForm extends Form {
	/** @var $approvedProof MonographFile */
	var $approvedProof;

	/** @var $monograph Monograph */
	var $monograph;

	/** @var $publicationFormat PublicationFormat */
	var $publicationFormat;

	/**
	 * Constructor
	 * @param $monograph Monograph
	 * @param $publicationFormat PublicationFormat
	 * @param $fileId string fileId-revision
	 */
	function ApprovedProofForm($monograph, $publicationFormat, $fileIdAndRevision) {
		parent::Form('controllers/grid/files/proof/form/approvedProofForm.tpl');

		$this->monograph =& $monograph;
		$this->publicationFormat =& $publicationFormat;

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
		list($fileId, $revision) = explode('-', $fileIdAndRevision);
		$this->approvedProof =& $submissionFileDao->getRevision($fileId, $revision, MONOGRAPH_FILE_PROOF, $this->monograph->getId());
		if (!$this->approvedProof->getViewable()) fatalError('Proof not approved!');

		$this->addCheck(new FormValidatorCustom($this, 'price', 'optional', 'payment.directSales.validPriceRequired', create_function('$price, $form', 'return is_numeric($price) && $price >= 0;'), array(&$this)));
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Extended methods from Form
	//
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('fileId', $this->approvedProof->getFileIdAndRevision());
		return parent::fetch($request);
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('price'));
	}

	/**
	 * @see Form::initData()
	 */
	function initData() {
		$this->_data = array(
			'price' => $this->approvedProof->getDirectSalesPrice()
		);
	}

	/**
	 * @see Form::execute()
	 */
	function execute(&$request) {
		fatalError('unimplemented');
		return $fileId;
	}
}

?>
