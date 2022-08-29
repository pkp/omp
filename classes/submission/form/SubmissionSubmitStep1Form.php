<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep1Form.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep1Form
 * @ingroup submission_form
 *
 * @brief Form for Step 1 of author submission.
 */

namespace APP\submission\form;

use APP\core\Application;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;

use PKP\security\Role;
use PKP\submission\form\PKPSubmissionSubmitStep1Form;

class SubmissionSubmitStep1Form extends PKPSubmissionSubmitStep1Form
{
    /**
     * Constructor.
     *
     * @param null|mixed $submission
     */
    public function __construct($context, $submission = null)
    {
        parent::__construct($context, $submission);
        $this->addCheck(new \PKP\form\validation\FormValidatorCustom($this, 'seriesId', 'optional', 'author.submit.seriesRequired', [DAORegistry::getDAO('SeriesDAO'), 'getById'], [$context->getId()]));
    }

    /**
     * @copydoc PKPSubmissionSubmitStep1Form::fetch
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false)
    {
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $user = $request->getUser();
        $canSubmitAll = $roleDao->userHasRole($this->context->getId(), $user->getId(), Role::ROLE_ID_MANAGER) ||
            $roleDao->userHasRole($this->context->getId(), $user->getId(), Role::ROLE_ID_SUB_EDITOR) ||
            $roleDao->userHasRole(Application::CONTEXT_SITE, $user->getId(), Role::ROLE_ID_SITE_ADMIN);

        // Get series for this context
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $activeSeries = [];
        $seriesIterator = $seriesDao->getByContextId($this->context->getId(), null, !$canSubmitAll);
        while ($series = $seriesIterator->next()) {
            if (!$series->getIsInactive()) {
                $activeSeries[$series->getId()] = $series->getLocalizedTitle();
            }
        }
        $seriesOptions = ['' => __('submission.submit.selectSeries')] + $activeSeries;
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('seriesOptions', $seriesOptions);

        return parent::fetch($request, $template, $display);
    }

    /**
     * @copydoc PKPSubmissionSubmitStep1Form::initData
     */
    public function initData($data = [])
    {
        if (isset($this->submission)) {
            parent::initData([
                'seriesId' => $this->submission->getSeriesId(),
                'workType' => $this->submission->getWorkType(),
            ]);
        } else {
            parent::initData();
        }
    }

    /**
     * Perform additional validation checks
     *
     * @copydoc PKPSubmissionSubmitStep1Form::validate
     */
    public function validate($callHooks = true)
    {
        if (!parent::validate($callHooks)) {
            return false;
        }

        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $series = $seriesDao->getById($this->getData('seriesId'), $context->getId());
        $seriesIsInactive = ($series && $series->getIsInactive()) ? true : false;
        // Ensure that submissions are enabled and the assigned series is activated
        if ($context->getData('disableSubmissions') || $seriesIsInactive) {
            return false;
        }

        return true;
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData()
    {
        $this->readUserVars([
            'workType', 'seriesId',
        ]);
        parent::readInputData();
    }

    /**
     * Set the submission data from the form.
     *
     * @param Submission $submission
     */
    public function setSubmissionData($submission)
    {
        $submission->setWorkType($this->getData('workType'));
        $submission->setSeriesId($this->getData('seriesId'));
        parent::setSubmissionData($submission);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\submission\form\SubmissionSubmitStep1Form', '\SubmissionSubmitStep1Form');
}
