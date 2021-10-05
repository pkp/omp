<?php

/**
 * @file controllers/grid/users/chapter/form/ChapterForm.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterForm
 * @ingroup controllers_grid_users_chapter_form
 *
 * @brief Form for adding/editing a chapter
 * stores/retrieves from an associative array
 */

use APP\template\TemplateManager;

use PKP\form\Form;

class ChapterForm extends Form
{
    /** The monograph associated with the chapter being edited **/
    public $_monograph;

    /** The publication associated with the chapter being edited **/
    public $_publication;

    /** Chapter the chapter being edited **/
    public $_chapter;

    /**
     * Constructor.
     *
     * @param $monograph Monograph
     * @param $publication Publication
     * @param $chapter Chapter
     */
    public function __construct($monograph, $publication, $chapter)
    {
        parent::__construct('controllers/grid/users/chapter/form/chapterForm.tpl');
        $this->setMonograph($monograph);
        $this->setPublication($publication);
        $this->setDefaultFormLocale($publication->getData('locale'));

        if ($chapter) {
            $this->setChapter($chapter);
        }

        // Validation checks for this form
        $this->addCheck(new \PKP\form\validation\FormValidatorLocale($this, 'title', 'required', 'metadata.property.validationMessage.title', $publication->getData('locale')));
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }


    //
    // Getters/Setters
    //
    /**
     * Get the monograph associated with this chapter grid.
     *
     * @return Monograph
     */
    public function getMonograph()
    {
        return $this->_monograph;
    }

    /**
     * Set the monograph associated with this chapter grid.
     *
     * @param $monograph Monograph
     */
    public function setMonograph($monograph)
    {
        $this->_monograph = $monograph;
    }

    /**
     * Get the publication associated with this chapter grid.
     *
     * @return Publication
     */
    public function getPublication()
    {
        return $this->_publication;
    }

    /**
     * Set the publication associated with this chapter grid.
     *
     * @param $publication Publication
     */
    public function setPublication($publication)
    {
        $this->_publication = $publication;
    }

    /**
     * Get the Chapter associated with this form
     *
     * @return Chapter
     */
    public function getChapter()
    {
        return $this->_chapter;
    }

    /**
     * Set the Chapter associated with this form
     *
     * @param $chapter Chapter
     */
    public function setChapter($chapter)
    {
        $this->_chapter = $chapter;
    }

    //
    // Overridden template methods
    //
    /**
     * Initialize form data from the associated chapter.
     */
    public function initData()
    {
        AppLocale::requireComponents(LOCALE_COMPONENT_APP_DEFAULT, LOCALE_COMPONENT_PKP_SUBMISSION);

        $this->setData('submissionId', $this->getMonograph()->getId());
        $this->setData('publicationId', $this->getPublication()->getId());
        $this->setData('enableChapterPublicationDates', (bool) $this->getMonograph()->getEnableChapterPublicationDates());
		$this->setData('enableChapterLandingPages', (bool) $this->getMonograph()->getEnableChapterLandingPages());

        $chapter = $this->getChapter();
        if ($chapter) {
            $this->setData('chapterId', $chapter->getId());
            $this->setData('title', $chapter->getTitle());
            $this->setData('subtitle', $chapter->getSubtitle());
            $this->setData('abstract', $chapter->getAbstract());
            $this->setData('datePublished', $chapter->getDatePublished());
            $this->setData('isLandingPageEnabled', $chapter->isLandingPageEnabled());
            $this->setData('pages', $chapter->getPages());
        } else {
            $this->setData('title', null);
            $this->setData('subtitle', null);
            $this->setData('abstract', null);
            $this->setData('datePublished', null);
			$this->setData('isLandingPageEnabled', null);
            $this->setData('pages', null);
        }
    }

