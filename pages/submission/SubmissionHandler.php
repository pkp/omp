<?php

/**
 * @file pages/submission/SubmissionHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHandler
 *
 * @ingroup pages_submission
 *
 * @brief Handles page requests to the submission wizard
 */

namespace APP\pages\submission;

use APP\components\forms\submission\ForTheEditors;
use APP\components\forms\submission\ReconfigureSubmission;
use APP\components\forms\submission\StartSubmission;
use APP\components\listPanels\ContributorsListPanel;
use APP\controllers\grid\users\chapter\ChapterGridHandler;
use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\monograph\ChapterDAO;
use APP\publication\Publication;
use APP\section\Section;
use APP\submission\Submission;
use APP\template\TemplateManager;
use Illuminate\Support\LazyCollection;
use PKP\components\forms\FormComponent;
use PKP\components\forms\publication\Details;
use PKP\context\Context;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\pages\submission\PKPSubmissionHandler;
use PKP\plugins\Hook;

class SubmissionHandler extends PKPSubmissionHandler
{
    public const CHAPTERS_SECTION_ID = 'chapters';

    /**
     * Display the screen to start a new submission
     */
    protected function start(array $args, Request $request): void
    {
        $context = $request->getContext();
        $userGroups = $this->getSubmitUserGroups($context, $request->getUser());
        if (!$userGroups->count()) {
            $this->showErrorPage(
                'submission.wizard.notAllowed',
                __('submission.wizard.notAllowed.description', [
                    'email' => $context->getData('contactEmail'),
                    'name' => $context->getData('contactName'),
                ])
            );
            return;
        }

        $apiUrl = $request->getDispatcher()->url(
            $request,
            Application::ROUTE_API,
            $context->getPath(),
            'submissions'
        );

        $form = new StartSubmission($apiUrl, $context, $userGroups);

        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->setState([
            'form' => $form->getConfig(),
        ]);

        parent::start($args, $request);
    }

    protected function getSubmittingTo(Context $context, Submission $submission, array $sections, LazyCollection $categories): string
    {
        $multipleLanguages = count($context->getSupportedSubmissionLocales()) > 1;
        $workType = $submission->getData('workType');

        if ($multipleLanguages) {
            return __(
                (
                    $workType === Submission::WORK_TYPE_AUTHORED_WORK
                    ? 'submission.wizard.submitting.monographInLanguage'
                    : 'submission.wizard.submitting.editedVolumeInLanguage'
                ),
                ['language' => Locale::getSubmissionLocaleDisplayNames([$submission->getData('locale')])[$submission->getData('locale')]]
            );
        }

        return __(
            (
                $workType === Submission::WORK_TYPE_AUTHORED_WORK
                ? 'submission.wizard.submitting.monograph'
                : 'submission.wizard.submitting.editedVolume'
            )
        );
    }

    /**
     * Add the chapters grid to the details step
     */
    protected function getDetailsStep(Request $request, Submission $submission, Publication $publication, array $locales, string $publicationApiUrl, array $sections, string $controlledVocabUrl): array
    {
        $step = parent::getDetailsStep($request, $submission, $publication, $locales, $publicationApiUrl, $sections, $controlledVocabUrl);
        $step['sections'][] = [
            'id' => self::CHAPTERS_SECTION_ID,
            'name' => __('submission.chapters'),
            'description' => __('submission.wizard.chapters.description'),
            'type' => SubmissionHandler::SECTION_TYPE_TEMPLATE,
        ];

        Hook::add('Template::SubmissionWizard::Section', function (string $hookName, array $params) {
            $templateMgr = $params[1]; /** @var TemplateManager $templateMgr */
            $output = &$params[2]; /** @var string $step */

            $output .= sprintf(
                '<template v-else-if="section.id === \'' . self::CHAPTERS_SECTION_ID . '\'">%s</template>',
                $templateMgr->fetch('submission/chapters.tpl')
            );

            return false;
        });

        Hook::add('Template::SubmissionWizard::Section::Review', function (string $hookName, array $params) {
            $step = $params[0]['step']; /** @var string $step */
            $templateMgr = $params[1]; /** @var TemplateManager $templateMgr */
            $output = &$params[2]; /** @var string $output */

            if ($step === 'details') {
                $output .= $templateMgr->fetch('submission/review-chapters.tpl');
            }

            return false;
        });

        $chapterGrid = new ChapterGridHandler();
        /** @var ChapterDAO */
        $chapterDao = DAORegistry::getDAO('ChapterDAO');
        $chapters = $chapterDao->getByPublicationId($publication->getId())
            ->toArray();
        $chapterData = [];
        foreach ($chapters as $chapter) {
            $chapterData[] = $chapterGrid->getChapterData($chapter, $publication);
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->setState([
            'chapters' => $chapterData,
        ]);

        return $step;
    }

    protected function getReconfigureForm(Context $context, Submission $submission, Publication $publication, array $sections, LazyCollection $categories): ReconfigureSubmission
    {
        return new ReconfigureSubmission(
            FormComponent::ACTION_EMIT,
            $submission,
            $publication,
            $context
        );
    }

    protected function getDetailsForm(string $publicationApiUrl, array $locales, Publication $publication, Context $context, array $sections, string $suggestionUrlBase): Details
    {
        return new Details(
            $publicationApiUrl,
            $locales,
            $publication,
            $context,
            $suggestionUrlBase
        );
    }

    /**
     * Get the series that this user can submit to
     */
    protected function getSubmitSeries(Context $context): array
    {
        $allSeries = Repo::section()
            ->getCollector()
            ->filterByContextIds([$context->getId()])
            ->excludeInactive(true)
            ->getMany();

        $submitSeries = [];
        /** @var Section $series */
        foreach ($allSeries as $series) {
            if ($series->getEditorRestricted() && !$this->isEditor()) {
                continue;
            }
            $submitSeries[] = $series;
        }

        return $submitSeries;
    }

    protected function getForTheEditorsForm(string $publicationApiUrl, array $locales, Publication $publication, Submission $submission, Context $context, string $suggestionUrlBase, LazyCollection $categories): ForTheEditors
    {
        return new ForTheEditors(
            $publicationApiUrl,
            $locales,
            $publication,
            $submission,
            $context,
            $suggestionUrlBase,
            $this->getSubmitSeries($context),
            $categories
        );
    }

    /**
     * Get the properties that should be saved to the Submission
     * from the ReconfigureSubmission form
     */
    protected function getReconfigurePublicationProps(): array
    {
        return [];
    }

    /**
     * Get the properties that should be saved to the Submission
     * from the ReconfigureSubmission form
     */
    protected function getReconfigureSubmissionProps(): array
    {
        return ['locale', 'workType'];
    }

    protected function getContributorsListPanel(Request $request, Submission $submission, Publication $publication, array $locales): ContributorsListPanel
    {
        return new ContributorsListPanel(
            'contributors',
            __('publication.contributors'),
            $submission,
            $request->getContext(),
            $locales,
            [], // Populated by publication state
            true
        );
    }
}
