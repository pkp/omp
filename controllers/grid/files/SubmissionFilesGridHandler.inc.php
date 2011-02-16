<?php

/**
 * @file controllers/grid/files/SubmissionFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridHandler
 * @ingroup controllers_grid_files
 *
 * @brief Handle submission file grid requests.
 */

// The percentage of characters that the name of a file
// has to share with an existing file for it to be
// considered as a revision of that file.
define('SUBMISSION_MIN_SIMILARITY_OF_REVISION', 70);

// Import UI base classes.
import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.linkAction.request.WizardModal');
import('lib.pkp.classes.linkAction.request.RedirectAction');

// Import submission files grid specific classes.
import('controllers.grid.files.SubmissionFilesGridRow');
import('controllers.grid.files.SubmissionFilesGridCellProvider');

// Import monograph file class which contains the MONOGRAPH_FILE_* constants.
import('classes.monograph.MonographFile');

class SubmissionFilesGridHandler extends GridHandler {
	/** @var integer */
	var $_fileStage;

	/** @var boolean */
	var $_canAdd;

	/** @var boolean */
	var $_revisionOnly;

	/** @var boolean */
	var $_canDownloadAll;

	/** @var array */
	var $_selectedFileIds;

	/** @var string */
	var $_selectName;

	/** @var array */
	var $_additionalActionArgs;

	/**
	 * Constructor
	 * @param $fileStage integer the workflow stage
	 *  file storage that this grid operates on. One of
	 *  the MONOGRAPH_FILE_* constants.
	 * @param $canAdd boolean whether the grid will contain
	 *  an "add file" button.
	 * @param $revisionOnly boolean whether this grid
	 *  allows uploading of revisions only or whether also
	 *  new files can be uploaded.
	 * @param $isSelectable boolean whether this grid displays
	 *  checkboxes on each grid row that allows files to be selected
	 *  as form inputs
	 * @param $canDownloadAll boolean whether the user can download
	 *  all files in the grid as a compressed file
	 */
	function SubmissionFilesGridHandler($fileStage, $canAdd = true, $revisionOnly = false, $isSelectable = false, $canDownloadAll = false) {
		assert(is_numeric($fileStage) && $fileStage > 0);
		$this->_fileStage = (int)$fileStage;
		$this->_canAdd = (boolean)$canAdd;
		$this->_revisionOnly = (boolean)$revisionOnly;
		$this->_isSelectable = (boolean)$isSelectable;
		$this->_canDownloadAll = (boolean)$canDownloadAll;

		parent::GridHandler();
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function &getMonograph() {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		// We assume proper authentication by sub-classes.
		assert(is_a($monograph, 'Monograph'));
		return $monograph;
	}

	/**
	 * Get the workflow stage file storage that this
	 * grid operaties on. One of the MONOGRAPH_FILE_*
	 * constants.
	 * @return integer
	 */
	function getFileStage() {
		return $this->_fileStage;
	}

	/**
	 * Does this grid allow the addition of files
	 * or revisions?
	 * @return boolean
	 */
	function canAdd() {
		return $this->_canAdd;
	}

	/**
	 * Does this grid only allow revisions and no new files?
	 * @return boolean
	 */
	function revisionOnly() {
		return $this->_revisionOnly;
	}

	/**
	 * Does this grid have a checkbox column?
	 * @return boolean
	 */
	function isSelectable() {
		return $this->_isSelectable;
	}

	/**
	 * Set the selected file IDs
	 * @param $selectedFileIds array
	 */
	function setSelectedFileIds($selectedFileIds) {
	    $this->_selectedFileIds = $selectedFileIds;
	}

	/**
	 * Get the selected file IDs
	 * @return array
	 */
	function getSelectedFileIds() {
	    return $this->_selectedFileIds;
	}

	/**
	 * Set the selection name
	 * @param $selectName string
	 */
	function setSelectName($selectName) {
	    $this->_selectName = $selectName;
	}

	/**
	 * Get the selection name
	 * @return string
	 */
	function getSelectName() {
	    return $this->_selectName;
	}


	/**
	 * Can the user download all files as an archive?
	 * @return boolean
	 */
	function canDownloadAll() {
		return $this->_canDownloadAll;
	}

	/**
	 * Set the additional action argument array
	 * @param $additionalActionArgs array
	 */
	function setAdditionalActionArgs($additionalActionArgs) {
	    $this->_additionalActionArgs = $additionalActionArgs;
	}

	/**
	 * Get the additional action argument array
	 * @return array
	 */
	function getAdditionalActionArgs() {
	    return $this->_additionalActionArgs;
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @param $request PKPRequest
	 * @param $cellProvider GridCellProvider
	 * @param $additionalActionArgs array Additional key/value pairs to add to URLs
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, &$cellProvider, $additionalActionArgs = array()) {
		parent::initialize($request);
		$router =& $request->getRouter();
		$monograph =& $this->getMonograph();

		// Set any additional action args coming in from subclasses; Merge them with the monograph ID
		$this->setAdditionalActionArgs($additionalActionArgs);
		$actionArgs = array_merge(array('monographId' => $monograph->getId()), $additionalActionArgs);

		// Load translations.
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON));

