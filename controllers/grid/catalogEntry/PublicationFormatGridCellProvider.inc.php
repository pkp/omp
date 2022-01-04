<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridCellProvider
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Base class for a cell provider that can retrieve labels for publication formats
 */

use PKP\controllers\grid\DataObjectGridCellProvider;
use PKP\controllers\grid\GridHandler;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\security\Role;
use PKP\submissionFile\SubmissionFile;

// FIXME: Add namespacing
import('lib.pkp.controllers.grid.files.FileNameGridColumn');

class PublicationFormatGridCellProvider extends DataObjectGridCellProvider
{
    /** @var int Submission ID */
    public $_submissionId;

    /** @var boolean */
    protected $_canManage;

    /**
     * Constructor
     *
     * @param $submissionId int Submission ID
     * @param $canManage boolean
     * @param $publicationId int Publication ID
     */
    public function __construct($submissionId, $canManage, $publicationId)
    {
        parent::__construct();
        $this->_submissionId = $submissionId;
        $this->_publicationId = $publicationId;
        $this->_canManage = $canManage;
    }


    //
    // Getters and setters.
    //
    /**
     * Get submission ID.
     *
     * @return int
     */
    public function getSubmissionId()
    {
        return $this->_submissionId;
    }

    /**
     * Get publication ID.
     *
     * @return int
     */
    public function getPublicationId()
    {
        return $this->_publicationId;
    }


    //
    // Template methods from GridCellProvider
    //
    /**
     * Extracts variables for a given column from a data element
     * so that they may be assigned to template before rendering.
     *
     * @param $row GridRow
     * @param $column GridColumn
     *
     * @return array
     */
    public function getTemplateVarsFromRowColumn($row, $column)
    {
        $data = $row->getData();

        if (is_a($data, 'Representation')) {
            /** @var Representation $data */
            switch ($column->getId()) {
                case 'indent': return [];
                case 'name':
                    $remoteURL = $data->getRemoteURL();
                    if ($remoteURL) {
                        return ['label' => '<a href="' . htmlspecialchars($remoteURL) . '" target="_blank">' . htmlspecialchars($data->getLocalizedName()) . '</a>' . '<span class="onix_code">' . $data->getNameForONIXCode() . '</span>'];
                    }
                    return ['label' => htmlspecialchars($data->getLocalizedName()) . '<span class="onix_code">' . $data->getNameForONIXCode() . '</span>'];
                case 'isAvailable':
                    return ['status' => $data->getIsAvailable() ? 'completed' : 'new'];
                case 'isComplete':
                    return ['status' => $data->getIsApproved() ? 'completed' : 'new'];
            }
        } else {
            assert(is_array($data) && isset($data['submissionFile']));
            $proofFile = $data['submissionFile'];
            switch ($column->getId()) {
                case 'isAvailable':
                    return ['status' => ($proofFile->getSalesType() != null && $proofFile->getDirectSalesPrice() != null) ? 'completed' : 'new'];
                case 'name':
                    $fileNameGridColumn = new FileNameGridColumn(true, WORKFLOW_STAGE_ID_PRODUCTION);
                    return $fileNameGridColumn->getTemplateVarsFromRow($row);
                case 'isComplete':
                    return ['status' => $proofFile->getViewable() ? 'completed' : 'new'];
            }
        }

        return parent::getTemplateVarsFromRowColumn($row, $column);
    }

    /**
     * Get request arguments.
     *
     * @param $row GridRow
     *
     * @return array
     */
    public function getRequestArgs($row)
    {
        return [
            'submissionId' => $this->getSubmissionId(),
            'publicationId' => $this->getPublicationId(),
        ];
    }

