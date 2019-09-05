<?php

/**
 * @file controllers/grid/users/chapter/form/ChapterForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterForm
 * @ingroup controllers_grid_users_chapter_form
 *
 * @brief Form for adding/editing a chapter
 * stores/retrieves from an associative array
 */

import('lib.pkp.classes.form.Form');

class ChapterForm extends Form {
	/** The monograph associated with the chapter being edited **/
	var $_monograph;

	/** The publication associated with the chapter being edited **/
	var $_publication;

	/** Chapter the chapter being edited **/
	var $_chapter;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $publication Publication
	 * @param $chapter Chapter
	 */
	function __construct($monograph, $publication, $chapter) {
		parent::__construct('controllers/grid/users/chapter/form/chapterForm.tpl');
		$this->setMonograph($monograph);
		$this->setPublication($publication);

		if ($chapter) {
			$this->setChapter($chapter);
		}

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'title', 'required', 'metadata.property.validationMessage.title'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this chapter grid.
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the monograph associated with this chapter grid.
	 * @param $monograph Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph = $monograph;
	}

	/**
	 * Get the publication associated with this chapter grid.
	 * @return Publication
	 */
	function getPublication() {
		return $this->_publication;
	}

	/**
	 * Set the publication associated with this chapter grid.
	 * @param $publication Publication
	 */
	function setPublication($publication) {
		$this->_publication = $publication;
	}

	/**
	 * Get the Chapter associated with this form
	 * @return Chapter
	 */
	function getChapter() {
		return $this->_chapter;
	}

	/**
	 * Set the Chapter associated with this form
	 * @param $chapter Chapter
	 */
	function setChapter($chapter) {
		$this->_chapter = $chapter;
	}

	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the associated chapter.
	 * @param $chapter Chapter
	 */
	function initData() {
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_DEFAULT, LOCALE_COMPONENT_PKP_SUBMISSION);

		$this->setData('submissionId', $this->getMonograph()->getId());
		$this->setData('publicationId', $this->getPublication()->getId());