    /**
     * @copydoc Form::fetch()
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $chapterAuthorOptions = [];
        $selectedChapterAuthors = [];
        $chapterFileOptions = [];
        $selectedChapterFiles = [];

        if ($this->getChapter()) {
            $selectedChapterAuthors = DAORegistry::getDAO('ChapterAuthorDAO')->getAuthors($this->getPublication()->getId(), $this->getChapter()->getId())->toArray();
            foreach ($selectedChapterAuthors as $selectedChapterAuthor) {
                $chapterAuthorOptions[$selectedChapterAuthor->getId()] = $selectedChapterAuthor->getFullName();
            }
        }
        $authorsIterator = Services::get('author')->getMany(['publicationIds' => $this->getPublication()->getId(), 'count' => 1000]);
        foreach ($authorsIterator as $author) {
            $isIncluded = false;
            foreach ($chapterAuthorOptions as $chapterAuthorOptionId => $chapterAuthorOption) {
                if ($chapterAuthorOptionId === $author->getId()) {
                    $isIncluded = true;
                }
            }
            if (!$isIncluded) {
                $chapterAuthorOptions[$author->getId()] = $author->getFullName();
            }
        }

        $submissionFiles = Services::get('submissionFile')->getMany(['submissionIds' => [$this->getMonograph()->getId()]]);
        foreach ($submissionFiles as $submissionFile) {
            $isIncluded = false;

            if ($this->getChapter() && $submissionFile->getData('chapterId') == $this->getChapter()->getId()) {
                $selectedChapterFiles[] = $submissionFile->getId();
                $isIncluded = true;
            }

            // Include in list if not used in another chapter OR already selected to this chapter
            if (!$submissionFile->getData('chapterId') || $isIncluded) {
                $chapterFileOptions[$submissionFile->getId()] = $submissionFile->getLocalizedData('name');
            }
        }

        $templateMgr->assign([
            'chapterAuthorOptions' => $chapterAuthorOptions,
            'selectedChapterAuthors' => array_map(function ($author) {
                return $author->getId();
            }, $selectedChapterAuthors),
            'chapterFileOptions' => $chapterFileOptions,
            'selectedChapterFiles' => $selectedChapterFiles,
        ]);

        return parent::fetch($request, $template, $display);
    }

    /**
     * Assign form data to user-submitted data.
     *
     * @see Form::readInputData()
     */
    public function readInputData()
    {
        $this->readUserVars(['title', 'subtitle', 'authors', 'files','abstract','datePublished','pages','isLandingPageEnabled']);
    }

    /**
     * Save chapter
     *
     * @see Form::execute()
     */
    public function execute(...$functionParams)
    {
		parent::execute(...$functionParams);

        $chapterDao = DAORegistry::getDAO('ChapterDAO'); /* @var $chapterDao ChapterDAO */
        $chapter = $this->getChapter();

        if ($chapter) {
            $chapter->setTitle($this->getData('title'), null); //Localized
            $chapter->setSubtitle($this->getData('subtitle'), null); //Localized
            $chapter->setAbstract($this->getData('abstract'), null); //Localized
            $chapter->setDatePublished($this->getData('datePublished'));
            $chapter->setPages($this->getData('pages'));
            $chapter->setLandingPageEnabled($this->getData('isLandingPageEnabled'));
            $chapterDao->updateObject($chapter);
        } else {
            $chapter = $chapterDao->newDataObject();
            $chapter->setData('publicationId', $this->getPublication()->getId());
            $chapter->setTitle($this->getData('title'), null); //Localized
            $chapter->setSubtitle($this->getData('subtitle'), null); //Localized
            $chapter->setAbstract($this->getData('abstract'), null); //Localized
            $chapter->setDatePublished($this->getData('datePublished'));
            $chapter->setPages($this->getData('pages'));
			$chapter->setLandingPageEnabled($this->getData('isLandingPageEnabled'));
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
        $selectedFiles = (array) $this->getData('files');
        DAORegistry::getDAO('SubmissionFileDAO')->updateChapterFiles($selectedFiles, $this->getChapter()->getId());

        return true;
    }
}
