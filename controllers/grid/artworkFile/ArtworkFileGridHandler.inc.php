<?php

/**
 * @file controllers/grid/artworkFile/ArtworkFileGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArtworkFileGridHandler
 * @ingroup controllers_grid_artworkFile
 *
 * @brief Handle file grid requests.
 */

import('controllers.grid.GridHandler');
import('controllers.grid.DataObjectGridCellProvider');
import('controllers.grid.artworkFile.ArtworkFileGridRow');

class ArtworkFileGridHandler extends GridHandler {

	/** @var Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function ArtworkFileGridHandler() {
		parent::GridHandler();
	}

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addArtworkFile', 'editArtworkFile', 'uploadArtworkFile', 'updateArtworkFile', 'deleteArtworkFile'));
	}

	/**
	 * Get the monograph associated with this submissionContributor grid.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	//
	// Overridden methods from PKPHandler
	//

	/**
	 * Make sure the monograph exists.
	 * @param $requiredContexts array
	 * @param $request PKPRequest
	 * @return boolean
	 */
	function validate($requiredContexts, $request) {
		// Retrieve and validate the monograph id
		$monographId =& $request->getUserVar('monographId');
		if (!is_numeric($monographId)) return false;

		// Retrieve the monograph associated with this citation grid
		$monographDAO =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDAO->getMonograph($monographId);

		// Monograph and editor validation
		if (!is_a($monograph, 'Monograph')) return false;

		// Validation successful
		$this->_monograph =& $monograph;
		return true;
	}

	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic grid configuration
		$this->setTitle('grid.artworkFile.title');

		// Get the monograph id
		$monograph =& $this->getMonograph();
		assert(is_a($monograph, 'Monograph'));
		$monographId = $monograph->getId();

		// Elements to be displayed in the grid
		$artworkFileDao =& DAORegistry::getDAO('ArtworkFileDAO');
		$artworkFiles =& $artworkFileDao->getByMonographId($monographId);
		$this->setData($artworkFiles);

		// Add grid-level actions
		$router =& $request->getRouter();
		$actionArgs = array('gridId' => $this->getId(), 'monographId' => $monographId);
		$this->addAction(
			new GridAction(
				'addArtworkFile',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addArtworkFile', null, $actionArgs),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		// Columns
		$emptyActions = array();
		$cellProvider = new DataObjectGridCellProvider();
		// Basic grid row configuration
		$this->addColumn(new GridColumn('id', 'grid.libraryFiles.column.files', $emptyActions, 'controllers/grid/gridCellInSpan.tpl', $cellProvider));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return ArtworkFileGridRow
	 */
	function &getRowInstance() {
		$row = new ArtworkFileGridRow();
		return $row;
	}

	//
	// Public Artwork File Grid Actions
	//
	/**
	 * An action to manually add a new artwork file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addArtworkFile(&$args, &$request) {
		// Calling editArtworkFile() with an empty artworkFileId will add
		// a new artwork file.
		$this->editArtworkFile($args, $request);
	}

	/**
	 * Edit an artwork file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editArtworkFile(&$args, &$request) {
		// Identify the artwork file to be updated
		$artworkFile =& $this->_getArtworkFileFromArgs($args);

		// Form handling
		import('controllers.grid.artworkFile.form.ArtworkFileForm');
		$artworkFileForm = new ArtworkFileForm($artworkFile);

		if ($artworkFileForm->isLocaleResubmit()) {
			$artworkFileForm->readInputData();
		} else {
			$artworkFileForm->initData($args, $request);
		}
		$artworkFileForm->display();
	}

	function uploadArtworkFile() {

		$artworkFileDao =& DAORegistry::getDAO('ArtworkFileDAO');
		import('file.MonographFileManager');

		$monograph =& $this->getMonograph();

		$monographFileManager = new MonographFileManager($monograph->getId());
		
		$fileId = null;
		
		if ($monographFileManager->uploadedFileExists('artwork_file')) {
			$fileId = $monographFileManager->uploadArtworkFile('artwork_file');
		}

		if ($fileId) {
			$artworkFile =& $artworkFileDao->newDataObject();
			
			$artworkFile->setFileId($fileId);
			$artworkFile->setMonographId($monograph->getId());
			$artworkFileDao->insertObject($artworkFile);

			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('artworkFile', $artworkFile);
			$templateMgr->display('controllers/grid/artworkFile/form/fileInfo.tpl');
		}
	}

	/**
	 * Edit an artwork file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function updateArtworkFile(&$args, &$request) {
		// Identify the artwork file to be updated
		$artworkFile =& $this->_getArtworkFileFromArgs($args);

		$uploadArtworkFile = $request->getUserVar('uploadArtworkFile');

		if ($uploadArtworkFile) {
			$this->uploadArtworkFile();
			exit;
		}
		// Form handling
		import('controllers.grid.artworkFile.form.ArtworkFileForm');
		$artworkFileForm = new ArtworkFileForm($artworkFile);
		$artworkFileForm->readInputData();
		if ($artworkFileForm->validate()) {
			$artworkFileForm->execute();

			$artworkFile =& $artworkFileForm->getArtworkFile();

			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->initialize($request);
			$row->setData($artworkFile);
			$row->setId($artworkFile->getId());

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false', Locale::translate('error'));
		}
		return $json->getString();
	}

	/**
	 * Delete an artwork file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteArtworkFile(&$args, &$request) {
		// Identify the submissionContributor to be deleted
		$artworkFile =& $this->_getArtworkFileFromArgs($args);

		$artworkFileDAO = DAORegistry::getDAO('ArtworkFileDAO');
		$result = $artworkFileDAO->deleteObject($artworkFile);

		if ($result) {
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('error'));
		}
		return $json->getString();
	}

	//
	// Private helper function
	//
	/**
	 * This will retrieve a submissionContributor object from the
	 * grids data source based on the request arguments.
	 * If no submissionContributor can be found then this will raise
	 * a fatal error.
	 * @param $args array
	 * @param $createIfMissing boolean If this is set to true
	 *  then an artwork file object will be instantiated if no
	 *  artwork file id is in the request.
	 * @return ArtworkFile
	 */
	function &_getArtworkFileFromArgs(&$args, $createIfMissing = false) {

		// Identify the artwork file id and retrieve the
		// corresponding element from the grid's data source.

		if (!isset($args['artworkFileId'])) {
			$artworkFile = null;
		} else {
			$artworkFile =& $this->getRowDataElement($args['artworkFileId']);
		}

		return $artworkFile;
	}
}