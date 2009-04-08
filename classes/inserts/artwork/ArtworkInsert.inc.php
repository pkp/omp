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

class ArtworkInsert
{

	var $options;
	var $monograph;

	function ArtworkInsert($monograph, $options = 0) {
		$this->monograph =& $monograph;
		$this->options = $options;
	}
	function &listUserVars() {
		$returner = array('artworkFile', 'type', 'componentId', 'identifier');
		return $returner;
	}
	function initData(&$form) {

/*		if (isset($this->monograph)) {

			$insertReturns =& $this->contributorInsert->initData($form);
			$gnash = $insertReturns['lookup'];

			$components =& $this->monograph->getMonographComponents();
			$formComponents = array();

			import('monograph.Author');
			for ($i=0, $count=count($components); $i < $count; $i++) {
				$cas = array();
				foreach ($components[$i]->getMonographComponentAuthors() as $ca) {
					array_push($cas, array(
								'authorId' => $gnash[$ca->getAuthorId()],
								'email' => $ca->getEmail(),
								'firstName' => $ca->getFirstName(),
								'lastName' => $ca->getLastName()
							)
						);
				}
				array_push(
					$formComponents,
					array (
						'title' => $components[$i]->getTitle(null),
						'authors' => $cas
					)
				);
			}
			$returner = array ('components' => $formComponents, 
						'contributors'=>$insertReturns['contributors'],
						'newContributor'=>$insertReturns['newContributor'], 'primaryContact'=>$insertReturns['primaryContact']
					);
			return $returner;
		}
		return array();*/
	}
	function display(&$form) {
		
		$templateMgr =& TemplateManager::getManager();
		$press =& Request::getPress();

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$artworks =& $monographFileDao->getMonographFilesByAssocId(null, MONOGRAPH_FILE_ARTWORK, $this->monograph->getMonographId());

		$templateMgr->assign_by_ref('artworks', $artworks);

	}
	function getLocaleFieldNames() {
		$fields = array();
		return $fields;
	}
	function execute(&$form, &$monograph) {
		$press =& Request::getPress();

		import('monograph.MonographArtworkFile');
		$artworkFileDao =& DAORegistry::getDAO('MonographFileDAO');

		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monograph->getMonographId());
		$fileId = null;

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

		$artworkFile->setSeq(0);

		if ($newEntry) {
			$artworkFileDao->insertMonographArtworkFile($artworkFile);
		} else {
			$artworkFileDao->updateMonographArtworkFile($artworkFile);
		}

		return $fileId;
	}
	function processEvents(&$form) {
		$eventProcessed = false;

		if (Request::getUserVar('uploadNewArtwork')) {
			$press =& Request::getPress();
			$eventProcessed = true;
			import('monograph.MonographArtworkFile');
			$artworkFileDao =& DAORegistry::getDAO('MonographFileDAO');

			import('file.MonographFileManager');
			$monographFileManager = new MonographFileManager($this->monograph->getMonographId());
			$fileId = null;

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

			$artworkFile->setSeq(0);

			if ($newEntry) {
				$artworkFileDao->insertMonographArtworkFile($artworkFile);
			} else {
				$artworkFileDao->updateMonographArtworkFile($artworkFile);
			}

		} else if ($removeArtwork = Request::getUserVar('removeArtwork')) {
			$eventProcessed = true;
			list($fileId) = array_keys($removeArtwork);
			import('file.MonographFileManager');
			$monographFileManager = new MonographFileManager($this->monograph->getMonographId());

			$monographFileManager->deleteFile($fileId);

		}

		return $eventProcessed;
	}
}

?>