    /**
     * @see GridCellProvider::getCellActions()
     */
    public function getCellActions($request, $row, $column, $position = GridHandler::GRID_ACTION_POSITION_DEFAULT)
    {
        $data = $row->getData();
        $router = $request->getRouter();
        if (is_a($data, 'Representation')) {
            switch ($column->getId()) {
                case 'isAvailable':
                    return [new LinkAction(
                        'availableRepresentation',
                        new RemoteActionConfirmationModal(
                            $request->getSession(),
                            __($data->getIsAvailable() ? 'grid.catalogEntry.availableRepresentation.removeMessage' : 'grid.catalogEntry.availableRepresentation.message'),
                            __('grid.catalogEntry.availableRepresentation.title'),
                            $router->url(
                                $request,
                                null,
                                null,
                                'setAvailable',
                                null,
                                [
                                    'representationId' => $data->getId(),
                                    'newAvailableState' => $data->getIsAvailable() ? 0 : 1,
                                    'submissionId' => $this->getSubmissionId(),
                                    'publicationId' => $data->getData('publicationId'),
                                ]
                            ),
                            'modal_approve'
                        ),
                        $data->getIsAvailable() ? __('grid.catalogEntry.isAvailable') : __('grid.catalogEntry.isNotAvailable'),
                        $data->getIsAvailable() ? 'complete' : 'incomplete',
                        __('grid.action.formatAvailable')
                    )];
                case 'name':
                    // if it is a remotely hosted content, don't provide
                    // file upload and select link actions
                    $remoteURL = $data->getRemoteURL();
                    if ($remoteURL) {
                        return [];
                    }
                    // If this is just an author account, don't give any actions
                    if (!$this->_canManage) {
                        return [];
                    }
                    import('lib.pkp.controllers.api.file.linkAction.AddFileLinkAction');
                    import('lib.pkp.controllers.grid.files.fileList.linkAction.SelectFilesLinkAction');
                    AppLocale::requireComponents(LOCALE_COMPONENT_PKP_EDITOR);
                    return [
                        new AddFileLinkAction(
                            $request,
                            $this->getSubmissionId(),
                            WORKFLOW_STAGE_ID_PRODUCTION,
                            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT],
                            SubmissionFile::SUBMISSION_FILE_PROOF,
                            ASSOC_TYPE_REPRESENTATION,
                            $data->getId()
                        ),
                        new SelectFilesLinkAction(
                            $request,
                            [
                                'submissionId' => $this->getSubmissionId(),
                                'assocType' => ASSOC_TYPE_REPRESENTATION,
                                'assocId' => $data->getId(),
                                'representationId' => $data->getId(),
                                'publicationId' => $this->getPublicationId(),
                                'stageId' => WORKFLOW_STAGE_ID_PRODUCTION,
                                'fileStage' => SubmissionFile::SUBMISSION_FILE_PROOF,
                            ],
                            __('editor.submission.selectFiles')
                        )
                    ];
                case 'isComplete':
                    return [new LinkAction(
                        'approveRepresentation',
                        new AjaxModal(
                            $router->url(
                                $request,
                                null,
                                null,
                                'setApproved',
                                null,
                                [
                                    'representationId' => $data->getId(),
                                    'newApprovedState' => $data->getIsApproved() ? 0 : 1,
                                    'submissionId' => $this->getSubmissionId(),
                                    'publicationId' => $data->getData('publicationId'),
                                ]
                            ),
                            __('grid.catalogEntry.approvedRepresentation.title'),
                            'modal_approve'
                        ),
                        $data->getIsApproved() ? __('submission.complete') : __('submission.incomplete'),
                        $data->getIsApproved() ? 'complete' : 'incomplete',
                        __('grid.action.setApproval')
                    )];
            }
        } else {
            assert(is_array($data) && isset($data['submissionFile']));
            $submissionFile = $data['submissionFile'];
            switch ($column->getId()) {
                case 'isAvailable':
                    $salesType = preg_replace('/[^\da-z]/i', '', $submissionFile->getSalesType());
                    $salesTypeString = 'editor.monograph.approvedProofs.edit.linkTitle';
                    if ($salesType == 'openAccess') {
                        $salesTypeString = 'payment.directSales.openAccess';
                    } elseif ($salesType == 'directSales') {
                        $salesTypeString = 'payment.directSales.directSales';
                    } elseif ($salesType == 'notAvailable') {
                        $salesTypeString = 'payment.directSales.notAvailable';
                    }
                    return [new LinkAction(
                        'editApprovedProof',
                        new AjaxModal(
                            $router->url($request, null, null, 'editApprovedProof', null, [
                                'submissionFileId' => $submissionFile->getId(),
                                'submissionId' => $submissionFile->getData('submissionId'),
                                'publicationId' => $this->getPublicationId(),
                                'representationId' => $submissionFile->getData('assocId'),
                            ]),
                            __('editor.monograph.approvedProofs.edit'),
                            'edit'
                        ),
                        __($salesTypeString),
                        $salesType
                    )];
                case 'name':
                    $fileNameColumn = new FileNameGridColumn(true, WORKFLOW_STAGE_ID_PRODUCTION, true);
                    return $fileNameColumn->getCellActions($request, $row, $position);
                case 'isComplete':
                    AppLocale::requireComponents(LOCALE_COMPONENT_PKP_EDITOR);
                    $title = __($submissionFile->getViewable() ? 'editor.submission.proofreading.revokeProofApproval' : 'editor.submission.proofreading.approveProof');
                    return [new LinkAction(
                        $submissionFile->getViewable() ? 'approved' : 'not_approved',
                        new AjaxModal(
                            $router->url(
                                $request,
                                null,
                                null,
                                'setProofFileCompletion',
                                null,
                                [
                                    'submissionId' => $submissionFile->getData('submissionId'),
                                    'publicationId' => $this->getPublicationId(),
                                    'submissionFileId' => $submissionFile->getId(),
                                    'approval' => !$submissionFile->getData('viewable'),
                                ]
                            ),
                            $title,
                            'modal_approve'
                        ),
                        $submissionFile->getViewable() ? __('grid.catalogEntry.availableRepresentation.approved') : __('grid.catalogEntry.availableRepresentation.notApproved'),
                        $submissionFile->getViewable() ? 'complete' : 'incomplete',
                        __('grid.action.setApproval')
                    )];
            }
        }
        return parent::getCellActions($request, $row, $column, $position);
    }
}
