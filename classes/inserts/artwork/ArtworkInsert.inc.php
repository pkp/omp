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

class ArtworkInsert {
	var $monographId;

	function ArtworkInsert($monographId, $options = 0) {
		parent::Insert($options);
		$this->monographId = $monographId;
	}

	function &listUserVars() {
		$returner = array('artworkFile', 'type', 'componentId', 'identifier');
		return $returner;
	}

	function display(&$form) {

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$templateMgr =& TemplateManager::getManager();

		$artworks =& $monographFileDao->getMonographFilesByAssocId(
								null, 
								MONOGRAPH_FILE_ARTWORK, 
								$this->monographId
							);

		$templateMgr->assign_by_ref('artworks', $artworks);

	}

	function execute(&$form, &$monograph) {
		import('monograph.MonographArtworkFile');
		import('file.MonographFileManager');

		$fileId = null;
		$artworkFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFileManager = new MonographFileManager($monograph->getMonographId());

		if ($monographFileManager->uploadedFileExists('artworkFile')) {
			$fileId = $monographFileManager->uploadArtworkFile('artworkFile', null);
		}

		if ($fileId) {
			$newEntry = true;
			$artworkFile =& $artworkFileDao->getMonographArtworkFile($fileId);
		} else {
			$newEntry = false;
			$artworkFile = new MonographArtworkFile();
		}

		$form->readInputData();

		$artworkFile->setFileId($fileId);
		$artworkFile->setPermission(0);
		$artworkFile->setPermissionFileId(0);
		$artworkFile->setMonographComponentId($form->getData('componentId'));
		$artworkFile->setIdentifier($form->getData('identifier'));
		$artworkFile->setSeq(REALLY_BIG_NUMBER);

		if ($newEntry) {
			$artworkFileDao->insertMonographArtworkFile($artworkFile);
		} else {
			$artworkFileDao->updateMonographArtworkFile($artworkFile);
		}

		return $fileId;
	}
	
	function processEvents(&$form, &$monograph) {
		$eventProcessed = false;

		if (Request::getUserVar('uploadNewArtwork')) {
			import('monograph.MonographArtworkFile');
			import('file.MonographFileManager');

			$eventProcessed = true;
			$artworkFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFileManager = new MonographFileManager($monograph->getMonographId());

			$fileId = null;

			if ($monographFileManager->uploadedFileExists('artworkFile')) {
				$fileId = $monographFileManager->uploadArtworkFile('artworkFile', null);
			}

			if ($fileId) {
				$artworkFile =& $artworkFileDao->getMonographArtworkFile($fileId);
			} else {
				$artworkFile = new MonographArtworkFile();
			}

			$form->readInputData();

			$artworkFile->setFileId($fileId);
			$artworkFile->setPermission(0);
			$artworkFile->setPermissionFileId(0);
			$artworkFile->setMonographComponentId($form->getData('componentId'));
			$artworkFile->setIdentifier($form->getData('identifier'));
			$artworkFile->setSeq(REALLY_BIG_NUMBER);

			if ($fileId) {
				$artworkFileDao->insertMonographArtworkFile($artworkFile);
			} else {
				$artworkFileDao->updateMonographArtworkFile($artworkFile);
			}

		} else if ($removeArtwork = Request::getUserVar('removeArtwork')) {
			import('file.MonographFileManager');

			$eventProcessed = true;
			list($fileId) = array_keys($removeArtwork);
			$monographFileManager = new MonographFileManager($monograph->getMonographId());

			$monographFileManager->deleteFile($fileId);
		}

		return $eventProcessed;
	}
}

?>