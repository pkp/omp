<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridHandler
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests.
 */

namespace APP\controllers\grid\catalogEntry;

use APP\controllers\grid\catalogEntry\form\PublicationFormatForm;
use APP\controllers\grid\catalogEntry\form\PublicationFormatMetadataForm;
use APP\controllers\grid\files\proof\form\ApprovedProofForm;
use APP\controllers\tab\pubIds\form\PublicIdentifiersForm;
use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\log\event\SubmissionEventLogEntry;
use APP\notification\NotificationManager;
use APP\publication\Publication;
use APP\publicationFormat\PublicationFormat;
use APP\publicationFormat\PublicationFormatDAO;
use APP\publicationFormat\PublicationFormatTombstoneManager;
use APP\services\PublicationFormatService;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\controllers\grid\CategoryGridHandler;
use PKP\controllers\grid\files\proof\form\ManageProofFilesForm;
use PKP\controllers\grid\GridColumn;
use PKP\controllers\grid\pubIds\form\PKPAssignPublicIdentifiersForm;
use PKP\core\Core;
use PKP\core\JSONMessage;
use PKP\core\PKPApplication;
use PKP\db\DAO;
use PKP\db\DAORegistry;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\log\event\SubmissionFileEventLogEntry;
use PKP\notification\Notification;
use PKP\plugins\PluginRegistry;
use PKP\security\authorization\internal\RepresentationRequiredPolicy;
use PKP\security\authorization\PublicationAccessPolicy;
use PKP\security\Role;
use PKP\submission\PKPSubmission;
use PKP\submissionFile\SubmissionFile;

class PublicationFormatGridHandler extends CategoryGridHandler
{
    /** @var PublicationFormatGridCellProvider */
    public $_cellProvider;

    /** @var Submission */
    public $_submission;

    /** @var Publication */
    public $_publication;

