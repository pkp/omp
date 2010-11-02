<?php

/**
 * @defgroup submission_form
 */

/**
 * @file classes/submission/form/MonographGalleyForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographGalleyForm
 * @ingroup submission_form
 * @see MonographGalley
 *
 * @brief Monograph galley editing form.
 */



import('lib.pkp.classes.form.Form');

class MonographGalleyForm extends Form {
	/** @var int the Id of the monograph */
	var $monographId;

	/** @var int the Id of the galley */
	var $galleyId;

	/** @var MonographGalley current galley */
	var $galley;

	/**
	 * Constructor.
	 * @param $monographId int
	 * @param $assignmentId int
	 * @param $galleyId int (optional)
	 */
	function MonographGalleyForm($monographId, $galleyId = null) {
		parent::Form('submission/layout/galleyForm.tpl');
		$press =& Request::getPress();
		$this->monographId = $monographId;

		if (isset($galleyId) && !empty($galleyId)) {
			$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
			$this->galley =& $galleyDao->getGalley($galleyId, $monographId);
			if (isset($this->galley)) {
				$this->galleyId = $galleyId;
			}
		}

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'galleyLocale', 'required', 'submission.layout.galleyLocaleRequired'), create_function('$galleyLocale,$availableLocales', 'return in_array($galleyLocale,$availableLocales);'), array_keys($press->getSupportedLocaleNames()));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Display the form.
	 */
	function display() {
		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('monographId', $this->monographId);
		$templateMgr->assign('galleyId', $this->galleyId);
		$templateMgr->assign('supportedLocales', $press->getSupportedLocaleNames());
		$templateMgr->assign('enablePublicGalleyId', $press->getSetting('enablePublicGalleyId'));

		if (isset($this->galley)) {
			$templateMgr->assign_by_ref('galley', $this->galley);
		}
		$templateMgr->assign('helpTopicId', 'editorial.layoutEditorsRole.layout');
		parent::display();
	}

	/**
	 * Validate the form
	 */
	function validate() {
		// check if public galley ID has already used
		$press =& Request::getPress();
		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');

		$publicGalleyId = $this->getData('publicGalleyId');
		if ($publicGalleyId && $galleyDao->publicGalleyIdExists($publicGalleyId, $this->galleyId)) {
			$this->addError('publicGalleyId', Locale::translate('submission.layout.galleyPublicIdentificationExists'));
		}

		return parent::validate();
	}

	/**
	 * Initialize form data from current galley (if applicable).
	 */
	function initData() {
		if (isset($this->galley)) {
			$galley =& $this->galley;
			$this->_data = array(
				'publicGalleyId' => $galley->getPublicGalleyId(),
				'galleyLocale' => $galley->getLocale(),
				'label' => $galley->getLabel()
			);

		} else {
			$this->_data = array();
		}

	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array(
				'publicGalleyId',
				'deleteStyleFile',
				'galleyLocale'
			)
		);
	}

	/**
	 * Save changes to the galley.
	 * @return int the galley ID
	 */
	function execute($fileName = null, $assignmentId = null) {
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($this->monographId);
		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');

		$fileName = isset($fileName) ? $fileName : 'galleyFile';
		$press =& Request::getPress();

		if (isset($this->galley)) {
			$galley =& $this->galley;

			// Upload galley file
			if ($monographFileManager->uploadedFileExists($fileName)) {
				if($galley->getFileId()) {
					$monographFileManager->uploadPublicFile($fileName, $galley->getFileId());
				} else {
					$fileId = $monographFileManager->uploadPublicFile($fileName);
					$galley->setFileId($fileId);
				}

				// Update file search index
				import('classes.search.MonographSearchIndex');
				MonographSearchIndex::updateFileIndex($this->monographId, MONOGRAPH_SEARCH_GALLEY_FILE, $galley->getFileId());
			}

			if ($monographFileManager->uploadedFileExists('styleFile')) {
				// Upload stylesheet file
				$styleFileId = $monographFileManager->uploadPublicFile('styleFile', $galley->getStyleFileId());
				$galley->setStyleFileId($styleFileId);

			} else if($this->getData('deleteStyleFile')) {
				// Delete stylesheet file
				$styleFile =& $galley->getStyleFile();
				if (isset($styleFile)) {
					$monographFileManager->deleteFile($styleFile->getFileId());
				}
			}

			// Update existing galley
			if ($press->getSetting('enablePublicGalleyId')) {
				$galley->setPublicGalleyId($this->getData('publicGalleyId'));
			}
			$galley->setLocale($this->getData('galleyLocale'));
			$galleyDao->updateObject($galley);

		} else {
			// Upload galley file
			if ($monographFileManager->uploadedFileExists($fileName)) {
				$fileType = $monographFileManager->getUploadedFileType($fileName);
				$fileId = $monographFileManager->uploadLayoutFile($fileName);

				// Update file search index
				import('classes.search.MonographSearchIndex');
				MonographSearchIndex::updateFileIndex($this->monographId, MONOGRAPH_SEARCH_GALLEY_FILE, $fileId);
			} else {
				$fileId = 0;
			}

			if (isset($fileType) && strstr($fileType, 'html')) {
				// Assume HTML galley
				$galley = new MonographHTMLGalley();
			} else {
				$galley = new MonographGalley();
			}

			$galley->setLocale($this->getData('galleyLocale'));
			$galley->setMonographId($this->monographId);
			$galley->setAssignmentId(isset($assignmentId) ? $assignmentId : 0);
			$galley->setFileId($fileId);

			if ($enablePublicGalleyId) {
				// check to make sure the assigned public id doesn't already exist
				$publicGalleyId = $galley->getPublicgalleyId();
				$suffix = '';
				$i = 1;
				while ($galleyDao->publicGalleyIdExists($publicGalleyId . $suffix, '')) {
					$suffix = '_'.$i++;
				}

				$galley->setPublicgalleyId($publicGalleyId . $suffix);
			}

			// Insert new galley
			$this->galleyId = $galleyDao->insertObject($galley);
		}

		return $this->galleyId;
	}

	/**
	 * Upload an image to an HTML galley.
	 * @param $imageName string file input key
	 */
	function uploadImage() {
		import('classes.file.MonographFileManager');
		$fileManager = new MonographFileManager($this->monographId);
		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');

		$fileName = 'imageFile';

		if (isset($this->galley) && $fileManager->uploadedFileExists($fileName)) {
			$type = $fileManager->getUploadedFileType($fileName);
			$extension = $fileManager->getImageExtension($type);
			if (!$extension) {
				$this->addError('imageFile', Locale::translate('submission.layout.imageInvalid'));
				return false;
			}

			if ($fileId = $fileManager->uploadPublicFile($fileName)) {
				$galleyDao->insertGalleyImage($this->galleyId, $fileId);

				// Update galley image files
				$this->galley->setImageFiles($galleyDao->getGalleyImages($this->galleyId));
			}

		}
	}

	/**
	 * Delete an image from an HTML galley.
	 * @param $imageId int the file ID of the image
	 */
	function deleteImage($imageId) {
		import('classes.file.MonographFileManager');
		$fileManager = new MonographFileManager($this->monographId);
		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');

		if (isset($this->galley)) {
			$images =& $this->galley->getImageFiles();
			if (isset($images)) {
				for ($i=0, $count=count($images); $i < $count; $i++) {
					if ($images[$i]->getFileId() == $imageId) {
						$fileManager->deleteFile($images[$i]->getFileId());
						$galleyDao->deleteGalleyImage($this->galleyId, $imageId);
						unset($images[$i]);
						break;
					}
				}
			}
		}
	}
}

?>