		// Add grid-level actions.
		if($this->canAdd()) {
			$this->addAction(
				new LinkAction(
					'addFile',
					new WizardModal(
						$router->url($request, null, null, 'addFile', null, $actionArgs),
						$this->revisionOnly() ? 'submission.submit.uploadRevision' : 'submission.submit.uploadSubmissionFile',
						'fileManagement'
					),
					$this->revisionOnly() ? 'submission.addRevision' : 'submission.addFile',
					'add'
				)
			);
		}

		// Test whether the tar binary is available for the export to work, if so, add 'download all' grid action
		$tarBinary = Config::getVar('cli', 'tar');
		if ($this->canDownloadAll() && !empty($tarBinary) && file_exists($tarBinary) && isset($this->_data)) {
			$this->addAction(
				new LinkAction(
					'downloadAll',
					new RedirectAction($router->url($request, null, null, 'downloadAllFiles', null, $actionArgs)),
					'submission.files.downloadAll',
					'getPackage'
				)
			);
		}

		// Add extra columns to the grid
		if($this->isSelectable()) {
			$this->addColumn(new GridColumn('select',
				'common.select',
				null,
				'controllers/grid/gridRowSelectInput.tpl',
				$cellProvider,
				array('selectedFileIds' => $this->getSelectedFileIds(), 'selectName' => $this->getSelectName())
			));
		}
		// Default columns
		$this->addColumn(new GridColumn('name',	'common.name', null, 'controllers/grid/gridCell.tpl', $cellProvider));
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 */
	function &getRowInstance() {
		$row = new SubmissionFilesGridRow();
		return $row;
	}

	/**
	 * @see GridHandler::fetchGrid()
	 */
	function fetchGrid($args, &$request) {
		// Merge the monograph ID with any other arguments we need to put in the request
		$monograph =& $this->getMonograph();
		$fetchParams = array_merge(
							array('monographId' => $monograph->getId()),
							$this->getAdditionalActionArgs()
					   );

		return parent::fetchGrid($args, $request, $fetchParams);
	}


	//
	// Public handler actions
	//
	/**
	 * An action to add a new file or revision.
	 * Displays the file upload wizard modal which in
	 * turn will request the file upload wizard.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function addFile($args, &$request) {
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('additionalActionArgs', $this->getAdditionalActionArgs());

		// Assign the monograph.
		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());

		// Assign the pre-configured revised file id (if any).
		$revisedFileId = $this->_getRevisedFileIdFromRequest($request);
		$templateMgr->assign('revisedFileId', $revisedFileId);

		// Render the file upload wizard.
		$json = new JSON(true, $templateMgr->fetch('controllers/grid/files/submissionFiles/fileUploadWizard.tpl'));
		return $json->getString();
	}

	/**
	 * Render the file upload form in its initial state.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function displayFileUploadForm($args, &$request) {
		// Instantiate, configure and initialize the form.
		import('controllers.grid.files.form.SubmissionFilesUploadForm');
		$monograph =& $this->getMonograph();
		$revisedFileId = $this->_getRevisedFileIdFromRequest($request);
		$fileForm = new SubmissionFilesUploadForm($request, $monograph->getId(), $this->getFileStage(), $this->revisionOnly(), $revisedFileId, $this->getAdditionalActionArgs());
		$fileForm->initData($args, $request);

		// Render the form.
		$json = new JSON(true, $fileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a file and render the modified upload wizard.
	 * @param $args array
	 * @param $request Request
	 * @param fileModifyCallback callable callback function used to further modify the file object
	 * @return string a serialized JSON object
	 */
	function uploadFile($args, &$request, $fileModifyCallback = null) {
		// Instantiate the file upload form.
		$monograph =& $this->getMonograph();
		import('controllers.grid.files.form.SubmissionFilesUploadForm');
		$uploadForm = new SubmissionFilesUploadForm($request, $monograph->getId(), $this->getFileStage(), $this->revisionOnly(), null, $this->getAdditionalActionArgs());
		$uploadForm->readInputData();

		// Validate the form and upload the file.
		if ($uploadForm->validate($request)) {
			if (is_a($uploadedFile =& $uploadForm->execute(), 'MonographFile')) {
				// Let the callback make any further modifications to the file (e.g. set assoc_id)
				if(isset($fileModifyCallback)) {
					call_user_func_array($fileModifyCallback, array($uploadedFile));
				}
				$uploadedFileInfo = $this->_getUploadedFileInfo($uploadedFile);

				// If no revised file id was given then try out whether
				// the user maybe accidentally didn't identify this file as a revision.
				if (!$uploadForm->getRevisedFileId()) {
					$revisedFileId = $this->_checkForRevision($uploadedFile, $uploadForm->getMonographFiles());
					if ($revisedFileId) {
						// Instantiate the revision confirmation form.
						import('controllers.grid.files.form.SubmissionFilesUploadConfirmationForm');
						$confirmationForm = new SubmissionFilesUploadConfirmationForm($request, $monograph->getId(), $this->getFileStage(), $revisedFileId, $uploadedFile, $this->getAdditionalActionArgs());
						$confirmationForm->initData($args, $request);

						// Render the revision confirmation form.
						$json = new JSON(true, $confirmationForm->fetch($request), false, '0', $uploadedFileInfo);
						return $json->getString();
					}
				}

				// Advance to the next step (i.e. meta-data editing).
				$json = new JSON(true, '', false, '0', $uploadedFileInfo);
			} else {
				$json = new JSON(false, Locale::translate('common.uploadFailed'));
			}
		} else {
			$json = new JSON(false, array_pop($uploadForm->getErrorsArray()));
		}
		return $json->getString();
	}

