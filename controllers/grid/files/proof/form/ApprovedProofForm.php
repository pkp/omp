<?php
/**
 * @file controllers/grid/files/proof/form/ApprovedProofForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ApprovedProofForm
 * @ingroup controllers_grid_files_proof_form
 *
 * @brief Form for editing approved proofs (available for direct sales).
 */

namespace APP\controllers\grid\files\proof\form;

use APP\facades\Repo;
use APP\template\TemplateManager;
use PKP\form\Form;
use PKP\submissionFile\SubmissionFile;

class ApprovedProofForm extends Form
{
    /** @var SubmissionFile $approvedProof */
    public $approvedProof;

    /** @var Monograph $monograph */
    public $monograph;

    /** @var PublicationFormat $publicationFormat */
    public $publicationFormat;

    /**
     * Constructor
     *
     * @param Monograph $monograph
     * @param PublicationFormat $publicationFormat
     * @param int $submissionFileId
     */
    public function __construct($monograph, $publicationFormat, $submissionFileId)
    {
        parent::__construct('controllers/grid/files/proof/form/approvedProofForm.tpl');

        $this->monograph = $monograph;
        $this->publicationFormat = $publicationFormat;
        $this->approvedProof = Repo::submissionFile()->get($submissionFileId);

        // matches currencies like:  1,500.50 1500.50 1,112.15 5,99 .99
        $this->addCheck(new \PKP\form\validation\FormValidatorRegExp($this, 'price', 'optional', 'grid.catalogEntry.validPriceRequired', '/^(([1-9]\d{0,2}(,\d{3})*|[1-9]\d*|0|)(.\d{2})?|([1-9]\d{0,2}(,\d{3})*|[1-9]\d*|0|)(.\d{2})?)$/'));
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }


    //
    // Extended methods from Form
    //
    /**
     * @copydoc Form::fetch
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('submissionFileId', $this->approvedProof->getId());
        $templateMgr->assign('submissionId', $this->monograph->getId());
        $templateMgr->assign('representationId', $this->publicationFormat->getId());
        $templateMgr->assign('publicationId', $this->publicationFormat->getData('publicationId'));

        $salesTypes = [
            'openAccess' => 'payment.directSales.openAccess',
            'directSales' => 'payment.directSales.directSales',
            'notAvailable' => 'payment.directSales.notAvailable',
        ];

        $templateMgr->assign('salesTypes', $salesTypes);
        $templateMgr->assign('salesType', $this->approvedProof->getSalesType());
        return parent::fetch($request, $template, $display);
    }

    /**
     * @see Form::readInputData()
     */
    public function readInputData()
    {
        $this->readUserVars(['price', 'salesType']);
    }

    /**
     * @see Form::initData()
     */
    public function initData()
    {
        $this->_data = [
            'price' => $this->approvedProof->getDirectSalesPrice(),
            'salesType' => $this->approvedProof->getSalesType(),
        ];
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        parent::execute(...$functionArgs);
        $salesType = $this->getData('salesType');

        $params = [
            'directSalesPrice' => $this->getData('price'),
            'salesType' => $salesType,
        ];

        if ($salesType === 'notAvailable') {
            // Not available
            $params['directSalesPrice'] = null;
        } elseif ($salesType === 'openAccess') {
            // Open access
            $params['directSalesPrice'] = 0;
        }

        Repo::submissionFile()
            ->edit(
                $this->approvedProof,
                $params
            );

        $id = Repo::submissionFile()->get($this->approvedProof->getId());

        return $id;
    }
}
