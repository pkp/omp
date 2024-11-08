<?php

/**
 * @file controllers/grid/users/chapter/form/ChapterForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterForm
 *
 * @ingroup controllers_grid_users_chapter_form
 *
 * @brief Form for adding/editing a chapter
 * stores/retrieves from an associative array
 */

namespace APP\controllers\grid\users\chapter\form;

use APP\core\Application;
use APP\facades\Repo;
use APP\monograph\Chapter;
use APP\monograph\ChapterDAO;
use APP\publication\Publication;
use APP\submission\Submission;
use APP\submissionFile\DAO;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\form\Form;

class ChapterForm extends Form
{
    /** @var Submission The monograph associated with the chapter being edited */
    public $_monograph;

    /** @var Publication The publication associated with the chapter being edited */
    public $_publication;

    /** @var Chapter the chapter being edited */
    public $_chapter;

    /**
     * Constructor.
     *
     * @param Submission $monograph
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
     * @return Submission
     */
    public function getMonograph()
    {
        return $this->_monograph;
    }

    /**
     * Set the monograph associated with this chapter grid.
     *
     * @param Submission $monograph
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
        //Create chapter license URL description
        $chapterLicenseUrlDescription = '';
        if ($this->getMonograph()->getData('workType') === Submission::WORK_TYPE_EDITED_VOLUME) {
            $licenseOptions = Application::getCCLicenseOptions();
            $context = Application::get()->getRequest()->getContext();
            if ($this->getPublication()->getData('chapterLicenseUrl')) {
                if (array_key_exists(
                    $this->getPublication()
                        ->getData('chapterLicenseUrl'),
                    $licenseOptions
                )) {
                    $licenseName = __(
                        $licenseOptions[$this->getPublication()
                            ->getData('chapterLicenseUrl')]
                    );
                } else {
                    $licenseName = $this->getPublication()
                        ->getData('chapterLicenseUrl');
                }
                $chapterLicenseUrlDescription = __('submission.license.description', [
                    'licenseUrl' => $this->getPublication()->getData('chapterLicenseUrl'),
                    'licenseName' => $licenseName,
                ]);
            } elseif ($this->getPublication()->getData('licenseUrl')) {
                if (array_key_exists($this->getPublication()->getData('licenseUrl'), $licenseOptions)) {
                    $licenseName = __($licenseOptions[$this->getPublication()->getData('licenseUrl')]);
                } else {
                    $licenseName = $this->getPublication()->getData('licenseUrl');
                }
                $chapterLicenseUrlDescription = __('submission.license.description', [
                    'licenseUrl' => $this->getPublication()->getData('licenseUrl'),
                    'licenseName' => $licenseName,
                ]);
            } elseif ($context->getData('licenseUrl')) {
                if (array_key_exists($context->getData('licenseUrl'), $licenseOptions)) {
                    $licenseName = __($licenseOptions[$context->getData('licenseUrl')]);
                } else {
                    $licenseName = $context->getData('licenseUrl');
                }
                $chapterLicenseUrlDescription = __('submission.license.description', [
                    'licenseUrl' => $context->getData('licenseUrl'),
                    'licenseName' => $licenseName,
                ]);
            }
        }

        $this->setData('submissionId', $this->getMonograph()->getId());
        $this->setData('publicationId', $this->getPublication()->getId());
        $this->setData('enableChapterPublicationDates', (bool) $this->getMonograph()->getEnableChapterPublicationDates());
        $this->setData('submissionWorkType', $this->getMonograph()->getData('workType'));
        $this->setData('chapterLicenseUrlDescription', $chapterLicenseUrlDescription);

        $chapter = $this->getChapter();
        if ($chapter) {
            $this->setData('chapterId', $chapter->getId());
            $this->setData('title', $chapter->getTitle());
            $this->setData('subtitle', $chapter->getSubtitle());
            $this->setData('abstract', $chapter->getAbstract());
            $this->setData('datePublished', $chapter->getDatePublished());
            $this->setData('isPageEnabled', $chapter->isPageEnabled());
            $this->setData('pages', $chapter->getPages());
            $this->setData('licenseUrl', $chapter->getLicenseUrl());
        } else {
            $this->setData('title', null);
            $this->setData('subtitle', null);
            $this->setData('abstract', null);
            $this->setData('datePublished', null);
            $this->setData('isPageEnabled', null);
            $this->setData('pages', null);
            $this->setData('licenseUrl', null);
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
            $selectedChapterAuthors = Repo::author()->getCollector()
                ->filterByChapterId($this->getChapter()->getId())
                ->filterByPublicationIds([$this->getPublication()->getId()])
                ->getMany();

            foreach ($selectedChapterAuthors as $selectedChapterAuthor) {
                $chapterAuthorOptions[$selectedChapterAuthor->getId()] = $selectedChapterAuthor->getFullName();
            }

            if ($selectedChapterAuthors) {
                $selectedChapterAuthorsArray = iterator_to_array($selectedChapterAuthors);
            }
        }
        $authorsIterator = Repo::author()->getCollector()
            ->filterByPublicationIds([$this->getPublication()->getId()])
            ->getMany();

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

        $submissionFiles = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$this->getMonograph()->getId()])
            ->getMany();

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
            'doi' => $this->getChapter()?->getDoi()
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
        $this->readUserVars(['title', 'subtitle', 'authors', 'files','abstract','datePublished','pages','isPageEnabled','licenseUrl']);
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
            $chapter->setLicenseUrl($this->getData('licenseUrl'));
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
            $chapter->setLicenseUrl($this->getData('licenseUrl'));
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
        /** @var DAO */
        $dao = Repo::submissionFile()->dao;
        $dao->updateChapterFiles(
            $selectedFiles,
            $this->getChapter()->getId()
        );

        return true;
    }
}
