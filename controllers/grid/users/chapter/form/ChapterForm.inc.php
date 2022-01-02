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

use APP\facades\Repo;
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
     * @param Monograph $monograph
     * @param Publication $publication
     * @param Chapter $chapter
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
     * @param Monograph $monograph
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
     * @param Publication $publication
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
     * @param Chapter $chapter
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

        $chapter = $this->getChapter();
        if ($chapter) {
            $this->setData('chapterId', $chapter->getId());
            $this->setData('title', $chapter->getTitle());
            $this->setData('subtitle', $chapter->getSubtitle());
            $this->setData('abstract', $chapter->getAbstract());
            $this->setData('datePublished', $chapter->getDatePublished());
            $this->setData('isPageEnabled', $chapter->isPageEnabled());
            $this->setData('pages', $chapter->getPages());
        } else {
            $this->setData('title', null);
            $this->setData('subtitle', null);
            $this->setData('abstract', null);
            $this->setData('datePublished', null);
            $this->setData('isPageEnabled', null);
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

        $selectedChapterAuthorsArray = [];
        if ($this->getChapter()) {
            $selectedChapterAuthors = Repo::author()->getMany(
                Repo::author()
                    ->getCollector()
                    ->filterByChapterIds([$this->getChapter()->getId()])
                    ->filterByPublicationIds([$this->getPublication()->getId()])
            );

            foreach ($selectedChapterAuthors as $selectedChapterAuthor) {
                $chapterAuthorOptions[$selectedChapterAuthor->getId()] = $selectedChapterAuthor->getFullName();
            }

            if ($selectedChapterAuthors) {
                $selectedChapterAuthorsArray = iterator_to_array($selectedChapterAuthors);
            }
        }
        $authorsIterator = Repo::author()->getMany(
            Repo::author()
                ->getCollector()
                ->filterByPublicationIds([$this->getPublication()->getId()])
        );

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

        $collector = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$this->getMonograph()->getId()]);

        $submissionFiles = Repo::submissionFile()->getMany($collector);
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
            }, $selectedChapterAuthorsArray),
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
        $this->readUserVars(['title', 'subtitle', 'authors', 'files','abstract','datePublished','pages','isPageEnabled']);
    }

    /**
     * Save chapter
     *
     * @see Form::execute()
     */
    public function execute(...$functionParams)
    {
        parent::execute(...$functionParams);

        $chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var ChapterDAO $chapterDao */
        $chapter = $this->getChapter();

        if ($chapter) {
            $chapter->setTitle($this->getData('title'), null); //Localized
            $chapter->setSubtitle($this->getData('subtitle'), null); //Localized
            $chapter->setAbstract($this->getData('abstract'), null); //Localized
            $chapter->setDatePublished($this->getData('datePublished'));
            $chapter->setPages($this->getData('pages'));
            $chapter->setPageEnabled($this->getData('isPageEnabled'));
            $chapterDao->updateObject($chapter);
        } else {
            $chapter = $chapterDao->newDataObject();
            $chapter->setData('publicationId', $this->getPublication()->getId());
            $chapter->setTitle($this->getData('title'), null); //Localized
            $chapter->setSubtitle($this->getData('subtitle'), null); //Localized
            $chapter->setAbstract($this->getData('abstract'), null); //Localized
            $chapter->setDatePublished($this->getData('datePublished'));
            $chapter->setPages($this->getData('pages'));
            $chapter->setPageEnabled($this->getData('isPageEnabled'));
            $chapter->setSequence(REALLY_BIG_NUMBER);
            $chapterDao->insertChapter($chapter);
            $chapterDao->resequenceChapters($this->getPublication()->getId());
        }

        $this->setChapter($chapter);

        // Save the chapter author associations
        Repo::author()->removeChapterAuthors($this->getChapter());
        foreach ((array) $this->getData('authors') as $seq => $authorId) {
            Repo::author()->addToChapter($authorId, $this->getChapter()->getId(), false, $seq);
        }

        // Save the chapter file associations
        $selectedFiles = (array) $this->getData('files');
        Repo::submissionFile()
            ->dao
            ->updateChapterFiles(
                $selectedFiles,
                $this->getChapter()->getId()
            );

        return true;
    }
}