	/**
	 * Confirm that the uploaded file is a revision of an
	 * earlier uploaded file.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function confirmRevision($args, &$request) {
		// Instantiate the revision confirmation form.
		$monograph =& $this->getMonograph();
		import('controllers.grid.files.form.SubmissionFilesUploadConfirmationForm');
		$confirmationForm = new SubmissionFilesUploadConfirmationForm($request, $monograph->getId(), $this->getFileStage(), null, null, $this->getAdditionalActionArgs());
		$confirmationForm->readInputData();

		// Validate the form and revise the file.
		if ($confirmationForm->validate($request)) {
			if (is_a($uploadedFile =& $confirmationForm->execute(), 'MonographFile')) {
				// Go to the meta-data editing step.
				$json = new JSON(true, '', false, '0', $this->_getUploadedFileInfo($uploadedFile));
			} else {
				$json = new JSON(false, Locale::translate('common.uploadFailed'));
			}
		} else {
			$json = new JSON(false, array_pop($confirmationForm->getErrorsArray()));
		}
		return $json->getString();
	}

	/**
	 * Delete a file or revision
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function deleteFile($args, &$request) {
		$fileId = (int)$request->getUserVar('fileId');

		$success = false;
		if($fileId) {
			// Delete all revisions or only one?
			$revision = $request->getUserVar('revision')? (int)$request->getUserVar('revision') : null;

			// Delete the file/revision but only when it belongs to the authorized monograph
			// and to the right file stage.
			$monograph =& $this->getMonograph();
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			if ($revision) {
				$success = (boolean)$submissionFileDao->deleteRevisionById($fileId, $revision, $this->getFileStage(), $monograph->getId());
			} else {
				$success = (boolean)$submissionFileDao->deleteAllRevisionsById($fileId, $this->getFileStage(), $monograph->getId());
			}
		}

		if ($success) {
			return $this->elementDeleted($fileId);
		} else {
			$json = new JSON(false);
			return $json->getString();
		}
	}

	/**
	 * Edit the metadata of the latest revision of
	 * the requested submission file.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function editMetadata($args, &$request) {
		$metadataForm =& $this->_getMetadataForm($request);
		if ($metadataForm->isLocaleResubmit()) {
			$metadataForm->readInputData();
		} else {
			$metadataForm->initData($args, $request);
		}
		$json = new JSON(true, $metadataForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the metadata of the latest revision of
	 * the requested submission file
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function saveMetadata($args, &$request) {
		$metadataForm =& $this->_getMetadataForm($request);
		$metadataForm->readInputData();
		if ($metadataForm->validate()) {
			$metadataForm->execute($args, $request);
			$submissionFile = $metadataForm->getSubmissionFile();
			return $this->elementAdded($submissionFile->getFileId());
		} else {
			$json = new JSON(false, $metadataForm->fetch($request));
		}
		return $json->getString();
	}

	/**
	 * Display the final tab of the modal
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function finishFileSubmission($args, &$request) {
		$monograph =& $this->getMonograph();
		$fileId = (int)$request->getUserVar('fileId');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monograph->getId());
		$templateMgr->assign('fileId', $fileId);

		$json = new JSON(true, $templateMgr->fetch('controllers/grid/files/submissionFiles/form/fileSubmissionComplete.tpl'));
		return $json->getString();
	}

	/**
	 * Download a file
	 * @param $args array
	 * @param $request Request
	 */
	function downloadFile($args, &$request) {
		$monographId = (int)$request->getUserVar('monographId');
		$fileId = (int)$request->getUserVar('fileId');
		$revision = (int)$request->getUserVar('fileRevision');

		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFile($monographId, $fileId, ($revision ? $revision : null));
	}

