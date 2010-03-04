<?php
 
/**
 * @file controllers/grid/artworkFile/ArtworkFileForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArtworkFileForm
 * @ingroup controllers_grid_artworkFile_form
 *
 * @brief Form for uploading artwork files and editing file metadata.
 */


import('form.Form');

class ArtworkFileForm extends Form {

	/** @var ArtworkFile */
	var $_artworkFile;

	/**
	 * Constructor.
	 */
	function ArtworkFileForm($artworkFile) {
		parent::Form('controllers/grid/artworkFile/form/artworkFileForm.tpl');

		$this->_artworkFile =& $artworkFile;
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Get the artwork file
	 * @return ArtworkFile
	 */
	function &getArtworkFile() {
		return $this->_artworkFile;
	}

	/**
	 * Display the form.
	 */
	function display() {

		$monographComponentDao =& DAORegistry::getDAO('MonographComponentDAO');
		$templateMgr =& TemplateManager::getManager();
		$artworkFile =& $this->getArtworkFile();

		// artwork can be grouped by monograph component
		$components =& $artworkFile ? $monographComponentDao->getMonographComponents($artworkFile->getMonographId()) : null;
		$templateMgr->assign_by_ref('components', $components);

		parent::display();
	}

	/**
	 * Initialize form data.
	 */
	function initData(&$args, &$request) {

		$this->_data['artworkFile'] =& $this->getArtworkFile();

		// grid related data
		$this->_data['gridId'] = $args['gridId'];
		$this->_data['rowId'] = isset($args['rowId']) ? $args['rowId'] : null;
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'artwork', 'artwork_file', 'artwork_caption', 'artwork_credit', 'artwork_copyrightOwner', 'artwork_copyrightOwnerContact', 'artwork_permissionTerms', 'monographId', 
			'artwork_permissionForm', 'artwork_type', 'artwork_otherType', 'artwork_contact', 'artwork_placement', 'artwork_otherPlacement', 'artwork_componentId', 'artwork_placementType'
		));
		$this->readUserVars(array('gridId', 'artworkFileId'));
	}

	/**
	 * Save settings.
	 */
	function execute() {
		$artworkFileDao =& DAORegistry::getDAO('ArtworkFileDAO');

		// manage artwork permissions file
		import('file.MonographFileManager');
		$monographId = $this->getData('monographId');
		$monographFileManager = new MonographFileManager($monographId);

		$artworkFile =& $this->_artworkFile;
		$artworkFileExists = false;
		$permissionFileId = null;

		if ($artworkFile) {
			$artworkFileExists = true;
		} else {
			$artworkFile =& $artworkFileDao->newDataObject();
		}

		if ($monographFileManager->uploadedFileExists('artwork_permissionForm')) {
			$permissionFileId = $monographFileManager->uploadArtworkFile('artwork_permissionForm');
		}

		$otherType = $this->getData('artwork_type') == MONOGRAPH_ARTWORK_TYPE_OTHER ? $this->getData('artwork_otherType') : null;
		$otherPlacement = $this->getData('artwork_placementType') == MONOGRAPH_ARTWORK_PLACEMENT_OTHER ? $this->getData('artwork_otherPlacement') : null;

		$artworkFile->setFileId($this->getData('artworkFileId'));
		$artworkFile->setMonographId($monographId);
		$artworkFile->setCaption($this->getData('artwork_caption'));
		$artworkFile->setCredit($this->getData('artwork_credit'));
		$artworkFile->setCopyrightOwner($this->getData('artwork_copyrightOwner'));
		$artworkFile->setCopyrightOwnerContactDetails($this->getData('artwork_copyrightOwnerContact'));
		$artworkFile->setPermissionTerms($this->getData('artwork_permissionTerms'));
		$artworkFile->setPermissionFileId($permissionFileId);
		$artworkFile->setContactAuthor($this->getData('artwork_contact'));
		$artworkFile->setType($this->getData('artwork_type'));

		if ($otherType) {
			$artworkFile->setCustomType($otherType);
		} else {
			$artworkFile->setCustomType(null);
		}

		if ($otherPlacement) {
			$artworkFile->setComponentId(null);
			$artworkFile->setPlacement($otherPlacement);
		} else {
			$artworkFile->setPlacement($this->getData('artwork_placement'));
			$artworkFile->setComponentId($this->getData('artwork_componentId'));
		}

		if ($artworkFileExists) {
			$artworkFileDao->updateObject($artworkFile);
		} else {
			$artworkFileDao->insertObject($artworkFile);
		}

		$this->_artworkFile = $artworkFile;

		return $artworkFile->getId();
	}

}

?>