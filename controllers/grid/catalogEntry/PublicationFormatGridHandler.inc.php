<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests.
 */

use APP\facades\Repo;
use APP\log\SubmissionEventLogEntry;
use APP\notification\NotificationManager;
use APP\publicationFormat\PublicationFormatTombstoneManager;
use APP\template\TemplateManager;
use PKP\controllers\grid\CategoryGridHandler;
use PKP\controllers\grid\GridColumn;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\log\SubmissionFileEventLogEntry;
use PKP\log\SubmissionLog;
use PKP\security\authorization\internal\RepresentationRequiredPolicy;
use PKP\security\authorization\PublicationAccessPolicy;
use PKP\security\Role;

use PKP\submission\PKPSubmission;
use PKP\submissionFile\SubmissionFile;

// import format grid specific classes
import('controllers.grid.catalogEntry.PublicationFormatGridRow');
import('controllers.grid.catalogEntry.PublicationFormatGridCategoryRow');
import('controllers.grid.catalogEntry.PublicationFormatCategoryGridDataProvider');

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
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR],
            [
                'setAvailable', 'editApprovedProof', 'saveApprovedProof',
            ]
        );
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT],
            [
                'addFormat', 'editFormat', 'editFormatTab', 'updateFormat', 'deleteFormat',
                'setApproved', 'setProofFileCompletion', 'selectFiles',
                'identifiers', 'updateIdentifiers', 'clearPubId',
                'dependentFiles', 'editFormatMetadata', 'updateFormatMetadata'
            ]
        );
        $this->addRoleAssignment(
            [Role::ROLE_ID_AUTHOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT],
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
        $this->setSubmission($this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION));
        $this->setPublication($this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION));

        // Load submission-specific translations
        AppLocale::requireComponents(
            LOCALE_COMPONENT_PKP_SUBMISSION,
            LOCALE_COMPONENT_PKP_EDITOR,
            LOCALE_COMPONENT_PKP_USER,
            LOCALE_COMPONENT_PKP_DEFAULT
        );
        $this->setTitle('monograph.publicationFormats');

        // Load submission-specific translations
        AppLocale::requireComponents(
            LOCALE_COMPONENT_APP_SUBMISSION,
            LOCALE_COMPONENT_APP_DEFAULT,
            LOCALE_COMPONENT_APP_EDITOR
        );

        if ($this->getPublication()->getData('status') !== PKPSubmission::STATUS_PUBLISHED) {
            // Grid actions
            $router = $request->getRouter();
            $actionArgs = $this->getRequestArgs();
            $userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);
            $this->_canManage = 0 != count(array_intersect($userRoles, [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT]));
            if ($this->_canManage) {
                $this->addAction(
                    new LinkAction(
                        'addFormat',
                        new AjaxModal(
                            $router->url($request, null, null, 'addFormat', null, $actionArgs),
                            __('grid.action.addFormat'),
                            'modal_add_item'
                        ),
                        __('grid.action.addFormat'),
                        'add_item'
                    )
                );
            }
        }

        // Columns
        import('controllers.grid.catalogEntry.PublicationFormatGridCellProvider');
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
     * @param PKPRequest $request
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
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function editFormat($args, $request)
    {
        $representationDao = Application::getRepresentationDAO();
        $representationId = $request->getUserVar('representationId');
        $representation = $representationDao->getById(
            $representationId,
            $this->getPublication()->getId()
        );
        // Check if this is a remote galley
        $remoteURL = isset($representation) ? $representation->getRemoteURL() : null;
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('submissionId', $this->getSubmission()->getId());
        $templateMgr->assign('publicationId', $this->getPublication()->getId());
        $templateMgr->assign('representationId', $representationId);
        $templateMgr->assign('remoteRepresentation', $remoteURL);

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
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function editFormatTab($args, $request)
    {
        $representationDao = Application::getRepresentationDAO();
        $representation = $representationDao->getById(
            $request->getUserVar('representationId'),
            $this->getPublication()->getId()
        );

        import('controllers.grid.catalogEntry.form.PublicationFormatForm');
        $publicationFormatForm = new PublicationFormatForm($this->getSubmission(), $representation, $this->getPublication());
        $publicationFormatForm->initData();

        return new JSONMessage(true, $publicationFormatForm->fetch($request));
    }

    /**
     * Update a format
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function updateFormat($args, $request)
    {
        $representationDao = Application::getRepresentationDAO();
        $representation = $representationDao->getById(
            $request->getUserVar('representationId'),
            $this->getPublication()->getId()
        );

        import('controllers.grid.catalogEntry.form.PublicationFormatForm');
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
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function deleteFormat($args, $request)
    {
        $context = $request->getContext();
        $submission = $this->getSubmission();
        $representationDao = Application::getRepresentationDAO();
        $representation = $representationDao->getById(
            $request->getUserVar('representationId'),
            $this->getPublication()->getId()
        );

        if (!$request->checkCSRF() || !$representation) {
            return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
        }

        Services::get('publicationFormat')->deleteFormat($representation, $submission, $context);

        $currentUser = $request->getUser();
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, ['contents' => __('notification.removedPublicationFormat')]);

        return DAO::getDataChangedEvent();
    }

    /**
     * Set a format's "approved" state
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function setApproved($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);
        $representationDao = Application::getRepresentationDAO();

        if (!$representation) {
            return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
        }

        $confirmationText = __('grid.catalogEntry.approvedRepresentation.removeMessage');
        if ($request->getUserVar('newApprovedState')) {
            $confirmationText = __('grid.catalogEntry.approvedRepresentation.message');
        }
        import('lib.pkp.controllers.grid.pubIds.form.PKPAssignPublicIdentifiersForm');
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

        // log the state changing of the format.
        SubmissionLog::logEvent(
            $request,
            $this->getSubmission(),
            $newApprovedState ? SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_PUBLISH : SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_UNPUBLISH,
            $newApprovedState ? 'submission.event.publicationFormatPublished' : 'submission.event.publicationFormatUnpublished',
            ['publicationFormatName' => $representation->getLocalizedName()]
        );

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
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function setAvailable($args, $request)
    {
        $context = $request->getContext();
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */
        $publicationFormat = $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);

        if (!$publicationFormat) {
            return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
        }

        $newAvailableState = (int) $request->getUserVar('newAvailableState');
        $publicationFormat->setIsAvailable($newAvailableState);
        $publicationFormatDao->updateObject($publicationFormat);

        // log the state changing of the format.
        SubmissionLog::logEvent(
            $request,
            $this->getSubmission(),
            $newAvailableState ? SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_AVAILABLE : SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_UNAVAILABLE,
            $newAvailableState ? 'submission.event.publicationFormatMadeAvailable' : 'submission.event.publicationFormatMadeUnavailable',
            ['publicationFormatName' => $publicationFormat->getLocalizedName()]
        );

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
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function editApprovedProof($args, $request)
    {
        $this->initialize($request);
        $submission = $this->getSubmission();
        $representation = $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);

        import('controllers.grid.files.proof.form.ApprovedProofForm');
        $approvedProofForm = new ApprovedProofForm($submission, $representation, $request->getUserVar('submissionFileId'));
        $approvedProofForm->initData();

        return new JSONMessage(true, $approvedProofForm->fetch($request));
    }

    /**
     * Save an approved proof.
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function saveApprovedProof($args, $request)
    {
        $submission = $this->getSubmission();
        $representation = $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);

        import('controllers.grid.files.proof.form.ApprovedProofForm');
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
     * @param PKPRequest $request
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
     * @param PKPRequest $request
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
        if ($submissionFile && $submissionFile->getData('assocType') == ASSOC_TYPE_REPRESENTATION) {
            import('lib.pkp.controllers.grid.pubIds.form.PKPAssignPublicIdentifiersForm');
            $formTemplate = $this->getAssignPublicIdentifiersFormTemplate();
            $assignPublicIdentifiersForm = new PKPAssignPublicIdentifiersForm($formTemplate, $submissionFile, $request->getUserVar('approval'), $confirmationText);
            if (!$request->getUserVar('confirmed')) {
                // Display assign pub ids modal
                $assignPublicIdentifiersForm->initData();
                return new JSONMessage(true, $assignPublicIdentifiersForm->fetch($request));
            }
            if ($request->getUserVar('approval')) {
                // Asign pub ids
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
            SubmissionFileLog::logEvent($request, $submissionFile, SubmissionFileEventLogEntry::SUBMISSION_LOG_FILE_SIGNOFF_SIGNOFF, 'submission.event.signoffSignoff', ['file' => $submissionFile->getLocalizedData('name'), 'name' => $user->getFullName(), 'username' => $user->getUsername()]);

            return DAO::getDataChangedEvent();
        }

        return new JSONMessage(false);
    }

    /**
     * Show the form to allow the user to select files from previous stages
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function selectFiles($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);

        import('lib.pkp.controllers.grid.files.proof.form.ManageProofFilesForm');
        $manageProofFilesForm = new ManageProofFilesForm($this->getSubmission()->getId(), $this->getPublication()->getId(), $representation->getId());
        $manageProofFilesForm->initData();
        return new JSONMessage(true, $manageProofFilesForm->fetch($request));
    }

    /**
     * Load a form to edit a format's metadata
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function editFormatMetadata($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);

        import('controllers.grid.catalogEntry.form.PublicationFormatMetadataForm');
        $publicationFormatForm = new PublicationFormatMetadataForm($this->getSubmission(), $this->getPublication(), $representation);
        $publicationFormatForm->initData();

        return new JSONMessage(true, $publicationFormatForm->fetch($request));
    }

    /**
     * Save a form to edit format's metadata
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function updateFormatMetadata($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);

        import('controllers.grid.catalogEntry.form.PublicationFormatMetadataForm');
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
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function identifiers($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);

        import('controllers.tab.pubIds.form.PublicIdentifiersForm');
        $form = new PublicIdentifiersForm($representation);
        $form->initData();
        return new JSONMessage(true, $form->fetch($request));
    }

    /**
     * Update pub ids
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function updateIdentifiers($args, $request)
    {
        $representation = $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);

        import('controllers.tab.pubIds.form.PublicIdentifiersForm');
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
     * @param PKPRequest $request
     *
     * @return JSONMessage JSON object
     */
    public function clearPubId($args, $request)
    {
        if (!$request->checkCSRF()) {
            return new JSONMessage(false);
        }

        $representation = $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);

        import('controllers.tab.pubIds.form.PublicIdentifiersForm');
        $form = new PublicIdentifiersForm($representation);
        $form->clearPubId($request->getUserVar('pubIdPlugIn'));
        return new JSONMessage(true);
    }

    /**
     * Show dependent files for a monograph file.
     *
     * @param array $args
     * @param PKPRequest $request
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
}
