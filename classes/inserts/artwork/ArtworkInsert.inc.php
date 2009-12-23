<?php

/**
 * @file inserts/artwork/ArtworkInsert.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArtworkInsert
 * @ingroup inserts
 * 
 * @brief Artwork form insert.
 */

// $Id$

import('inserts.Insert');

class ArtworkInsert extends Insert {
	var $monographId;

	function ArtworkInsert($monographId, $options = 0) {
		parent::Insert($options);
		$this->monographId = $monographId;
	}

	function &listUserVars() {
		$returner = array('artwork', 'artwork_file', 'artwork_caption', 'artwork_credit', 'artwork_copyrightOwner', 'artwork_copyrightOwnerContact', 'artwork_permissionTerms', 
				'artwork_permissionForm', 'artwork_type', 'artwork_otherType', 'artwork_contact', 'artwork_placement', 'artwork_otherPlacement', 'artwork_componentId', 'artwork_placementType');
		return $returner;
	}

	function display(&$form) {

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
	}

	function processEvents(&$form, &$monograph) {
		$eventProcessed = false;

		if (Request::getUserVar('uploadNewArtwork')) {
			$monographArtworkDao =& DAORegistry::getDAO('MonographArtworkDAO');
			import('file.MonographFileManager');
			$eventProcessed = true;

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

		} else if ($artworkId = Request::getUserVar('updateArtwork')) {

			$monographArtworkDao =& DAORegistry::getDAO('MonographArtworkDAO');
			list($artworkId) = array_keys($artworkId);
			import('file.MonographFileManager');
			$eventProcessed = true;


  			$monographFileManager = new MonographFileManager($this->monographId);

			$form->readInputData();
			$artworks = $form->getData('artwork');
			$artwork = $artworks[$artworkId];
			$fileId = $artwork['file_id'];
			$permissionFileId = $artwork['permission_file_id'];

			if ($monographFileManager->uploadedFileExists('artwork_file-'.$artworkId)) {
				$fileId = $monographFileManager->uploadArtworkFile('artwork_file-'.$artworkId, $fileId);
			}

			if ($monographFileManager->uploadedFileExists('artwork_permissionForm-'.$artworkId)) {
				$permissionFileId = $monographFileManager->uploadArtworkFile('artwork_permissionForm-'.$artworkId, $permissionFileId);
			}

			$otherType = $artwork['artwork_type'] == MONOGRAPH_ARTWORK_TYPE_OTHER ? $artwork['artwork_otherType'] : null;
			$otherPlacement = $artwork['artwork_placementType'] == MONOGRAPH_ARTWORK_PLACEMENT_OTHER ? $artwork['artwork_otherPlacement'] : null;

			$artworkFile =& $monographArtworkDao->newDataObject();

			$artworkFile->setId($artwork['artwork_id']);
			$artworkFile->setFileId($fileId);
			$artworkFile->setMonographId($this->monographId);
			$artworkFile->setCaption($artwork['artwork_caption']);
			$artworkFile->setCredit($artwork['artwork_credit']);
			$artworkFile->setCopyrightOwner($artwork['artwork_copyrightOwner']);
			$artworkFile->setCopyrightOwnerContactDetails($artwork['artwork_copyrightOwnerContact']);
			$artworkFile->setPermissionTerms($artwork['artwork_permissionTerms']);
			$artworkFile->setPermissionFileId($permissionFileId);
			$artworkFile->setContactAuthor($artwork['artwork_contact']);
			$artworkFile->setType($artwork['artwork_type']);

			if ($otherType) {
				$artworkFile->setCustomType($otherType);
			} else {
				$artworkFile->setCustomType(null);
			}

			if ($otherPlacement) {
				$artworkFile->setComponentId(null);
				$artworkFile->setPlacement($otherPlacement);
			} else {
				$artworkFile->setPlacement($artwork['artwork_placement']);
				$artworkFile->setComponentId($artwork['artwork_componentId']);
			}

			$monographArtworkDao->updateObject($artworkFile);

		} else if ($removeArtwork = Request::getUserVar('removeArtwork')) {

			$monographArtworkDao =& DAORegistry::getDAO('MonographArtworkDAO');
			list($fileId) = array_keys($removeArtwork);
			$eventProcessed = true;

			$artworkFile =& $monographArtworkDao->getById($fileId);
			if ($artworkFile) $monographArtworkDao->deleteObject($artworkFile);
		}

		return $eventProcessed;
	}
}

?>