		$chapter = $this->getChapter();
		if ($chapter) {
			$this->setData('chapterId', $chapter->getId());
			$this->setData('title', $chapter->getTitle());
			$this->setData('subtitle', $chapter->getSubtitle());
			$this->setData('abstract', $chapter->getAbstract());
		} else {
			$this->setData('title', null);
			$this->setData('subtitle', null);
			$this->setData('abstract', null);
		}
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {

		$chapterAuthorOptions = [];
		$selectedChapterAuthors = [];
		if ($this->getChapter()) {
			$selectedChapterAuthors = DAORegistry::getDAO('ChapterAuthorDAO')->getAuthors($this->getPublication()->getId(), $this->getChapter()->getId())->toArray();
			foreach ($selectedChapterAuthors as $selectedChapterAuthor) {
				$chapterAuthorOptions[] = [
					'id' => $selectedChapterAuthor->getId(),
					'title' => $selectedChapterAuthor->getFullName(),
				];
			}
		}
		$allAuthors = Services::get('author')->getMany(['publicationIds' => $this->getPublication()->getId(), 'count' => 1000]);
		foreach ($allAuthors as $author) {
			$isIncluded = false;
			foreach ($chapterAuthorOptions as $chapterAuthorOption) {
				if ($chapterAuthorOption['id'] === $author->getId()) {
					$isIncluded = true;
				}
			}
			if (!$isIncluded) {
				$chapterAuthorOptions[] = [
					'id' => $author->getId(),
					'title' => $author->getFullName(),
				];
			}
		}

		$chapterAuthorsListPanel = new \PKP\components\listPanels\ListPanel(
			'authors',
			__('submission.submit.addAuthor'),
			[
				'canOrder' => true,
				'canSelect' => true,
				'items' => $chapterAuthorOptions,
				'itemsMax' => count($chapterAuthorOptions),
				'selected' => array_map(function($author) { return $author->getId(); }, $selectedChapterAuthors),
				'selectorName' => 'authors[]',
			]
		);
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('chapterAuthorsListData', [
			'components' => [
				'authors' => $chapterAuthorsListPanel->getConfig()
			]
		]);

		if ($this->getChapter()) {
			$submissionFiles = DAORegistry::getDAO('SubmissionFileDAO')->getLatestRevisions($this->getMonograph()->getId());
			$chapterFileOptions = [];
			foreach ($submissionFiles as $submissionFile) {
				if (!$submissionFile->getData('chapterId') || $submissionFile->getData('chapterId') == $this->getChapter()->getId()) {
					$chapterFileOptions[] = [
						'id' => $submissionFile->getFileId(),
						'title' => $submissionFile->getLocalizedName(),
					];
				}
			}
			$selectedChapterFiles = [];
			foreach ($submissionFiles as $submissionFile) {
				if ($submissionFile->getData('chapterId') == $this->getChapter()->getId()) {
					$selectedChapterFiles[] = $submissionFile->getFileId();
				}
			}
			$chapterFilesListPanel = new \PKP\components\listPanels\ListPanel(
				'files',
				__('submission.files'),
				[
					'canSelect' => true,
					'items' => $chapterFileOptions,
					'itemsMax' => count($chapterFileOptions),
					'selected' => $selectedChapterFiles,
					'selectorName' => 'files[]',
				]
			);
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('chapterFilesListData', [
				'components' => [
					'chapterFilesListPanel' => $chapterFilesListPanel->getConfig()
				]
			]);
		}

		return parent::fetch($request, $template, $display);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('title', 'subtitle', 'authors', 'files','abstract'));
	}

	/**
	 * Save chapter
	 * @see Form::execute()
	 */
	function execute() {
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$chapter = $this->getChapter();
		$request = Application::get()->getRequest();
		$isEdit = !!$chapter;

		if ($chapter) {
			$chapter->setTitle($this->getData('title'), null); //Localized
			$chapter->setSubtitle($this->getData('subtitle'), null); //Localized
			$chapter->setAbstract($this->getData('abstract'), null); //Localized
			$chapterDao->updateObject($chapter);
		} else {
			$chapter = $chapterDao->newDataObject();
			$chapter->setData('publicationId', $this->getPublication()->getId());
			$chapter->setTitle($this->getData('title'), null); //Localized
			$chapter->setSubtitle($this->getData('subtitle'), null); //Localized
			$chapter->setAbstract($this->getData('abstract'), null); //Localized
			$chapter->setSequence(REALLY_BIG_NUMBER);
			$chapterDao->insertChapter($chapter);
			$chapterDao->resequenceChapters($this->getPublication()->getId());
		}

		$this->setChapter($chapter);

		// Save the chapter author aassociations
		DAORegistry::getDAO('ChapterAuthorDAO')->deleteChapterAuthorsByChapterId($this->getChapter()->getId());
		foreach ((array) $this->getData('authors') as $seq => $authorId) {
			DAORegistry::getDAO('ChapterAuthorDAO')->insertChapterAuthor($authorId, $this->getChapter()->getId(), false, $seq);
		}

		// Save the chapter file associations
		if ($isEdit) {
			$selectedFiles = (array) $this->getData('files');
			$allFiles = DAORegistry::getDAO('SubmissionFileDAO')->getLatestRevisions($this->getMonograph()->getId());
			foreach ($allFiles as $file) {
				$revisions = DAORegistry::getDAO('SubmissionFileDAO')->getAllRevisions($file->getId(), null, $this->getMonograph()->getId());
				foreach ($revisions as $revision) {
					if (in_array($revision->getFileId(), $selectedFiles)) {
						$revision->setData('chapterId', $this->getChapter()->getId());
					} else {
						$revision->setData('chapterId', null);
					}
					DAORegistry::getDAO('SubmissionFileDAO')->updateObject($revision);
				}
			}
		}

		return true;
	}
}


