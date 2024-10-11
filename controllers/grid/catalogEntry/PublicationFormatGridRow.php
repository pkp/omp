<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridRow.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridRow
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid row requests.
 */

namespace APP\controllers\grid\catalogEntry;

use PKP\controllers\grid\files\FilesGridCapabilities;
use PKP\controllers\grid\files\SubmissionFilesGridRow;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\submissionFile\SubmissionFile;

class PublicationFormatGridRow extends SubmissionFilesGridRow
{
    /** @var bool */
    protected $_canManage;

    /**
     * Constructor
     *
     * @param bool $canManage
     */
    public function __construct($canManage)
    {
        $this->_canManage = $canManage;

        parent::__construct(
            new FilesGridCapabilities(
                $canManage ? FilesGridCapabilities::FILE_GRID_ADD | FilesGridCapabilities::FILE_GRID_DELETE | FilesGridCapabilities::FILE_GRID_MANAGE | FilesGridCapabilities::FILE_GRID_EDIT | FilesGridCapabilities::FILE_GRID_VIEW_NOTES : 0
            ),
            WORKFLOW_STAGE_ID_PRODUCTION
        );
    }


    //
    // Overridden template methods from GridRow
    //
    /**
     * @copydoc SubmissionFilesGridRow::initialize()
     */
    public function initialize($request, $template = 'controllers/grid/gridRow.tpl')
    {
        parent::initialize($request, $template);
        $submissionFileData = & $this->getData();
        $submissionFile = & $submissionFileData['submissionFile']; /** @var SubmissionFile $submissionFile */
        $router = $request->getRouter();
        $mimetype = $submissionFile->getData('mimetype');
        if ($this->_canManage && in_array($mimetype, ['application/xml', 'text/html'])) {
            $this->addAction(new LinkAction(
                'dependentFiles',
                new AjaxModal(
                    $router->url($request, null, null, 'dependentFiles', null, array_merge(
                        $this->getRequestArgs(),
                        [
                            'submissionFileId' => $submissionFile->getId(),
                        ]
                    )),
                    __('submission.dependentFiles'),
                    'side-modal'
                ),
                __('submission.dependentFiles'),
                'edit'
            ));
        }
    }
}
