<?php
/**
 * @file controllers/grid/files/proof/form/ApprovedProofForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		list($fileId, $revision) = explode('-', $fileIdAndRevision);
		$this->approvedProof =& $submissionFileDao->getRevision($fileId, $revision, SUBMISSION_FILE_PROOF, $this->monograph->getId());
		if (!$this->approvedProof->getViewable()) fatalError('Proof not approved!');

		// matches currencies like:  1,500.50 1500.50 1,112.15 5,99 .99
		$this->addCheck(new FormValidatorRegExp($this, 'price', 'optional', 'grid.catalogEntry.validPriceRequired', '/^(([1-9]\d{0,2}(,\d{3})*|[1-9]\d*|0|)(.\d{2})?|([1-9]\d{0,2}(,\d{3})*|[1-9]\d*|0|)(.\d{2})?)$/'));
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Extended methods from Form
	//
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('fileId', $this->approvedProof->getFileIdAndRevision());
		$templateMgr->assign('submissionId', $this->monograph->getId());
		$templateMgr->assign('representationId', $this->publicationFormat->getId());

		$salesTypes = array(
			'openAccess' => 'payment.directSales.openAccess',
			'directSales' => 'payment.directSales.directSales',
			'notAvailable' => 'payment.directSales.notAvailable',
		);

		$templateMgr->assign('salesTypes', $salesTypes);
		$templateMgr->assign('salesType', $this->approvedProof->getSalesType());
		return parent::fetch($request);
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('price', 'salesType'));
	}

	/**
	 * @see Form::initData()
	 */
	function initData() {
		$this->_data = array(
			'price' => $this->approvedProof->getDirectSalesPrice(),
			'salesType' => $this->approvedProof->getSalesType(),
		);
	}

	/**
	 * @see Form::execute()
	 */
	function execute($request) {
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$salesType = $this->getData('salesType');
		if ($salesType === 'notAvailable') {
			// Not available
			$this->approvedProof->setDirectSalesPrice(null);
		} elseif ($salesType === 'openAccess') {
			// Open access
			$this->approvedProof->setDirectSalesPrice(0);
		} else { /* $salesType === 'directSales' */
			// Direct sale
			$this->approvedProof->setDirectSalesPrice($this->getData('price'));
		}
		$this->approvedProof->setSalesType($salesType);
		$submissionFileDao->updateObject($this->approvedProof);

		return $this->approvedProof->getFileIdAndRevision();
	}
}

?>