    /** @var bool */
    protected $_canManage;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(new PublicationFormatCategoryGridDataProvider($this));
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_SITE_ADMIN],
            [
                'setAvailable', 'editApprovedProof', 'saveApprovedProof',
            ]
        );
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT, Role::ROLE_ID_SITE_ADMIN],
            [
                'addFormat', 'editFormat', 'editFormatTab', 'updateFormat', 'deleteFormat',
                'setApproved', 'setProofFileCompletion', 'selectFiles',
                'identifiers', 'updateIdentifiers', 'clearPubId',
                'dependentFiles', 'editFormatMetadata', 'updateFormatMetadata'
            ]
        );
        $this->addRoleAssignment(
            [Role::ROLE_ID_AUTHOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT, Role::ROLE_ID_SITE_ADMIN],
            [
                'fetchGrid', 'fetchRow', 'fetchCategory',
            ]
        );
    }


    //
    // Getters/Setters
    //
    /**
     * Get the submission associated with this publication format grid.
     *
     * @return Submission
     */
    public function getSubmission()
    {
        return $this->_submission;
    }

    /**
     * Set the submission
     *
     * @param Submission $submission
     */
    public function setSubmission($submission)
    {
        $this->_submission = $submission;
    }

    /**
     * Get the publication associated with this publication format grid.
     *
     * @return Publication
     */
    public function getPublication()
    {
        return $this->_publication;
    }

    /**
     * Set the publication
     *
     * @param Publication $publication
     */
    public function setPublication($publication)
    {
        $this->_publication = $publication;
    }

    //
    // Overridden methods from PKPHandler
    //
    /**
     * @copydoc CategoryGridHandler::initialize
     *
     * @param null|mixed $args
     */
    public function initialize($request, $args = null)
    {
        parent::initialize($request, $args);

        // Retrieve the authorized submission.
        $this->setSubmission($this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION));
        $this->setPublication($this->getAuthorizedContextObject(Application::ASSOC_TYPE_PUBLICATION));

        $this->setTitle('monograph.publicationFormats');

        if ($this->getPublication()->getData('status') !== PKPSubmission::STATUS_PUBLISHED) {
            // Grid actions
            $router = $request->getRouter();
            $actionArgs = $this->getRequestArgs();
            $userRoles = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_USER_ROLES);
            $this->_canManage = 0 != count(array_intersect($userRoles, [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT]));
            if ($this->_canManage) {
                $this->addAction(
                    new LinkAction(
                        'addFormat',
                        new AjaxModal(
                            $router->url($request, null, null, 'addFormat', null, $actionArgs),
                            __('grid.action.addFormat'),
                        ),
                        __('grid.action.addFormat'),
                        'add_item'
                    )
                );
            }
        }

        // Columns
        $this->_cellProvider = new PublicationFormatGridCellProvider($this->getSubmission()->getId(), $this->_canManage, $this->getPublication()->getId());
        $this->addColumn(
            new GridColumn(
                'name',
                'common.name',
                null,
                null,
                $this->_cellProvider,
                ['width' => 60, 'anyhtml' => true]
            )
        );
        if ($this->_canManage) {
            $this->addColumn(
                new GridColumn(
                    'isComplete',
                    'common.complete',
                    null,
                    'controllers/grid/common/cell/statusCell.tpl',
                    $this->_cellProvider,
                    ['width' => 20]
                )
            );
            $this->addColumn(
                new GridColumn(
                    'isAvailable',
                    'grid.catalogEntry.availability',
                    null,
                    'controllers/grid/common/cell/statusCell.tpl',
                    $this->_cellProvider,
                    ['width' => 20]
                )
            );
        }
    }

    /**
     * @see PKPHandler::authorize()
     *
     * @param Request $request
     * @param array $args
     * @param array $roleAssignments
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $this->addPolicy(new PublicationAccessPolicy($request, $args, $roleAssignments));

        if ($request->getUserVar('representationId')) {
            $this->addPolicy(new RepresentationRequiredPolicy($request, $args));
        }

        return parent::authorize($request, $args, $roleAssignments);
    }


    //
    // Overridden methods from GridHandler
    //
    /**
     * @see GridHandler::getRowInstance()
     *
     * @return PublicationFormatGridCategoryRow
     */
    public function getCategoryRowInstance()
    {
        return new PublicationFormatGridCategoryRow($this->getSubmission(), $this->_cellProvider, $this->_canManage, $this->getPublication());
    }


    //
    // Public Publication Format Grid Actions
    //
    /**
     * Edit a publication format modal
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function editFormat($args, $request)
    {
        $representation = $this->getRequestedPublicationFormat($request);
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'submissionId' => $this->getSubmission()->getId(),
            'publicationId' => $this->getPublication()->getId(),
            'remoteRepresentation' => $representation?->getData('urlRemote') ?: null,
            'representationId' => $representation?->getId()
        ]);

        $publisherIdEnabled = in_array('representation', (array) $request->getContext()->getData('enablePublisherId'));
        $pubIdPlugins = PluginRegistry::getPlugins('pubIds');
        $pubIdEnabled = false;
        foreach ($pubIdPlugins as $pubIdPlugin) {
            if ($pubIdPlugin->isObjectTypeEnabled('Representation', $request->getContext()->getId())) {
                $pubIdEnabled = true;
                break;
            }
        }
        $templateMgr->assign('showIdentifierTab', $publisherIdEnabled || $pubIdEnabled);

        return new JSONMessage(true, $templateMgr->fetch('controllers/grid/catalogEntry/editFormat.tpl'));
    }

    /**
     * Edit a format
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function editFormatTab($args, $request)
    {
        $representation = $this->getRequestedPublicationFormat($request);

        $publicationFormatForm = new PublicationFormatForm($this->getSubmission(), $representation, $this->getPublication());
        $publicationFormatForm->initData();

        return new JSONMessage(true, $publicationFormatForm->fetch($request));
    }

    /**
     * Update a format
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function updateFormat($args, $request)
    {
        $representation = $this->getRequestedPublicationFormat($request);

        $publicationFormatForm = new PublicationFormatForm($this->getSubmission(), $representation, $this->getPublication());
        $publicationFormatForm->readInputData();
        if ($publicationFormatForm->validate()) {
            $publicationFormatForm->execute();
            return DAO::getDataChangedEvent();
        }
        return new JSONMessage(true, $publicationFormatForm->fetch($request));
    }

    /**
     * Delete a format
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function deleteFormat($args, $request)
    {
        $context = $request->getContext();
        $submission = $this->getSubmission();
        $representation = $this->getRequestedPublicationFormat($request);

        if (!$request->checkCSRF()) {
            return new JSONMessage(false, __('form.csrfInvalid'));
        }

        if (!$representation) {
            return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
        }

        /** @var PublicationFormatService */
        $publicationFormatService = app()->get('publicationFormat');
        $publicationFormatService->deleteFormat($representation, $submission, $context);

        $currentUser = $request->getUser();
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => __('notification.removedPublicationFormat')]);

        return DAO::getDataChangedEvent();
    }

    /**
     * Set a format's "approved" state
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function setApproved($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_REPRESENTATION);
        $representationDao = Application::getRepresentationDAO();

        if (!$representation) {
            return new JSONMessage(false, __('common.unknownError'));
        }

        $confirmationText = __('grid.catalogEntry.approvedRepresentation.removeMessage');
        if ($request->getUserVar('newApprovedState')) {
            $confirmationText = __('grid.catalogEntry.approvedRepresentation.message');
        }
        $formTemplate = $this->getAssignPublicIdentifiersFormTemplate();
        $assignPublicIdentifiersForm = new PKPAssignPublicIdentifiersForm($formTemplate, $representation, $request->getUserVar('newApprovedState'), $confirmationText);
        if (!$request->getUserVar('confirmed')) {
            // Display assign pub ids modal
            $assignPublicIdentifiersForm->initData();
            return new JSONMessage(true, $assignPublicIdentifiersForm->fetch($request));
        }
        if ($request->getUserVar('newApprovedState')) {
            // Assign pub ids
            $assignPublicIdentifiersForm->readInputData();
            $assignPublicIdentifiersForm->execute();
        }

        $newApprovedState = (int) $request->getUserVar('newApprovedState');
        $representation->setIsApproved($newApprovedState);
        $representationDao->updateObject($representation);

        $logEntry = Repo::eventLog()->newDataObject([
            'assocType' => PKPApplication::ASSOC_TYPE_SUBMISSION,
            'assocId' => $this->getSubmission()->getId(),
            'eventType' => $newApprovedState ? SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_PUBLISH : SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_UNPUBLISH,
            'userId' => $request->getUser()?->getId(),
            'message' => $newApprovedState ? 'submission.event.publicationFormatPublished' : 'submission.event.publicationFormatUnpublished',
            'isTranslated' => false,
            'dateLogged' => Core::getCurrentDate(),
            'publicationFormatName' => $representation->getData('name')
        ]);
        Repo::eventLog()->add($logEntry);

        // Update the formats tombstones.
        $publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
        if ($representation->getIsAvailable() && $representation->getIsApproved()) {
            // Delete any existing tombstone.
            $publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats([$representation]);
        } else {
            // (Re)create a tombstone for this publication format.
            $publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats([$representation]);
            $publicationFormatTombstoneMgr->insertTombstoneByPublicationFormat($representation, $request->getContext());
        }

        return DAO::getDataChangedEvent($representation->getId());
    }

    /**
     * Set a format's "available" state
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function setAvailable($args, $request)
    {
        $context = $request->getContext();
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */
        $publicationFormat = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_REPRESENTATION);

        if (!$publicationFormat) {
            return new JSONMessage(false, __('common.unknownError'));
        }

        $newAvailableState = (int) $request->getUserVar('newAvailableState');
        $publicationFormat->setIsAvailable($newAvailableState);
        $publicationFormatDao->updateObject($publicationFormat);

        // log the state changing of the format.
        $logEntry = Repo::eventLog()->newDataObject([
            'assocType' => PKPApplication::ASSOC_TYPE_SUBMISSION,
            'assocId' => $this->getSubmission()->getId(),
            'eventType' => $newAvailableState ? SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_AVAILABLE : SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_UNAVAILABLE,
            'userId' => $request->getUser()?->getId(),
            'message' => $newAvailableState ? 'submission.event.publicationFormatMadeAvailable' : 'submission.event.publicationFormatMadeUnavailable',
            'isTranslated' => false,
            'dateLogged' => Core::getCurrentDate(),
            'publicationFormatName' => $publicationFormat->getData('name')
        ]);
        Repo::eventLog()->add($logEntry);

        // Update the formats tombstones.
        $publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
        if ($publicationFormat->getIsAvailable() && $publicationFormat->getIsApproved()) {
            // Delete any existing tombstone.
            $publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats([$publicationFormat]);
        } else {
            // (Re)create a tombstone for this publication format.
            $publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats([$publicationFormat]);
            $publicationFormatTombstoneMgr->insertTombstoneByPublicationFormat($publicationFormat, $context);
        }

        return DAO::getDataChangedEvent($publicationFormat->getId());
    }

    /**
     * Edit an approved proof.
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function editApprovedProof($args, $request)
    {
        $this->initialize($request);
        $submission = $this->getSubmission();
        $representation = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_REPRESENTATION);

        $approvedProofForm = new ApprovedProofForm($submission, $representation, $request->getUserVar('submissionFileId'));
        $approvedProofForm->initData();

        return new JSONMessage(true, $approvedProofForm->fetch($request));
    }

    /**
     * Save an approved proof.
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function saveApprovedProof($args, $request)
    {
        $submission = $this->getSubmission();
        $representation = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_REPRESENTATION);

        $approvedProofForm = new ApprovedProofForm($submission, $representation, $request->getUserVar('submissionFileId'));
        $approvedProofForm->readInputData();

        if ($approvedProofForm->validate()) {
            $approvedProofForm->execute();
            return DAO::getDataChangedEvent();
        }
        return new JSONMessage(true, $approvedProofForm->fetch($request));
    }

    /**
     * Get the filename of the "assign public identifiers" form template.
     *
     * @return string
     */
    public function getAssignPublicIdentifiersFormTemplate()
    {
        return 'controllers/grid/pubIds/form/assignPublicIdentifiersForm.tpl';
    }

    //
    // Overridden methods from GridHandler
    //
    /**
     * @copydoc GridHandler::getRowInstance()
     */
    public function getRowInstance()
    {
        return new PublicationFormatGridRow($this->_canManage);
    }

    /**
     * Get the arguments that will identify the data in the grid
     * In this case, the submission.
     *
     * @return array
     */
    public function getRequestArgs()
    {
        return [
            'submissionId' => $this->getSubmission()->getId(),
            'publicationId' => $this->getPublication()->getId(),
        ];
    }


    //
    // Public grid actions
    //
    /**
     * Add a new publication format
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function addFormat($args, $request)
    {
        return $this->editFormat($args, $request);
    }

    /**
     * Set the approval status for a file.
     *
     * @param array $args
     * @param Request $request
     */
    public function setProofFileCompletion($args, $request)
    {
        $submission = $this->getSubmission();
        $submissionFileId = (int) $request->getUserVar('submissionFileId');
        $submissionFile = Repo::submissionFile()->get($submissionFileId);
        if ($submissionFile->getData('fileStage') !== SubmissionFile::SUBMISSION_FILE_PROOF || $submissionFile->getData('submissionId') != $submission->getId()) {
            return new JSONMessage(false);
        }
        $confirmationText = __('editor.submission.proofreading.confirmRemoveCompletion');
        if ($request->getUserVar('approval')) {
            $confirmationText = __('editor.submission.proofreading.confirmCompletion');
        }
        if ($submissionFile && $submissionFile->getData('assocType') == Application::ASSOC_TYPE_REPRESENTATION) {
            $formTemplate = $this->getAssignPublicIdentifiersFormTemplate();
            $assignPublicIdentifiersForm = new PKPAssignPublicIdentifiersForm($formTemplate, $submissionFile, $request->getUserVar('approval'), $confirmationText);
            if (!$request->getUserVar('confirmed')) {
                // Display assign pub ids modal
                $assignPublicIdentifiersForm->initData();
                return new JSONMessage(true, $assignPublicIdentifiersForm->fetch($request));
            }
            if ($request->getUserVar('approval')) {
                // Assign pub ids
                $assignPublicIdentifiersForm->readInputData();
                $assignPublicIdentifiersForm->execute();
            }
            // Update the approval flag
            $params = ['viewable' => (bool) $request->getUserVar('approval')];
            Repo::submissionFile()
                ->edit($submissionFile, $params);

            $submissionFile = Repo::submissionFile()->get($submissionFileId);

            // Log the event
            $user = $request->getUser();
            $eventLog = Repo::eventLog()->newDataObject([
                'assocType' => PKPApplication::ASSOC_TYPE_SUBMISSION_FILE,
                'assocId' => $submissionFile->getId(),
                'eventType' => SubmissionFileEventLogEntry::SUBMISSION_LOG_FILE_SIGNOFF_SIGNOFF,
                'userId' => $user->getId(),
                'message' => 'submission.event.signoffSignoff',
                'isTranslated' => false,
                'dateLogged' => Core::getCurrentDate(),
                'filename' => $submissionFile->getData('name'),
                'userFullName' => $user->getFullName(),
                'username' => $user->getUsername()
            ]);
            Repo::eventLog()->add($eventLog);

            return DAO::getDataChangedEvent();
        }

        return new JSONMessage(false);
    }

    /**
     * Show the form to allow the user to select files from previous stages
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function selectFiles($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_REPRESENTATION);

        $manageProofFilesForm = new ManageProofFilesForm($this->getSubmission()->getId(), $this->getPublication()->getId(), $representation->getId());
        $manageProofFilesForm->initData();
        return new JSONMessage(true, $manageProofFilesForm->fetch($request));
    }

    /**
     * Load a form to edit a format's metadata
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function editFormatMetadata($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_REPRESENTATION);

        $publicationFormatForm = new PublicationFormatMetadataForm($this->getSubmission(), $this->getPublication(), $representation);
        $publicationFormatForm->initData();

        return new JSONMessage(true, $publicationFormatForm->fetch($request));
    }

    /**
     * Save a form to edit format's metadata
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function updateFormatMetadata($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_REPRESENTATION);

        $publicationFormatForm = new PublicationFormatMetadataForm($this->getSubmission(), $this->getPublication(), $representation);
        $publicationFormatForm->readInputData();
        if ($publicationFormatForm->validate()) {
            $publicationFormatForm->execute();
            return DAO::getDataChangedEvent();
        }

        return new JSONMessage(true, $publicationFormatForm->fetch($request));
    }

    /**
     * Edit pub ids
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function identifiers($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_REPRESENTATION);

        $form = new PublicIdentifiersForm($representation);
        $form->initData();
        return new JSONMessage(true, $form->fetch($request));
    }

    /**
     * Update pub ids
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function updateIdentifiers($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_REPRESENTATION);

        $form = new PublicIdentifiersForm($representation);
        $form->readInputData();
        if ($form->validate()) {
            $form->execute();
            return DAO::getDataChangedEvent();
        } else {
            return new JSONMessage(true, $form->fetch($request));
        }
    }

    /**
     * Clear pub id
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function clearPubId($args, $request)
    {
        if (!$request->checkCSRF()) {
            return new JSONMessage(false);
        }

        $representation = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_REPRESENTATION);

        $form = new PublicIdentifiersForm($representation);
        $form->clearPubId($request->getUserVar('pubIdPlugIn'));
        return new JSONMessage(true);
    }

    /**
     * Show dependent files for a monograph file.
     *
     * @param array $args
     * @param Request $request
     */
    public function dependentFiles($args, $request)
    {
        $submission = $this->getSubmission();
        $submissionFile = Repo::submissionFile()->get((int) $request->getUserVar('submissionFileId'));
        if ($submissionFile->getData('fileStage') !== SubmissionFile::SUBMISSION_FILE_PROOF || $submissionFile->getData('submissionId') != $submission->getId()) {
            return new JSONMessage(false);
        }

        // Check if this is a remote galley
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'submissionId' => $this->getSubmission()->getId(),
            'submissionFile' => $submissionFile,
        ]);
        return new JSONMessage(true, $templateMgr->fetch('controllers/grid/catalogEntry/dependentFiles.tpl'));
    }

    /**
     * Get a publication format from the request param
     */
    public function getRequestedPublicationFormat(Request $request): ?PublicationFormat
    {
        $representationDao = Application::getRepresentationDAO();
        $representationId = $request->getUserVar('representationId');
        return $representationId
            ? $representationDao->getById(
                (int) $representationId,
                $this->getPublication()->getId()
            )
            : null;
    }
}