	/**
	 * Download all of the monograph files as one compressed file
	 * @param $args array
	 * @param $request Request
	 */
	function downloadAllFiles($args, &$request) {
		$monographId = (int)$request->getUserVar('monographId');

		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFilesArchive($monographId, $this->_data);
	}

	/**
	 * View a file
	 * @param $args array
	 * @param $request Request
	 */
	function viewFile($args, &$request) {
		$monographId = (int)$request->getUserVar('monographId');
		$fileId = (int)$request->getUserVar('fileId');
		$revision = (int)$request->getUserVar('fileRevision');

		import('classes.file.MonographFileManager');
		MonographFileManager::viewFile($monographId, $fileId, ($revision ? $revision : null));
	}


	//
	// Protected helper methods
	//
	/**
	 * Function that can be called by sub-classes to load the
	 * files into the grid.
	 */
	function loadMonographFiles() {
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monograph =& $this->getMonograph();
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), $this->getFileStage());
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] = $monographFile;
		}
		$this->setData($rowData);
	}


	//
	// Private helper methods
	//
	/**
	 * Find out whether we got a revised file pre-configured
	 * in the request and return it's id or null.
	 * @param $request Request
	 * @return integer
	 */
	function _getRevisedFileIdFromRequest(&$request) {
		// The revised file will be non-zero if we revise a
		// single existing file.
		if ($this->revisionOnly()) {
			$revisedFileId = $request->getUserVar('revisedFileId') ? (int)$request->getUserVar('revisedFileId') : null;
		} else {
			$revisedFileId = null;
		}
		return $revisedFileId;
	}

	/**
	 * Retrieve the requested meta-data form.
	 * @param $request Request
	 * @return SubmissionFilesMetadataForm
	 */
	function &_getMetadataForm(&$request) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getMonograph();

		// Retrieve the latest revision of the requested monograph file.
		$fileId = (int)$request->getUserVar('fileId');
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFile =& $submissionFileDao->getLatestRevision($fileId, $this->getFileStage(), $monograph->getId());
		if (!is_a($submissionFile, 'MonographFile')) fatalError('Invalid file id!');

		// Import the meta-data form based on the file implementation.
		if (is_a($submissionFile, 'ArtworkFile')) {
			import('controllers.grid.files.form.SubmissionFilesArtworkMetadataForm');
			$metadataForm = new SubmissionFilesArtworkMetadataForm($submissionFile, $this->getAdditionalActionArgs());
		} else {
			import('controllers.grid.files.form.SubmissionFilesMetadataForm');
			$metadataForm = new SubmissionFilesMetadataForm($submissionFile, $this->getAdditionalActionArgs());
		}

		return $metadataForm;
	}

	/**
	 * Check if the uploaded file has a similar name to an existing
	 * file which would then be a candidate for a revised file.
	 * @param $uploadedFile MonographFile
	 * @param $monographFiles array a list of monograph files to
	 *  check the uploaded file against.
	 * @return integer the if of the possibly revised file or null
	 *  if no matches were found.
	 */
	function &_checkForRevision(&$uploadedFile, &$monographFiles) {
		// Get the file name.
		$uploadedFileName = $uploadedFile->getOriginalFileName();

		// Start with the minimal required similarity.
		$minPercentage = SUBMISSION_MIN_SIMILARITY_OF_REVISION;

		// Find out whether one of the files belonging to the current
		// file stage matches the given file name.
		$possibleRevisedFileId = null;
		$matchedPercentage = 0;
		foreach ($monographFiles as $monographFile) { /* @var $monographFile MonographFile */
			// Do not consider the uploaded file itself.
			if ($uploadedFile->getFileId() == $monographFile->getFileId()) continue;

			// Test whether the current monograph file is similar
			// to the uploaded file.
			similar_text($uploadedFileName, $monographFile->getOriginalFileName(), &$matchedPercentage);
			if($matchedPercentage > $minPercentage) {
				// We found a file that might be a possible revision.
				$possibleRevisedFileId = $monographFile->getFileId();

				// Reset the min percentage to this comparison's precentage
				// so that only better matches will be considered from now on.
				$minPercentage = $matchedPercentage;
			}
		}

		// Return the id of the file that we found similar.
		return $possibleRevisedFileId;
	}

	/**
	 * Create an array that describes an uploaded file which can
	 * be used in a JSON response.
	 * @param MonographFile $uploadedFile
	 * @return array
	 */
	function &_getUploadedFileInfo(&$uploadedFile) {
		$uploadedFileInfo = array(
			'uploadedFile' => array(
				'fileId' => $uploadedFile->getFileId(),
				'revision' => $uploadedFile->getRevision()
			)
		);
		return $uploadedFileInfo;
	}
}