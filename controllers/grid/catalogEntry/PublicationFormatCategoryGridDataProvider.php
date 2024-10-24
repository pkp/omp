<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatCategoryGridDataProvider.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatCategoryGridDataProvider
 *
 * @ingroup controllers_grid_files_final
 *
 * @brief Provide access to proof files management.
 */

namespace APP\controllers\grid\catalogEntry;

use APP\core\Application;
use APP\facades\Repo;
use APP\publication\Publication;
use APP\submission\Submission;
use PKP\controllers\grid\files\SubmissionFilesCategoryGridDataProvider;
use PKP\controllers\grid\files\SubmissionFilesGridDataProvider;
use PKP\submission\Representation;
use PKP\submissionFile\SubmissionFile;

class PublicationFormatCategoryGridDataProvider extends SubmissionFilesCategoryGridDataProvider
{
    /** @var PublicationFormatGridHandler this data provider is used in */
    public $_gridHandler;

    /**
     * Constructor
     */
    public function __construct($gridHandler)
    {
        $this->_gridHandler = $gridHandler;
        parent::__construct(SubmissionFile::SUBMISSION_FILE_PROOF);
        $this->setStageId(WORKFLOW_STAGE_ID_PRODUCTION);
    }


    //
    // Getters/setters
    //
    /**
     * Get the representation associated with this grid
     *
     * @return Representation
     */
    public function getRepresentation()
    {
        return $this->getAuthorizedContextObject(Application::ASSOC_TYPE_REPRESENTATION);
    }

    /**
     * Get the submission associated with this grid
     *
     * @return Submission
     */
    public function getSubmission()
    {
        return $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);
    }

    /**
     * Get the publication associated with this grid
     *
     * @return Publication
     */
    public function getPublication()
    {
        return $this->getAuthorizedContextObject(Application::ASSOC_TYPE_PUBLICATION);
    }

    //
    // Overridden public methods from FilesGridDataProvider
    //
    /**
     * @copydoc GridHandler::getRequestArgs()
     */
    public function getRequestArgs()
    {
        $representation = $this->getRepresentation();
        return array_merge(
            parent::getRequestArgs(),
            [
                'representationId' => $representation->getId(),
                'publicationId' => $this->getPublication()->getId(),
                'assocType' => Application::ASSOC_TYPE_REPRESENTATION,
                'assocId' => $representation->getId(),
            ]
        );
    }

    /**
     * @copydoc GridHandler::loadData
     */
    public function loadData($filter = [])
    {
        return Application::getRepresentationDAO()
            ->getByPublicationId($this->getPublication()->getId());
    }

    /**
     * @copydoc GridDataProvider::loadCategoryData()
     *
     * @param null|mixed $filter
     * @param null|mixed $reviewRound
     */
    public function loadCategoryData($request, $categoryDataElement, $filter = null, $reviewRound = null)
    {
        assert($categoryDataElement instanceof Representation);

        // Retrieve all submission files for the given file stage.
        /** @var Representation $categoryDataElement */
        assert(is_a($categoryDataElement, 'Representation'));

        $submissionFiles = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$this->getPublication()->getData('submissionId')])
            ->filterByAssoc(
                Application::ASSOC_TYPE_REPRESENTATION,
                [$categoryDataElement->getId()]
            )
            ->filterByFileStages([$this->getFileStage()])
            ->getMany();

        // if it is a remotely hosted content, don't provide the files rows
        if ($categoryDataElement->getData('urlRemote')) {
            $this->_gridHandler->setEmptyCategoryRowText('grid.remotelyHostedItem');
            return [];
        }
        $this->_gridHandler->setEmptyCategoryRowText('grid.noItems');
        /** @var SubmissionFilesGridDataProvider */
        $dataProvider = $this->getDataProvider();
        return $dataProvider->prepareSubmissionFileData($submissionFiles->toArray(), false, $filter);
    }
}
