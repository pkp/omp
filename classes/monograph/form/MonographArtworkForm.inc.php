<?php
 
/**
 * @file classes/monograph/form/MonographArtworkForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographArtworkForm
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Form to create or edit an issue
 */

// $Id$


import('form.Form');

class MonographArtworkForm extends Form {

	var $monograph;

	/**
	 * Constructor.
	 */
	function MonographArtworkForm($template, $monograph) {
		parent::Form($template);
		$this->addCheck(new FormValidatorPost($this));
		$this->monograph =& $monograph;
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
		$press =& Request::getPress();

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$artworks =& $monographFileDao->getMonographFilesByAssocId(null, MONOGRAPH_FILE_ARTWORK, $this->monograph->getMonographId());

		$templateMgr->assign_by_ref('artworks', $artworks);
		$templateMgr->assign_by_ref('submission', $this->monograph);
		// set up the accessibility options pulldown
//		$templateMgr->assign('enableSubscriptions', $press->getSetting('enableSubscriptions'));
//		$templateMgr->assign('enableDelayedOpenAccess', $press->getSetting('enableDelayedOpenAccess'));

//		$accessOptions = array();
//		$accessOptions[OPEN_ACCESS] = Locale::Translate('editor.issues.openAccess');
//		$accessOptions[SUBSCRIPTION] = Locale::Translate('editor.issues.subscription');
//		$templateMgr->assign('accessOptions', $accessOptions);

//		$templateMgr->assign('enablePublicIssueId', $press->getSetting('enablePublicIssueId'));

		parent::display();
	}

	function processEvents() {
		$eventProcessed = false;

		if (Request::getUserVar('uploadNewArtworkx')) {

			import('file.MonographFileManager');
			$eventProcessed = true;
			$monographFileManager = new MonographFileManager($this->monograph->getMonographId());

			if ($monographFileManager->uploadedFileExists('artworkFile')) {
				$fileId = $monographFileManager->uploadArtworkFile('artworkFile', null);
			}

		} else if ($deleteAuthor = Request::getUserVar('remove')) {
			// Delete an author
			$eventProcessed = true;
			
		}

		return $eventProcessed;
	}

	/**
	 * Initialize form data from current issue.
	 * returns issue id that it initialized the page with
	 */
	function initData() {

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

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'artworkFile',
			'type',
			'componentId',
			'identifier'
		));
	}

	/**
	 * Save issue settings.
	 */
	function execute() {
		$press =& Request::getPress();

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
		$this->readInputData();

		$artworkFile->setFileId($fileId);
		$artworkFile->setPermission(0);
		$artworkFile->setPermissionFileId(0);
		$artworkFile->setMonographComponentId($this->getData('componentId'));
		$artworkFile->setIdentifier($this->getData('identifier'));

		$artworkFile->setSeq(0);

		if ($newEntry) {
			$artworkFileDao->insertMonographArtworkFile($artworkFile);
		} else {
			$artworkFileDao->updateMonographArtworkFile($artworkFile);
		}

		return $fileId;
	}
}

?>