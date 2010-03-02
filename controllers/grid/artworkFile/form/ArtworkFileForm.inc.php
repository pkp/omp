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
import('inserts.artwork.ArtworkInsert');

class ArtworkFileForm extends Form {

	var $monograph;
	var $artworkInsert;

	/**
	 * Constructor.
	 */
	function ArtworkFileForm($template, $monograph) {
		parent::Form($template);
		$this->addCheck(new FormValidatorPost($this));
		$this->monograph =& $monograph;
		$this->artworkInsert = new ArtworkInsert($monograph->getMonographId());
	}

	/**
	 * Get a list of fields for which localization should be used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array();
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $this->monograph);

		$monographComponentDao =& DAORegistry::getDAO('MonographComponentDAO');
		$monographArtworkDao =& DAORegistry::getDAO('MonographArtworkDAO');
		$templateMgr =& TemplateManager::getManager();

		$components =& $monographComponentDao->getMonographComponents($this->monographId);
		$artworks =& $monographArtworkDao->getByMonographId($this->monographId);
		$otherComponent = $monographComponentDao->newDataObject();


		$idMap = array();
		for ($i=0, $count=count($components); $i<$count; $i++) {
			$idMap[$components[$i]->getId()] = $i;
		}

		$otherArt = array();
		foreach ($artworks as $artwork) {
			$componentId = $artwork->getComponentId();
			if ($componentId > 0) {
				$components[$idMap[$artwork->getComponentId()]]->addAssocObject($artwork);
			} else {
				$otherArt[] = $artwork;
			}
		}
		$otherComponent->setAssocObjects($otherArt);
		$otherComponent->setTitle(Locale::translate('common.other'), $form->getFormLocale());
		$components[] = $otherComponent;

		$templateMgr->assign_by_ref('components', $components);

		parent::display();
	}

	function processEvents() {
		return $this->artworkInsert->processEvents($this);
	}

	/**
	 * Initialize form data.
	 */
	function initData() {

	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'artwork', 'artwork_file', 'artwork_caption', 'artwork_credit', 'artwork_copyrightOwner', 'artwork_copyrightOwnerContact', 'artwork_permissionTerms', 
			'artwork_permissionForm', 'artwork_type', 'artwork_otherType', 'artwork_contact', 'artwork_placement', 'artwork_otherPlacement', 'artwork_componentId', 'artwork_placementType'
		));
	}

	/**
	 * Save settings.
	 */
	function execute() {
		$monographArtworkDao =& DAORegistry::getDAO('MonographArtworkDAO');
		import('file.MonographFileManager');

		$monographFileManager = new MonographFileManager($this->monographId);

		$fileId = null;

		if ($monographFileManager->uploadedFileExists('artwork_file')) {
			$fileId = $monographFileManager->uploadArtworkFile('artwork_file');
		}

		if ($fileId) {
			$permissionFileId = null;

			if ($monographFileManager->uploadedFileExists('artwork_permissionForm')) {
				$permissionFileId = $monographFileManager->uploadArtworkFile('artwork_permissionForm');
			}

			$form->readInputData();
			$otherType = $form->getData('artwork_type') == MONOGRAPH_ARTWORK_TYPE_OTHER ? $form->getData('artwork_otherType') : null;
			$otherPlacement = $form->getData('artwork_placementType') == MONOGRAPH_ARTWORK_PLACEMENT_OTHER ? $form->getData('artwork_otherPlacement') : null;

			$artworkFile =& $monographArtworkDao->newDataObject();

			$artworkFile->setFileId($fileId);
			$artworkFile->setMonographId($this->monographId);
			$artworkFile->setCaption($form->getData('artwork_caption'));
			$artworkFile->setCredit($form->getData('artwork_credit'));
			$artworkFile->setCopyrightOwner($form->getData('artwork_copyrightOwner'));
			$artworkFile->setCopyrightOwnerContactDetails($form->getData('artwork_copyrightOwnerContact'));
			$artworkFile->setPermissionTerms($form->getData('artwork_permissionTerms'));
			$artworkFile->setPermissionFileId($permissionFileId);
			$artworkFile->setContactAuthor($form->getData('artwork_contact'));
			$artworkFile->setType($form->getData('artwork_type'));

			if ($otherType) {
				$artworkFile->setCustomType($otherType);
			} else {
				$artworkFile->setCustomType(null);
			}

			if ($otherPlacement) {
				$artworkFile->setComponentId(null);
				$artworkFile->setPlacement($otherPlacement);
			} else {
				$artworkFile->setPlacement($form->getData('artwork_placement'));
				$artworkFile->setComponentId($form->getData('artwork_componentId'));
			}

			$monographArtworkDao->insertObject($artworkFile);
		}
	}

}

?>